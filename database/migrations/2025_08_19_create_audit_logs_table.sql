-- Script SQL para crear tabla de auditoría/logs
-- Sistema Medifarma - Tabla de Logs de Auditoría
-- Fecha: 2025-08-19

-- Crear la tabla de logs de auditoría
CREATE TABLE ODS.TAB_AUDIT_LOGS (
    -- Identificador único del log
    idLog BIGINT IDENTITY(1,1) PRIMARY KEY,
    
    -- Información del usuario
    idUsuario INT NULL, -- Puede ser NULL para acciones del sistema
    nombreUsuario NVARCHAR(255) NULL, -- Nombre completo del usuario
    emailUsuario NVARCHAR(255) NULL, -- Email del usuario
    
    -- Información de la acción
    accion NVARCHAR(50) NOT NULL, -- INSERT, UPDATE, DELETE, LOGIN, LOGOUT, VIEW, etc.
    tabla NVARCHAR(100) NOT NULL, -- Nombre de la tabla afectada
    descripcion NVARCHAR(500) NULL, -- Descripción detallada de la acción
    
    -- Datos de la operación
    registroId NVARCHAR(50) NULL, -- ID del registro afectado
    valoresAnteriores NVARCHAR(MAX) NULL, -- Valores antes del cambio (JSON)
    valoresNuevos NVARCHAR(MAX) NULL, -- Valores después del cambio (JSON)
    
    -- Información técnica
    ip NVARCHAR(45) NULL, -- Dirección IP del usuario
    userAgent NVARCHAR(500) NULL, -- Información del navegador
    sesionId NVARCHAR(255) NULL, -- ID de la sesión
    
    -- Información de resultado
    exitoso BIT NOT NULL DEFAULT 1, -- Si la operación fue exitosa
    mensajeError NVARCHAR(1000) NULL, -- Mensaje de error si falló
    
    -- Timestamps
    fechaHora DATETIME2(3) NOT NULL DEFAULT GETDATE(), -- Fecha y hora de la acción
    duracionMs INT NULL, -- Duración de la operación en milisegundos
    
    -- Metadatos adicionales
    modulo NVARCHAR(100) NULL, -- Módulo del sistema (market-management, user-management, etc.)
    controlador NVARCHAR(100) NULL, -- Controlador que ejecutó la acción
    metadatos NVARCHAR(MAX) NULL -- Información adicional en formato JSON
);

-- Crear índices para mejorar el rendimiento
CREATE INDEX IX_TAB_AUDIT_LOGS_Usuario ON ODS.TAB_AUDIT_LOGS (idUsuario);
CREATE INDEX IX_TAB_AUDIT_LOGS_FechaHora ON ODS.TAB_AUDIT_LOGS (fechaHora);
CREATE INDEX IX_TAB_AUDIT_LOGS_Accion ON ODS.TAB_AUDIT_LOGS (accion);
CREATE INDEX IX_TAB_AUDIT_LOGS_Tabla ON ODS.TAB_AUDIT_LOGS (tabla);
CREATE INDEX IX_TAB_AUDIT_LOGS_Modulo ON ODS.TAB_AUDIT_LOGS (modulo);
CREATE INDEX IX_TAB_AUDIT_LOGS_RegistroId ON ODS.TAB_AUDIT_LOGS (registroId);

-- Crear índice compuesto para consultas frecuentes
CREATE INDEX IX_TAB_AUDIT_LOGS_Usuario_Fecha ON ODS.TAB_AUDIT_LOGS (idUsuario, fechaHora DESC);
CREATE INDEX IX_TAB_AUDIT_LOGS_Tabla_Accion ON ODS.TAB_AUDIT_LOGS (tabla, accion);

-- Agregar comentarios a la tabla
EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description', 
    @value = N'Tabla de auditoría para registrar todas las acciones de usuarios en el sistema', 
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOGS';

-- Comentarios para las columnas principales
EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description', 
    @value = N'Identificador único del registro de log', 
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOGS',
    @level2type = N'COLUMN', @level2name = N'idLog';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description', 
    @value = N'Tipo de acción realizada: INSERT, UPDATE, DELETE, LOGIN, LOGOUT, VIEW', 
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOGS',
    @level2type = N'COLUMN', @level2name = N'accion';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description', 
    @value = N'Nombre de la tabla o entidad afectada por la acción', 
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOGS',
    @level2type = N'COLUMN', @level2name = N'tabla';

