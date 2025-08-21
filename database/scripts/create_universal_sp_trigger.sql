-- Script para crear trigger universal para auditoría de Stored Procedures
-- Sistema Medifarma - Trigger Universal para SP

USE [TuBaseDeDatos]  -- Reemplaza con el nombre de tu base de datos
GO

-- =============================================
-- TRIGGER UNIVERSAL PARA AUDITORÍA DE STORED PROCEDURES
-- =============================================

-- Primero, crear una tabla para rastrear ejecuciones de SP si no existe
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'TAB_SP_EXECUTION_LOG' AND schema_id = SCHEMA_ID('ODS'))
BEGIN
    CREATE TABLE ODS.TAB_SP_EXECUTION_LOG (
        idExecution BIGINT IDENTITY(1,1) PRIMARY KEY,
        session_id INT NOT NULL,
        spid INT NOT NULL,
        procedure_name NVARCHAR(256) NOT NULL,
        parameters NVARCHAR(MAX) NULL,
        start_time DATETIME2 NOT NULL,
        end_time DATETIME2 NULL,
        duration_ms INT NULL,
        rows_affected INT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'EXECUTING', -- EXECUTING, COMPLETED, ERROR
        error_message NVARCHAR(MAX) NULL,
        user_context INT NULL
    );
    
    CREATE INDEX IX_TAB_SP_EXECUTION_LOG_Session ON ODS.TAB_SP_EXECUTION_LOG (session_id, start_time);
    CREATE INDEX IX_TAB_SP_EXECUTION_LOG_Procedure ON ODS.TAB_SP_EXECUTION_LOG (procedure_name, start_time);
END;
GO

-- =============================================
-- PROCEDURE PARA REGISTRAR INICIO DE SP
-- =============================================
CREATE OR ALTER PROCEDURE ODS.SP_LOG_PROCEDURE_START
    @ProcedureName NVARCHAR(256),
    @Parameters NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @SessionId INT = @@SPID;
    DECLARE @UserId INT = CAST(SESSION_CONTEXT(N'user_id') AS INT);
    
    INSERT INTO ODS.TAB_SP_EXECUTION_LOG (
        session_id,
        spid,
        procedure_name,
        parameters,
        start_time,
        status,
        user_context
    )
    VALUES (
        @SessionId,
        @SessionId,
        @ProcedureName,
        @Parameters,
        GETDATE(),
        'EXECUTING',
        @UserId
    );
    
    -- Retornar el ID de ejecución para tracking
    SELECT SCOPE_IDENTITY() AS execution_id;
END;
GO

-- =============================================
-- PROCEDURE PARA REGISTRAR FIN DE SP
-- =============================================
CREATE OR ALTER PROCEDURE ODS.SP_LOG_PROCEDURE_END
    @ExecutionId BIGINT,
    @Status VARCHAR(20) = 'COMPLETED',
    @RowsAffected INT = NULL,
    @ErrorMessage NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @StartTime DATETIME2;
    DECLARE @Duration INT;
    
    -- Obtener tiempo de inicio
    SELECT @StartTime = start_time 
    FROM ODS.TAB_SP_EXECUTION_LOG 
    WHERE idExecution = @ExecutionId;
    
    -- Calcular duración
    SET @Duration = DATEDIFF(MILLISECOND, @StartTime, GETDATE());
    
    -- Actualizar registro
    UPDATE ODS.TAB_SP_EXECUTION_LOG
    SET 
        end_time = GETDATE(),
        duration_ms = @Duration,
        rows_affected = @RowsAffected,
        status = @Status,
        error_message = @ErrorMessage
    WHERE idExecution = @ExecutionId;
    
    -- Registrar en auditoría principal si es un SP importante
    IF EXISTS (SELECT 1 FROM ODS.TAB_SP_EXECUTION_LOG 
               WHERE idExecution = @ExecutionId 
               AND procedure_name IN ('SP_INSERT_MERCADO', 'SP_UPDATE_MERCADO', 'SP_INSERT_CONFIGURACION', 'SP_UPDATE_CONFIGURACION', 'SP_ASIGNAR_RESTO'))
    BEGIN
        DECLARE @ProcName NVARCHAR(256), @Params NVARCHAR(MAX), @UserId INT;
        
        SELECT 
            @ProcName = procedure_name,
            @Params = parameters,
            @UserId = user_context
        FROM ODS.TAB_SP_EXECUTION_LOG 
        WHERE idExecution = @ExecutionId;
        
        -- Determinar acción basada en el SP
        DECLARE @Accion VARCHAR(100), @Descripcion NVARCHAR(500);
        
        SET @Accion = CASE 
            WHEN @ProcName = 'SP_INSERT_MERCADO' THEN 'INSERT_MERCADO'
            WHEN @ProcName = 'SP_UPDATE_MERCADO' THEN 'UPDATE_MERCADO'
            WHEN @ProcName = 'SP_INSERT_CONFIGURACION' THEN 'INSERT_CONFIGURACION'
            WHEN @ProcName = 'SP_UPDATE_CONFIGURACION' THEN 'UPDATE_CONFIGURACION'
            WHEN @ProcName = 'SP_ASIGNAR_RESTO' THEN 'ASIGNAR_RESTO'
            ELSE 'STORED_PROCEDURE_EXECUTION'
        END;
        
        SET @Descripcion = 'Ejecutado: ' + @ProcName + 
                          CASE WHEN @Params IS NOT NULL THEN ' con parámetros: ' + @Params ELSE '' END +
                          ' (Duración: ' + CAST(@Duration AS VARCHAR(10)) + 'ms)';
        
        -- Insertar en auditoría principal
        INSERT INTO ODS.TAB_AUDIT_LOG (
            idUsuario,
            accion,
            stored_procedure,
            descripcion,
            fecha_hora,
            datos_nuevos,
            resultado,
            mensaje_error
        )
        VALUES (
            ISNULL(@UserId, 1),
            @Accion,
            @ProcName,
            @Descripcion,
            GETDATE(),
            '{"execution_id":' + CAST(@ExecutionId AS VARCHAR(20)) + ',"duration_ms":' + CAST(@Duration AS VARCHAR(10)) + ',"parameters":"' + ISNULL(@Params, '') + '"}',
            @Status,
            @ErrorMessage
        );
    END;
