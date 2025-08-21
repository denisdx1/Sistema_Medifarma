-- Script para crear triggers de auditoría automática
-- Sistema Medifarma - Triggers para TAB_MERCADO

USE [TuBaseDeDatos]  -- Reemplaza con el nombre de tu base de datos
GO

-- =============================================
-- TRIGGER PARA INSERT EN TAB_MERCADO
-- =============================================
CREATE OR ALTER TRIGGER TRG_TAB_MERCADO_INSERT
ON ODS.TAB_MERCADO
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @idUsuario INT = CAST(SESSION_CONTEXT(N'user_id') AS INT);
    
    -- Si no hay contexto de usuario, usar un valor por defecto (usuario sistema)
    IF @idUsuario IS NULL
        SET @idUsuario = 1; -- ID del usuario sistema o admin
    
    INSERT INTO ODS.TAB_AUDIT_LOG (
        idUsuario,
        accion,
        stored_procedure,
        descripcion,
        fecha_hora,
        datos_nuevos,
        resultado
    )
    SELECT 
        @idUsuario,
        'INSERT_MERCADO',
        'SP_INSERT_MERCADO',
        'Nuevo mercado creado: ' + i.mercado,
        GETDATE(),
        '{"idMercado":' + CAST(i.idMercado AS VARCHAR(10)) + ',"mercado":"' + i.mercado + '","idEstado":' + CAST(i.idEstado AS VARCHAR(10)) + '}',
        'EXITOSO'
    FROM inserted i;
END;
GO

-- =============================================
-- TRIGGER PARA UPDATE EN TAB_MERCADO
-- =============================================
CREATE OR ALTER TRIGGER TRG_TAB_MERCADO_UPDATE
ON ODS.TAB_MERCADO
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @idUsuario INT = CAST(SESSION_CONTEXT(N'user_id') AS INT);
    
    -- Si no hay contexto de usuario, usar un valor por defecto
    IF @idUsuario IS NULL
        SET @idUsuario = 1;
    
    INSERT INTO ODS.TAB_AUDIT_LOG (
        idUsuario,
        accion,
        stored_procedure,
        descripcion,
        fecha_hora,
        datos_anteriores,
        datos_nuevos,
        resultado
    )
    SELECT 
        @idUsuario,
        'UPDATE_MERCADO',
        'SP_UPDATE_MERCADO',
        'Mercado actualizado ID: ' + CAST(i.idMercado AS VARCHAR(10)) + ' - ' + d.mercado + ' → ' + i.mercado,
        GETDATE(),
        '{"idMercado":' + CAST(d.idMercado AS VARCHAR(10)) + ',"mercado":"' + d.mercado + '","idEstado":' + CAST(d.idEstado AS VARCHAR(10)) + '}',
        '{"idMercado":' + CAST(i.idMercado AS VARCHAR(10)) + ',"mercado":"' + i.mercado + '","idEstado":' + CAST(i.idEstado AS VARCHAR(10)) + '}',
        'EXITOSO'
    FROM inserted i
    INNER JOIN deleted d ON i.idMercado = d.idMercado
    WHERE i.mercado != d.mercado OR i.idEstado != d.idEstado; -- Solo registrar si hubo cambios reales
END;
GO

-- =============================================
-- TRIGGER PARA DELETE EN TAB_MERCADO
-- =============================================
CREATE OR ALTER TRIGGER TRG_TAB_MERCADO_DELETE
ON ODS.TAB_MERCADO
AFTER DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @idUsuario INT = CAST(SESSION_CONTEXT(N'user_id') AS INT);
    
    -- Si no hay contexto de usuario, usar un valor por defecto
    IF @idUsuario IS NULL
        SET @idUsuario = 1;
    
    INSERT INTO ODS.TAB_AUDIT_LOG (
        idUsuario,
        accion,
        stored_procedure,
        descripcion,
        fecha_hora,
        datos_anteriores,
        resultado
    )
    SELECT 
        @idUsuario,
        'DELETE_MERCADO',
        'SP_DELETE_MERCADO',
        'Mercado eliminado: ' + d.mercado + ' (ID: ' + CAST(d.idMercado AS VARCHAR(10)) + ')',
        GETDATE(),
        '{"idMercado":' + CAST(d.idMercado AS VARCHAR(10)) + ',"mercado":"' + d.mercado + '","idEstado":' + CAST(d.idEstado AS VARCHAR(10)) + '}',
        'EXITOSO'
    FROM deleted d;
END;
GO