-- Crear stored procedure mejorado para insertar logs de auditoría con soporte para SP
CREATE PROCEDURE ODS.SP_INSERT_AUDIT_LOG
    @idUsuario INT = NULL,
    @nombreUsuario NVARCHAR(255) = NULL,
    @emailUsuario NVARCHAR(255) = NULL,
    @accion NVARCHAR(50), -- Ej: 'SP_INSERT_MERCADO', 'SP_UPDATE_MERCADO', etc.
    @tabla NVARCHAR(100),
    @descripcion NVARCHAR(500) = NULL,
    @registroId NVARCHAR(50) = NULL,
    @valoresAnteriores NVARCHAR(MAX) = NULL,
    @valoresNuevos NVARCHAR(MAX) = NULL,
    @ip NVARCHAR(45) = NULL,
    @userAgent NVARCHAR(500) = NULL,
    @sesionId NVARCHAR(255) = NULL,
    @exitoso BIT = 1,
    @mensajeError NVARCHAR(1000) = NULL,
    @duracionMs INT = NULL,
    @modulo NVARCHAR(100) = NULL,
    @controlador NVARCHAR(100) = NULL,
    @metadatos NVARCHAR(MAX) = NULL,
    @storedProcedure NVARCHAR(100) = NULL, -- Nombre del SP que se ejecutó
    @parametrosSP NVARCHAR(MAX) = NULL -- Parámetros pasados al SP
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        INSERT INTO ODS.TAB_AUDIT_LOGS (
            idUsuario, nombreUsuario, emailUsuario, accion, tabla, descripcion,
            registroId, valoresAnteriores, valoresNuevos, ip, userAgent, sesionId,
            exitoso, mensajeError, duracionMs, modulo, controlador, metadatos
        )
        VALUES (
            @idUsuario, @nombreUsuario, @emailUsuario, 
            CASE 
                WHEN @storedProcedure IS NOT NULL THEN @storedProcedure 
                ELSE @accion 
            END,
            @tabla, 
            CASE 
                WHEN @storedProcedure IS NOT NULL THEN 
                    CONCAT(@descripcion, ' (SP: ', @storedProcedure, ')')
                ELSE @descripcion 
            END,
            @registroId, @valoresAnteriores, @valoresNuevos, @ip, @userAgent, @sesionId,
            @exitoso, @mensajeError, @duracionMs, @modulo, @controlador, 
            CASE 
                WHEN @storedProcedure IS NOT NULL THEN 
                    JSON_MODIFY(
                        ISNULL(@metadatos, '{}'), 
                        '$.stored_procedure', @storedProcedure
                    )
                WHEN @parametrosSP IS NOT NULL THEN
                    JSON_MODIFY(
                        ISNULL(@metadatos, '{}'), 
                        '$.sp_parameters', @parametrosSP
                    )
                ELSE @metadatos 
            END
        );
        
        RETURN SCOPE_IDENTITY(); -- Retorna el ID del log insertado
    END TRY
    BEGIN CATCH
        -- En caso de error, intentar registrar el error
        DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();
        
        -- Intento básico de insertar el error
        INSERT INTO ODS.TAB_AUDIT_LOGS (
            idUsuario, accion, tabla, descripcion, exitoso, mensajeError, modulo
        )
        VALUES (
            @idUsuario, 'ERROR_LOG', 'SYSTEM', 'Error al insertar log de auditoría', 0, @ErrorMessage, 'AUDIT_SYSTEM'
        );
        
        RETURN -1; -- Indica error
    END CATCH
END;

