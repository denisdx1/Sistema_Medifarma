<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla de auditoría simple
        DB::connection('sqlsrv')->unprepared("
            CREATE TABLE ODS.TAB_AUDIT_LOGS (
                idLog BIGINT IDENTITY(1,1) PRIMARY KEY,
                nombreUsuario NVARCHAR(255) NOT NULL,
                accion NVARCHAR(100) NOT NULL,
                descripcion NVARCHAR(500) NOT NULL,
                fechaHora DATETIME2(3) NOT NULL DEFAULT GETDATE()
            );
        ");

        // Crear índices básicos
        DB::connection('sqlsrv')->unprepared("
            CREATE INDEX IX_TAB_AUDIT_LOGS_Usuario ON ODS.TAB_AUDIT_LOGS (nombreUsuario);
            CREATE INDEX IX_TAB_AUDIT_LOGS_FechaHora ON ODS.TAB_AUDIT_LOGS (fechaHora);
        ");

        // Crear stored procedure simple para insertar logs
        DB::connection('sqlsrv')->unprepared("
            CREATE PROCEDURE ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                @nombreUsuario NVARCHAR(255),
                @accion NVARCHAR(100),
                @descripcion NVARCHAR(500)
            AS
            BEGIN
                SET NOCOUNT ON;
                
                INSERT INTO ODS.TAB_AUDIT_LOGS (nombreUsuario, accion, descripcion)
                VALUES (@nombreUsuario, @accion, @descripcion);
                
                RETURN SCOPE_IDENTITY();
            END;
        ");

        // Crear stored procedure mejorado para crear mercado con auditoría simple
        DB::connection('sqlsrv')->unprepared("
            CREATE PROCEDURE ODS.SP_INSERT_MERCADO_WITH_SIMPLE_AUDIT
                @mercado NVARCHAR(255),
                @nombreUsuario NVARCHAR(255)
            AS
            BEGIN
                SET NOCOUNT ON;
                
                BEGIN TRY
                    BEGIN TRANSACTION;
                    
                    -- Verificar si ya existe el mercado
                    IF EXISTS (SELECT 1 FROM ODS.TAB_MERCADO WHERE mercado = @mercado)
                    BEGIN
                        -- Log del intento fallido
                        EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                            @nombreUsuario = @nombreUsuario,
                            @accion = 'CREAR MERCADO - FALLIDO',
                            @descripcion = CONCAT('Intento de crear mercado \"', @mercado, '\" - Ya existe');
                        
                        THROW 50001, 'Ya existe un mercado con ese nombre', 1;
                    END
                    
                    -- Ejecutar el SP original
                    EXEC ODS.SP_INSERT_MERCADO @mercado;
                    
                    -- Obtener el ID del mercado creado
                    DECLARE @idMercado INT;
                    SELECT @idMercado = idMercado 
                    FROM ODS.TAB_MERCADO 
                    WHERE mercado = @mercado 
                    ORDER BY fechaRegistro DESC;
                    
                    -- Log de la operación exitosa
                    EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                        @nombreUsuario = @nombreUsuario,
                        @accion = 'CREAR MERCADO',
                        @descripcion = CONCAT('Creó el mercado \"', @mercado, '\" (ID: ', @idMercado, ')');
                    
                    COMMIT TRANSACTION;
                    
                    -- Retornar información del mercado creado
                    SELECT 
                        @idMercado as idMercado,
                        @mercado as mercado,
                        'ESPERA' as solicitud,
                        GETDATE() as fechaRegistro,
                        1 as exitoso,
                        'Mercado creado exitosamente' as mensaje;
                        
                END TRY
                BEGIN CATCH
                    ROLLBACK TRANSACTION;
                    
                    -- Log del error
                    EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                        @nombreUsuario = @nombreUsuario,
                        @accion = 'CREAR MERCADO - ERROR',
                        @descripcion = CONCAT('Error al crear mercado \"', @mercado, '\": ', ERROR_MESSAGE());
                    
                    THROW;
                END CATCH
            END;
        ");

        // Crear stored procedure para actualizar mercado con auditoría simple
        DB::connection('sqlsrv')->unprepared("
            CREATE PROCEDURE ODS.SP_UPDATE_MERCADO_WITH_SIMPLE_AUDIT
                @idMercado INT,
                @nuevoNombre NVARCHAR(255),
                @nombreUsuario NVARCHAR(255)
            AS
            BEGIN
                SET NOCOUNT ON;
                
                DECLARE @nombreAnterior NVARCHAR(255);
                
                BEGIN TRY
                    BEGIN TRANSACTION;
                    
                    -- Obtener el nombre anterior
                    SELECT @nombreAnterior = mercado 
                    FROM ODS.TAB_MERCADO 
                    WHERE idMercado = @idMercado;
                    
                    IF @nombreAnterior IS NULL
                    BEGIN
                        EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                            @nombreUsuario = @nombreUsuario,
                            @accion = 'ACTUALIZAR MERCADO - FALLIDO',
                            @descripcion = CONCAT('Intento de actualizar mercado ID ', @idMercado, ' - No encontrado');
                        
                        THROW 50002, 'Mercado no encontrado', 1;
                    END
                    
                    -- Verificar si el nuevo nombre ya existe
                    IF EXISTS (SELECT 1 FROM ODS.TAB_MERCADO WHERE mercado = @nuevoNombre AND idMercado != @idMercado)
                    BEGIN
                        EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                            @nombreUsuario = @nombreUsuario,
                            @accion = 'ACTUALIZAR MERCADO - FALLIDO',
                            @descripcion = CONCAT('Intento de cambiar \"', @nombreAnterior, '\" a \"', @nuevoNombre, '\" - Nombre ya existe');
                        
                        THROW 50003, 'Ya existe otro mercado con ese nombre', 1;
                    END
                    
                    -- Actualizar el mercado
                    UPDATE ODS.TAB_MERCADO 
                    SET mercado = @nuevoNombre,
                        fechaUpdate = GETDATE()
                    WHERE idMercado = @idMercado;
                    
                    -- Log de la operación exitosa
                    EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                        @nombreUsuario = @nombreUsuario,
                        @accion = 'ACTUALIZAR MERCADO',
                        @descripcion = CONCAT('Cambió el nombre del mercado de \"', @nombreAnterior, '\" a \"', @nuevoNombre, '\" (ID: ', @idMercado, ')');
                    
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
                    
                    EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                        @nombreUsuario = @nombreUsuario,
                        @accion = 'ACTUALIZAR MERCADO - ERROR',
                        @descripcion = CONCAT('Error al actualizar mercado ID ', @idMercado, ': ', ERROR_MESSAGE());
                    
                    THROW;
                END CATCH
            END;
        ");

        // Crear stored procedure para aprobar mercado con auditoría simple
        DB::connection('sqlsrv')->unprepared("
            CREATE PROCEDURE ODS.SP_APPROVE_MERCADO_WITH_SIMPLE_AUDIT
                @idMercado INT,
                @nombreUsuario NVARCHAR(255)
            AS
            BEGIN
                SET NOCOUNT ON;
                
                DECLARE @nombreMercado NVARCHAR(255);
                DECLARE @idSolicitudAprobado INT;
                
                BEGIN TRY
                    BEGIN TRANSACTION;
                    
                    -- Obtener información del mercado
                    SELECT @nombreMercado = mercado 
                    FROM ODS.TAB_MERCADO 
                    WHERE idMercado = @idMercado;
                    
                    IF @nombreMercado IS NULL
                    BEGIN
                        EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                            @nombreUsuario = @nombreUsuario,
                            @accion = 'APROBAR MERCADO - FALLIDO',
                            @descripcion = CONCAT('Intento de aprobar mercado ID ', @idMercado, ' - No encontrado');
                        
                        THROW 50004, 'Mercado no encontrado', 1;
                    END
                    
                    -- Obtener ID de solicitud APROBADO
                    SELECT @idSolicitudAprobado = idSolicitud 
                    FROM ODS.TAB_SOLICITUD 
                    WHERE solicitud = 'APROBADO';
                    
                    -- Actualizar el mercado
                    UPDATE ODS.TAB_MERCADO 
                    SET idSolicitud = @idSolicitudAprobado
                    WHERE idMercado = @idMercado;
                    
                    -- Log de la operación exitosa
                    EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                        @nombreUsuario = @nombreUsuario,
                        @accion = 'APROBAR MERCADO',
                        @descripcion = CONCAT('Aprobó el mercado \"', @nombreMercado, '\" (ID: ', @idMercado, ')');
                    
                    COMMIT TRANSACTION;
                    
                    SELECT 
                        @idMercado as idMercado,
                        @nombreMercado as mercado,
                        1 as exitoso,
                        'Mercado aprobado exitosamente' as mensaje;
                        
                END TRY
                BEGIN CATCH
                    ROLLBACK TRANSACTION;
                    
                    EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE
                        @nombreUsuario = @nombreUsuario,
                        @accion = 'APROBAR MERCADO - ERROR',
                        @descripcion = CONCAT('Error al aprobar mercado \"', ISNULL(@nombreMercado, 'UNKNOWN'), '\": ', ERROR_MESSAGE());
                    
                    THROW;
                END CATCH
            END;
        ");

        // Crear stored procedure para consultar logs
        DB::connection('sqlsrv')->unprepared("
            CREATE PROCEDURE ODS.SP_GET_AUDIT_LOGS_SIMPLE
                @nombreUsuario NVARCHAR(255) = NULL,
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
                    nombreUsuario,
                    accion,
                    descripcion,
                    fechaHora
                FROM ODS.TAB_AUDIT_LOGS
                WHERE 
                    (@nombreUsuario IS NULL OR nombreUsuario LIKE '%' + @nombreUsuario + '%')
                    AND (@fechaInicio IS NULL OR fechaHora >= @fechaInicio)
                    AND (@fechaFin IS NULL OR fechaHora <= @fechaFin)
                ORDER BY fechaHora DESC
                OFFSET @offset ROWS
                FETCH NEXT @pageSize ROWS ONLY;
                
                -- Retornar también el total de registros
                SELECT COUNT(*) as TotalRegistros
                FROM ODS.TAB_AUDIT_LOGS
                WHERE 
                    (@nombreUsuario IS NULL OR nombreUsuario LIKE '%' + @nombreUsuario + '%')
                    AND (@fechaInicio IS NULL OR fechaHora >= @fechaInicio)
                    AND (@fechaFin IS NULL OR fechaHora <= @fechaFin);
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar stored procedures
        DB::connection('sqlsrv')->unprepared('DROP PROCEDURE IF EXISTS ODS.SP_INSERT_AUDIT_LOG_SIMPLE');
        DB::connection('sqlsrv')->unprepared('DROP PROCEDURE IF EXISTS ODS.SP_INSERT_MERCADO_WITH_SIMPLE_AUDIT');
        DB::connection('sqlsrv')->unprepared('DROP PROCEDURE IF EXISTS ODS.SP_UPDATE_MERCADO_WITH_SIMPLE_AUDIT');
        DB::connection('sqlsrv')->unprepared('DROP PROCEDURE IF EXISTS ODS.SP_APPROVE_MERCADO_WITH_SIMPLE_AUDIT');
        DB::connection('sqlsrv')->unprepared('DROP PROCEDURE IF EXISTS ODS.SP_GET_AUDIT_LOGS_SIMPLE');
        
        // Eliminar tabla
        DB::connection('sqlsrv')->unprepared('DROP TABLE IF EXISTS ODS.TAB_AUDIT_LOGS');
    }
};