-- =============================================
-- TRIGGER PARA INSERT EN TAB_CONFIGURACION
-- =============================================
CREATE OR ALTER TRIGGER TRG_TAB_CONFIGURACION_INSERT
ON ODS.TAB_CONFIGURACION
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @idUsuario INT = CAST(SESSION_CONTEXT(N'user_id') AS INT);
    
    IF @idUsuario IS NULL
        SET @idUsuario = 1;
    
    INSERT INTO ODS.TAB_AUDIT_LOG (
        idUsuario,
        accion,
        stored_procedure,
        descripcion,
        fecha_hora,
        datos_nuevos,
        resultado
    )
    SELECT 
        @idUsuario,
        'INSERT_CONFIGURACION',
        'SP_INSERT_CONFIGURACION',
        'Producto asignado al mercado - Código: ' + i.codigo + ', Mercado ID: ' + CAST(i.idMercado AS VARCHAR(10)),
        GETDATE(),
        '{"idConfiguracion":' + CAST(i.idConfiguracion AS VARCHAR(10)) + ',"codigo":"' + i.codigo + '","idMercado":' + CAST(i.idMercado AS VARCHAR(10)) + ',"fuente":"' + ISNULL(i.fuente, '') + '"}',
        'EXITOSO'
    FROM inserted i;
END;
GO

-- =============================================
-- TRIGGER PARA UPDATE EN TAB_CONFIGURACION
-- =============================================
CREATE OR ALTER TRIGGER TRG_TAB_CONFIGURACION_UPDATE
ON ODS.TAB_CONFIGURACION
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @idUsuario INT = CAST(SESSION_CONTEXT(N'user_id') AS INT);
    
    IF @idUsuario IS NULL
        SET @idUsuario = 1;
    
    INSERT INTO ODS.TAB_AUDIT_LOG (
        idUsuario,
        accion,
        stored_procedure,
        descripcion,
        fecha_hora,
        datos_anteriores,
        datos_nuevos,
        resultado
    )
    SELECT 
        @idUsuario,
        'UPDATE_CONFIGURACION',
        'SP_UPDATE_CONFIGURACION',
        'Configuración actualizada - Código: ' + i.codigo + ', Mercado: ' + CAST(d.idMercado AS VARCHAR(10)) + ' → ' + CAST(i.idMercado AS VARCHAR(10)),
        GETDATE(),
        '{"idConfiguracion":' + CAST(d.idConfiguracion AS VARCHAR(10)) + ',"codigo":"' + d.codigo + '","idMercado":' + CAST(d.idMercado AS VARCHAR(10)) + ',"fuente":"' + ISNULL(d.fuente, '') + '"}',
        '{"idConfiguracion":' + CAST(i.idConfiguracion AS VARCHAR(10)) + ',"codigo":"' + i.codigo + '","idMercado":' + CAST(i.idMercado AS VARCHAR(10)) + ',"fuente":"' + ISNULL(i.fuente, '') + '"}',
        'EXITOSO'
    FROM inserted i
    INNER JOIN deleted d ON i.idConfiguracion = d.idConfiguracion
    WHERE i.idMercado != d.idMercado OR ISNULL(i.fuente, '') != ISNULL(d.fuente, '');
END;
GO

-- =============================================
-- TRIGGER PARA DELETE EN TAB_CONFIGURACION
-- =============================================
CREATE OR ALTER TRIGGER TRG_TAB_CONFIGURACION_DELETE
ON ODS.TAB_CONFIGURACION
AFTER DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @idUsuario INT = CAST(SESSION_CONTEXT(N'user_id') AS INT);
    
    IF @idUsuario IS NULL
        SET @idUsuario = 1;
    
    INSERT INTO ODS.TAB_AUDIT_LOG (
        idUsuario,
        accion,
        stored_procedure,
        descripcion,
        fecha_hora,
        datos_anteriores,
        resultado
    )
    SELECT 
        @idUsuario,
        'DELETE_CONFIGURACION',
        'SP_ASIGNAR_RESTO',
        'Producto removido del mercado - Código: ' + d.codigo + ', Mercado ID: ' + CAST(d.idMercado AS VARCHAR(10)),
        GETDATE(),
        '{"idConfiguracion":' + CAST(d.idConfiguracion AS VARCHAR(10)) + ',"codigo":"' + d.codigo + '","idMercado":' + CAST(d.idMercado AS VARCHAR(10)) + ',"fuente":"' + ISNULL(d.fuente, '') + '"}',
        'EXITOSO'
    FROM deleted d;
END;
GO

-- =============================================
-- PROCEDIMIENTO PARA ESTABLECER CONTEXTO DE USUARIO
-- =============================================
CREATE OR ALTER PROCEDURE ODS.SP_SET_USER_CONTEXT
    @UserId INT
AS
BEGIN
    SET NOCOUNT ON;
    EXEC sp_set_session_context @key = N'user_id', @value = @UserId;
END;
GO

PRINT 'Triggers de auditoría creados exitosamente:';
PRINT '- TRG_TAB_MERCADO_INSERT';
PRINT '- TRG_TAB_MERCADO_UPDATE';
PRINT '- TRG_TAB_MERCADO_DELETE';
PRINT '- TRG_TAB_CONFIGURACION_INSERT';
PRINT '- TRG_TAB_CONFIGURACION_UPDATE';
PRINT '- TRG_TAB_CONFIGURACION_DELETE';
PRINT '- SP_SET_USER_CONTEXT (para establecer contexto de usuario)';
PRINT '';
PRINT 'NOTA: Antes de ejecutar operaciones, establece el contexto del usuario con:';
PRINT 'EXEC ODS.SP_SET_USER_CONTEXT @UserId = [ID_DEL_USUARIO]';