-- Crear stored procedure especializado para auditar operaciones con SP_INSERT_MERCADO
CREATE PROCEDURE ODS.SP_INSERT_MERCADO_WITH_AUDIT
    @mercado NVARCHAR(255),
    @idUsuario INT = NULL,
    @nombreUsuario NVARCHAR(255) = NULL,
    @emailUsuario NVARCHAR(255) = NULL,
    @ip NVARCHAR(45) = NULL,
    @userAgent NVARCHAR(500) = NULL,
    @sesionId NVARCHAR(255) = NULL,
    @modulo NVARCHAR(100) = 'market-management',
    @controlador NVARCHAR(100) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @startTime DATETIME2 = SYSDATETIME();
    DECLARE @idMercadoCreado INT = NULL;
    DECLARE @exitoso BIT = 1;
    DECLARE @mensajeError NVARCHAR(1000) = NULL;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        -- Verificar si ya existe el mercado
        IF EXISTS (SELECT 1 FROM ODS.TAB_MERCADO WHERE mercado = @mercado)
        BEGIN
            SET @exitoso = 0;
            SET @mensajeError = 'Ya existe un mercado con ese nombre';
            
            -- Log del intento fallido
            EXEC ODS.SP_INSERT_AUDIT_LOG
                @idUsuario = @idUsuario,
                @nombreUsuario = @nombreUsuario,
                @emailUsuario = @emailUsuario,
                @accion = 'SP_INSERT_MERCADO_FAILED',
                @tabla = 'TAB_MERCADO',
                @descripcion = 'Intento de crear mercado con nombre duplicado',
                @valoresNuevos = @mercado,
                @ip = @ip,
                @userAgent = @userAgent,
                @sesionId = @sesionId,
                @exitoso = 0,
                @mensajeError = @mensajeError,
                @duracionMs = DATEDIFF(MILLISECOND, @startTime, SYSDATETIME()),
                @modulo = @modulo,
                @controlador = @controlador,
                @storedProcedure = 'SP_INSERT_MERCADO',
                @parametrosSP = CONCAT('{"mercado": "', @mercado, '"}');
            
            THROW 50001, @mensajeError, 1;
        END
        
        -- Ejecutar el SP original
        EXEC ODS.SP_INSERT_MERCADO @mercado;
        
        -- Obtener el ID del mercado recién creado
        SELECT @idMercadoCreado = idMercado 
        FROM ODS.TAB_MERCADO 
        WHERE mercado = @mercado 
        ORDER BY fechaRegistro DESC;
        
        -- Log de la operación exitosa
        EXEC ODS.SP_INSERT_AUDIT_LOG
            @idUsuario = @idUsuario,
            @nombreUsuario = @nombreUsuario,
            @emailUsuario = @emailUsuario,
            @accion = 'SP_INSERT_MERCADO',
            @tabla = 'TAB_MERCADO',
            @descripcion = 'Creación de mercado mediante stored procedure',
            @registroId = @idMercadoCreado,
            @valoresNuevos = CONCAT('{"mercado": "', @mercado, '", "idMercado": ', @idMercadoCreado, ', "solicitud": "ESPERA"}'),
            @ip = @ip,
            @userAgent = @userAgent,
            @sesionId = @sesionId,
            @exitoso = 1,
            @duracionMs = DATEDIFF(MILLISECOND, @startTime, SYSDATETIME()),
            @modulo = @modulo,
            @controlador = @controlador,
            @storedProcedure = 'SP_INSERT_MERCADO',
            @parametrosSP = CONCAT('{"mercado": "', @mercado, '"}');
        
        COMMIT TRANSACTION;
        
        -- Retornar información del mercado creado
        SELECT 
            @idMercadoCreado as idMercado,
            @mercado as mercado,
            'ESPERA' as solicitud,
            GETDATE() as fechaRegistro,
            1 as exitoso,
            'Mercado creado exitosamente' as mensaje;
            
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;
        
        SET @exitoso = 0;
        SET @mensajeError = ERROR_MESSAGE();
        
        -- Log del error
        EXEC ODS.SP_INSERT_AUDIT_LOG
            @idUsuario = @idUsuario,
            @nombreUsuario = @nombreUsuario,
            @emailUsuario = @emailUsuario,
            @accion = 'SP_INSERT_MERCADO_ERROR',
            @tabla = 'TAB_MERCADO',
            @descripcion = 'Error al crear mercado mediante stored procedure',
            @valoresNuevos = @mercado,
            @ip = @ip,
            @userAgent = @userAgent,
            @sesionId = @sesionId,
            @exitoso = 0,
            @mensajeError = @mensajeError,
            @duracionMs = DATEDIFF(MILLISECOND, @startTime, SYSDATETIME()),
            @modulo = @modulo,
            @controlador = @controlador,
            @storedProcedure = 'SP_INSERT_MERCADO',
            @parametrosSP = CONCAT('{"mercado": "', @mercado, '"}');
        
        -- Retornar información del error
        SELECT 
            NULL as idMercado,
            @mercado as mercado,
            NULL as solicitud,
            NULL as fechaRegistro,
            0 as exitoso,
            @mensajeError as mensaje;
            
        THROW;
    END CATCH
END;

