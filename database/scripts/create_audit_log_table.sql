-- Script para crear tabla de auditoría de acciones de usuarios
-- Sistema Medifarma - Tabla de Logs de Auditoría

USE [TuBaseDeDatos]  -- Reemplaza con el nombre de tu base de datos
GO

-- Crear tabla de logs de auditoría
CREATE TABLE ODS.TAB_AUDIT_LOG (
    idAuditLog INT IDENTITY(1,1) PRIMARY KEY,
    idUsuario INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    stored_procedure VARCHAR(100) NULL,
    descripcion NVARCHAR(500) NOT NULL,
    fecha_hora DATETIME2 NOT NULL DEFAULT GETDATE(),
    ip_address VARCHAR(45) NULL,
    datos_anteriores NVARCHAR(MAX) NULL,
    datos_nuevos NVARCHAR(MAX) NULL,
    resultado VARCHAR(20) NOT NULL DEFAULT 'EXITOSO', -- EXITOSO, ERROR
    mensaje_error NVARCHAR(1000) NULL
);

-- Crear índices para optimizar consultas
CREATE INDEX IX_TAB_AUDIT_LOG_Usuario ON ODS.TAB_AUDIT_LOG (idUsuario);
CREATE INDEX IX_TAB_AUDIT_LOG_Fecha ON ODS.TAB_AUDIT_LOG (fecha_hora);
CREATE INDEX IX_TAB_AUDIT_LOG_Accion ON ODS.TAB_AUDIT_LOG (accion);
CREATE INDEX IX_TAB_AUDIT_LOG_SP ON ODS.TAB_AUDIT_LOG (stored_procedure);

-- Agregar relación con tabla de usuarios
ALTER TABLE ODS.TAB_AUDIT_LOG 
ADD CONSTRAINT FK_TAB_AUDIT_LOG_Usuario 
FOREIGN KEY (idUsuario) REFERENCES ODS.TAB_USUARIO(idUsuario);

-- Agregar comentarios a la tabla
EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Tabla de auditoría para registrar todas las acciones realizadas por los usuarios del sistema',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'ID único del registro de auditoría',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'idAuditLog';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'ID del usuario que realizó la acción',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'idUsuario';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Tipo de acción realizada (INSERT_MERCADO, UPDATE_MERCADO, DELETE_MERCADO, etc.)',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'accion';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Nombre del stored procedure ejecutado',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'stored_procedure';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Descripción detallada de la acción realizada',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'descripcion';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Fecha y hora cuando se realizó la acción',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'fecha_hora';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Dirección IP desde donde se realizó la acción',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'ip_address';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Datos antes del cambio (formato JSON)',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'datos_anteriores';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Datos después del cambio (formato JSON)',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'datos_nuevos';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Resultado de la operación: EXITOSO o ERROR',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'resultado';

EXEC sys.sp_addextendedproperty 
    @name = N'MS_Description',
    @value = N'Mensaje de error en caso de fallo',
    @level0type = N'SCHEMA', @level0name = N'ODS',
    @level1type = N'TABLE', @level1name = N'TAB_AUDIT_LOG',
    @level2type = N'COLUMN', @level2name = N'mensaje_error';

PRINT 'Tabla ODS.TAB_AUDIT_LOG creada exitosamente con índices y relaciones.';