END;
GO

-- =============================================
-- PROCEDURE WRAPPER GENÉRICO PARA SP IMPORTANTES
-- =============================================
CREATE OR ALTER PROCEDURE ODS.SP_EXECUTE_WITH_AUDIT
    @ProcedureName NVARCHAR(256),
    @Parameters NVARCHAR(MAX) = NULL,
    @SQL NVARCHAR(MAX)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @ExecutionId BIGINT;
    DECLARE @ErrorMessage NVARCHAR(MAX);
    DECLARE @RowsAffected INT;
    
    BEGIN TRY
        -- Registrar inicio
        EXEC ODS.SP_LOG_PROCEDURE_START @ProcedureName, @Parameters;
        SELECT @ExecutionId = execution_id FROM (
            SELECT TOP 1 idExecution as execution_id 
            FROM ODS.TAB_SP_EXECUTION_LOG 
            WHERE session_id = @@SPID 
            ORDER BY start_time DESC
        ) t;
        
        -- Ejecutar el SP dinámicamente
        EXEC sp_executesql @SQL;
        SET @RowsAffected = @@ROWCOUNT;
        
        -- Registrar fin exitoso
        EXEC ODS.SP_LOG_PROCEDURE_END @ExecutionId, 'COMPLETED', @RowsAffected;
        
    END TRY
    BEGIN CATCH
        SET @ErrorMessage = ERROR_MESSAGE();
        
        -- Registrar error
        EXEC ODS.SP_LOG_PROCEDURE_END @ExecutionId, 'ERROR', NULL, @ErrorMessage;
        
        -- Re-lanzar el error
        THROW;
    END CATCH;
END;
GO

-- =============================================
-- VISTA PARA CONSULTAR AUDITORÍA COMPLETA
-- =============================================
CREATE OR ALTER VIEW ODS.VW_AUDIT_COMPLETE AS
SELECT 
    a.idAuditLog,
    a.idUsuario,
    u.usuario as nombre_usuario,
    a.accion,
    a.stored_procedure,
    a.descripcion,
    a.fecha_hora,
    a.ip_address,
    a.datos_anteriores,
    a.datos_nuevos,
    a.resultado,
    a.mensaje_error,
    -- Información adicional del SP si existe
    sp.duration_ms,
    sp.rows_affected,
    sp.parameters as sp_parameters
FROM ODS.TAB_AUDIT_LOG a
LEFT JOIN ODS.TAB_USUARIO u ON a.idUsuario = u.idUsuario
LEFT JOIN ODS.TAB_SP_EXECUTION_LOG sp ON JSON_VALUE(a.datos_nuevos, '$.execution_id') = CAST(sp.idExecution AS VARCHAR(20));
GO

-- =============================================
-- PROCEDURE PARA LIMPIAR LOGS ANTIGUOS
-- =============================================
CREATE OR ALTER PROCEDURE ODS.SP_CLEANUP_AUDIT_LOGS
    @DaysToKeep INT = 90
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @CutoffDate DATETIME2 = DATEADD(DAY, -@DaysToKeep, GETDATE());
    DECLARE @DeletedSP INT, @DeletedAudit INT;
    
    -- Limpiar logs de ejecución de SP
    DELETE FROM ODS.TAB_SP_EXECUTION_LOG 
    WHERE start_time < @CutoffDate;
    SET @DeletedSP = @@ROWCOUNT;
    
    -- Limpiar logs de auditoría (opcional, comentado por seguridad)
    -- DELETE FROM ODS.TAB_AUDIT_LOG 
    -- WHERE fecha_hora < @CutoffDate;
    -- SET @DeletedAudit = @@ROWCOUNT;
    
    PRINT 'Limpieza completada:';
    PRINT '- Logs de SP eliminados: ' + CAST(@DeletedSP AS VARCHAR(10));
    PRINT '- Logs de auditoría eliminados: 0 (conservados por seguridad)';
END;
GO

PRINT 'Sistema de auditoría universal creado exitosamente:';
PRINT '';
PRINT 'COMPONENTES CREADOS:';
PRINT '1. TAB_SP_EXECUTION_LOG - Tabla para rastrear ejecuciones de SP';
PRINT '2. SP_LOG_PROCEDURE_START - Registra inicio de SP';
PRINT '3. SP_LOG_PROCEDURE_END - Registra fin de SP';
PRINT '4. SP_EXECUTE_WITH_AUDIT - Wrapper para ejecutar SP con auditoría';
PRINT '5. VW_AUDIT_COMPLETE - Vista completa de auditoría';
PRINT '6. SP_CLEANUP_AUDIT_LOGS - Limpieza de logs antiguos';
PRINT '';
PRINT 'EJEMPLO DE USO:';
PRINT 'EXEC ODS.SP_EXECUTE_WITH_AUDIT ''SP_INSERT_MERCADO'', ''Nuevo Mercado'', ''EXEC SP_INSERT_MERCADO ''''Nuevo Mercado'''''';
PRINT '';
PRINT 'CONSULTAR AUDITORÍA:';
PRINT 'SELECT * FROM ODS.VW_AUDIT_COMPLETE ORDER BY fecha_hora DESC;';