-- Crear stored procedure para auditar actualizaciones de mercados
CREATE PROCEDURE ODS.SP_UPDATE_MERCADO_WITH_AUDIT
    @idMercado INT,
    @nuevoNombre NVARCHAR(255),
    @idUsuario INT = NULL,
    @nombreUsuario NVARCHAR(255) = NULL,
    @emailUsuario NVARCHAR(255) = NULL,
    @ip NVARCHAR(45) = NULL,
    @userAgent NVARCHAR(500) = NULL,
    @sesionId NVARCHAR(255) = NULL,
    @modulo NVARCHAR(100) = 'market-management',
    @controlador NVARCHAR(100) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @startTime DATETIME2 = SYSDATETIME();
    DECLARE @nombreAnterior NVARCHAR(255);
    DECLARE @exitoso BIT = 1;
    DECLARE @mensajeError NVARCHAR(1000) = NULL;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        -- Obtener el nombre anterior
        SELECT @nombreAnterior = mercado 
        FROM ODS.TAB_MERCADO 
        WHERE idMercado = @idMercado;
        
        IF @nombreAnterior IS NULL
        BEGIN
            SET @exitoso = 0;
            SET @mensajeError = 'Mercado no encontrado';
            THROW 50002, @mensajeError, 1;
        END
        
        -- Verificar si el nuevo nombre ya existe (en otro mercado)
        IF EXISTS (SELECT 1 FROM ODS.TAB_MERCADO WHERE mercado = @nuevoNombre AND idMercado != @idMercado)
        BEGIN
            SET @exitoso = 0;
            SET @mensajeError = 'Ya existe otro mercado con ese nombre';
            THROW 50003, @mensajeError, 1;
        END
        
        -- Actualizar el mercado
        UPDATE ODS.TAB_MERCADO 
        SET mercado = @nuevoNombre,
            fechaUpdate = GETDATE(),
            idUsuario = @idUsuario
        WHERE idMercado = @idMercado;
        
        -- Log de la operación exitosa
        EXEC ODS.SP_INSERT_AUDIT_LOG
            @idUsuario = @idUsuario,
            @nombreUsuario = @nombreUsuario,
            @emailUsuario = @emailUsuario,
            @accion = 'SP_UPDATE_MERCADO',
            @tabla = 'TAB_MERCADO',
            @descripcion = 'Actualización de nombre de mercado',
            @registroId = @idMercado,
            @valoresAnteriores = CONCAT('{"mercado": "', @nombreAnterior, '"}'),
            @valoresNuevos = CONCAT('{"mercado": "', @nuevoNombre, '"}'),
            @ip = @ip,
            @userAgent = @userAgent,
            @sesionId = @sesionId,
            @exitoso = 1,
            @duracionMs = DATEDIFF(MILLISECOND, @startTime, SYSDATETIME()),
            @modulo = @modulo,
            @controlador = @controlador,
            @storedProcedure = 'SP_UPDATE_MERCADO',
            @parametrosSP = CONCAT('{"idMercado": ', @idMercado, ', "nuevoNombre": "', @nuevoNombre, '"}');
        
        COMMIT TRANSACTION;
        
        SELECT 
            @idMercado as idMercado,
            @nuevoNombre as mercado,
            @nombreAnterior as nombreAnterior,
            1 as exitoso,
            'Mercado actualizado exitosamente' as mensaje;
            
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;
        
        SET @exitoso = 0;
        SET @mensajeError = ERROR_MESSAGE();
        
        -- Log del error
        EXEC ODS.SP_INSERT_AUDIT_LOG
            @idUsuario = @idUsuario,
            @nombreUsuario = @nombreUsuario,
            @emailUsuario = @emailUsuario,
            @accion = 'SP_UPDATE_MERCADO_ERROR',
            @tabla = 'TAB_MERCADO',
            @descripcion = 'Error al actualizar mercado',
            @registroId = @idMercado,
            @valoresAnteriores = CONCAT('{"mercado": "', ISNULL(@nombreAnterior, 'UNKNOWN'), '"}'),
            @valoresNuevos = CONCAT('{"mercado": "', @nuevoNombre, '"}'),
            @ip = @ip,
            @userAgent = @userAgent,
            @sesionId = @sesionId,
            @exitoso = 0,
            @mensajeError = @mensajeError,
            @duracionMs = DATEDIFF(MILLISECOND, @startTime, SYSDATETIME()),
            @modulo = @modulo,
            @controlador = @controlador,
            @storedProcedure = 'SP_UPDATE_MERCADO',
            @parametrosSP = CONCAT('{"idMercado": ', @idMercado, ', "nuevoNombre": "', @nuevoNombre, '"}');
        
        SELECT 
            @idMercado as idMercado,
            @nuevoNombre as mercado,
            @nombreAnterior as nombreAnterior,
            0 as exitoso,
            @mensajeError as mensaje;
            
        THROW;
    END CATCH
END;

-- Crear stored procedure para consultar logs con filtros
CREATE PROCEDURE ODS.SP_GET_AUDIT_LOGS
    @idUsuario INT = NULL,
    @accion NVARCHAR(50) = NULL,
    @tabla NVARCHAR(100) = NULL,
    @modulo NVARCHAR(100) = NULL,
    @fechaInicio DATETIME2 = NULL,
    @fechaFin DATETIME2 = NULL,
    @pageSize INT = 50,
    @pageNumber INT = 1
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @offset INT = (@pageNumber - 1) * @pageSize;
    
    SELECT 
        idLog,
        idUsuario,
        nombreUsuario,
        emailUsuario,
        accion,
        tabla,
        descripcion,
        registroId,
        valoresAnteriores,
        valoresNuevos,
        ip,
        userAgent,
        sesionId,
        exitoso,
        mensajeError,
        fechaHora,
        duracionMs,
        modulo,
        controlador,
        metadatos
    FROM ODS.TAB_AUDIT_LOGS
    WHERE 
        (@idUsuario IS NULL OR idUsuario = @idUsuario)
        AND (@accion IS NULL OR accion = @accion)
        AND (@tabla IS NULL OR tabla = @tabla)
        AND (@modulo IS NULL OR modulo = @modulo)
        AND (@fechaInicio IS NULL OR fechaHora >= @fechaInicio)
        AND (@fechaFin IS NULL OR fechaHora <= @fechaFin)
    ORDER BY fechaHora DESC
    OFFSET @offset ROWS
    FETCH NEXT @pageSize ROWS ONLY;
    
    -- Retornar también el total de registros
    SELECT COUNT(*) as TotalRegistros
    FROM ODS.TAB_AUDIT_LOGS
    WHERE 
        (@idUsuario IS NULL OR idUsuario = @idUsuario)
        AND (@accion IS NULL OR accion = @accion)
        AND (@tabla IS NULL OR tabla = @tabla)
        AND (@modulo IS NULL OR modulo = @modulo)
        AND (@fechaInicio IS NULL OR fechaHora >= @fechaInicio)
        AND (@fechaFin IS NULL OR fechaHora <= @fechaFin);
END;

-- Ejemplos de uso del stored procedure para insertar logs:

-- Ejemplo 1: Log de creación de mercado
/*
EXEC ODS.SP_INSERT_AUDIT_LOG
    @idUsuario = 1,
    @nombreUsuario = 'Juan Pérez',
    @emailUsuario = 'juan.perez@medifarma.com',
    @accion = 'INSERT',
    @tabla = 'TAB_MERCADO',
    @descripcion = 'Creación de nuevo mercado',
    @registroId = '123',
    @valoresNuevos = '{"mercado": "Mercado Premium", "estado": "ACTIVO"}',
    @ip = '192.168.1.100',
    @modulo = 'market-management',
    @controlador = 'MarketManagementController';
*/

-- Ejemplo 2: Log de actualización de usuario
/*
EXEC ODS.SP_INSERT_AUDIT_LOG
    @idUsuario = 1,
    @nombreUsuario = 'Admin Sistema',
    @emailUsuario = 'admin@medifarma.com',
    @accion = 'UPDATE',
    @tabla = 'users',
    @descripcion = 'Actualización de datos de usuario',
    @registroId = '5',
    @valoresAnteriores = '{"name": "Usuario Viejo", "email": "viejo@test.com"}',
    @valoresNuevos = '{"name": "Usuario Nuevo", "email": "nuevo@test.com"}',
    @ip = '192.168.1.100',
    @modulo = 'user-management',
    @controlador = 'UserManagementController';
*/

-- Ejemplo 3: Consultar logs de un usuario específico
/*
EXEC ODS.SP_GET_AUDIT_LOGS
    @idUsuario = 1,
    @fechaInicio = '2025-08-01',
    @fechaFin = '2025-08-19',
    @pageSize = 25,
    @pageNumber = 1;
*/
