-- =====================================================
-- SISTEMA DE LOGS PARA GESTIÓN DE MERCADOS Y PRODUCTOS
-- =====================================================
-- Este script crea la tabla de logs y los triggers necesarios
-- para auditar todas las acciones en mercados y productos

-- =====================================================
-- 1. CREAR TABLA DE LOGS
-- =====================================================
CREATE TABLE ODS.TAB_LOGS (
    idLog BIGINT IDENTITY(1,1) PRIMARY KEY,
    tipoAccion VARCHAR(50) NOT NULL,           -- 'INSERT', 'UPDATE', 'DELETE'
    tablaAfectada VARCHAR(100) NOT NULL,       -- Nombre de la tabla afectada
    operacion VARCHAR(100) NOT NULL,           -- Descripción de la operación
    idRegistro VARCHAR(50),                    -- ID del registro afectado
    datosAnteriores NVARCHAR(MAX),            -- JSON con datos anteriores (para UPDATE/DELETE)
    datosNuevos NVARCHAR(MAX),                -- JSON con datos nuevos (para INSERT/UPDATE)
    idUsuario INT,                            -- ID del usuario que realizó la acción
    nombreUsuario VARCHAR(100),               -- Nombre del usuario
    direccionIP VARCHAR(45),                  -- IP desde donde se realizó la acción
    userAgent VARCHAR(500),                   -- User Agent del navegador
    fechaAccion DATETIME2 DEFAULT GETDATE(),  -- Fecha y hora de la acción
    detallesAdicionales NVARCHAR(MAX),        -- Información adicional en JSON
    sessionId VARCHAR(100),                   -- ID de la sesión
    aplicacion VARCHAR(50) DEFAULT 'MEDIFARMA_WEB', -- Aplicación que realizó la acción
    INDEX IX_TAB_LOGS_Fecha (fechaAccion),
    INDEX IX_TAB_LOGS_Usuario (idUsuario),
    INDEX IX_TAB_LOGS_Tabla (tablaAfectada),
    INDEX IX_TAB_LOGS_Tipo (tipoAccion)
);

-- =====================================================
-- 2. TRIGGER PARA TAB_MERCADO
-- =====================================================

-- Trigger para INSERT en TAB_MERCADO
CREATE TRIGGER TR_MERCADO_INSERT
ON ODS.TAB_MERCADO
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    INSERT INTO ODS.TAB_LOGS (
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosNuevos,
        fechaAccion,
        detallesAdicionales
    )
    SELECT 
        'INSERT',
        'TAB_MERCADO',
        'Creación de nuevo mercado',
        CAST(i.idMercado AS VARCHAR(50)),
        (SELECT 
            i.idMercado,
            i.mercado,
            i.idEstado,
            i.fechaRegistro,
            i.fechaModificacion
         FROM inserted i2 WHERE i2.idMercado = i.idMercado
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        GETDATE(),
        JSON_QUERY('{"descripcion": "Se ha creado un nuevo mercado en el sistema"}')
    FROM inserted i;
END;

-- Trigger para UPDATE en TAB_MERCADO
CREATE TRIGGER TR_MERCADO_UPDATE
ON ODS.TAB_MERCADO
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    INSERT INTO ODS.TAB_LOGS (
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosAnteriores,
        datosNuevos,
        fechaAccion,
        detallesAdicionales
    )
    SELECT 
        'UPDATE',
        'TAB_MERCADO',
        'Actualización de mercado',
        CAST(i.idMercado AS VARCHAR(50)),
        (SELECT 
            d.idMercado,
            d.mercado,
            d.idEstado,
            d.fechaRegistro,
            d.fechaModificacion
         FROM deleted d2 WHERE d2.idMercado = d.idMercado
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        (SELECT 
            i.idMercado,
            i.mercado,
            i.idEstado,
            i.fechaRegistro,
            i.fechaModificacion
         FROM inserted i2 WHERE i2.idMercado = i.idMercado
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        GETDATE(),
        JSON_QUERY('{"descripcion": "Se ha actualizado la información de un mercado"}')
    FROM inserted i
    INNER JOIN deleted d ON i.idMercado = d.idMercado;
END;

-- Trigger para DELETE en TAB_MERCADO
CREATE TRIGGER TR_MERCADO_DELETE
ON ODS.TAB_MERCADO
AFTER DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    INSERT INTO ODS.TAB_LOGS (
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosAnteriores,
        fechaAccion,
        detallesAdicionales
    )
    SELECT 
        'DELETE',
        'TAB_MERCADO',
        'Eliminación de mercado',
        CAST(d.idMercado AS VARCHAR(50)),
        (SELECT 
            d.idMercado,
            d.mercado,
            d.idEstado,
            d.fechaRegistro,
            d.fechaModificacion
         FROM deleted d2 WHERE d2.idMercado = d.idMercado
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        GETDATE(),
        JSON_QUERY('{"descripcion": "Se ha eliminado un mercado del sistema"}')
    FROM deleted d;
END;

-- =====================================================
-- 3. TRIGGER PARA TAB_CONFIGURACION (Asignación de productos)
-- =====================================================

-- Trigger para INSERT en TAB_CONFIGURACION
CREATE TRIGGER TR_CONFIGURACION_INSERT
ON ODS.TAB_CONFIGURACION
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    INSERT INTO ODS.TAB_LOGS (
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosNuevos,
        fechaAccion,
        detallesAdicionales
    )
    SELECT 
        'INSERT',
        'TAB_CONFIGURACION',
        'Asignación de producto a mercado',
        CAST(i.idConfiguracion AS VARCHAR(50)),
        (SELECT 
            i.idConfiguracion,
            i.codigo,
            i.idMercado,
            i.fuente,
            i.fechaRegistro,
            i.fechaModificacion,
            m.mercado
         FROM inserted i2 
         LEFT JOIN ODS.TAB_MERCADO m ON i2.idMercado = m.idMercado
         WHERE i2.idConfiguracion = i.idConfiguracion
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        GETDATE(),
        (SELECT 
            JSON_QUERY('{"descripcion": "Se ha asignado un producto a un mercado", "codigoProducto": "' + i.codigo + '", "mercado": "' + ISNULL(m.mercado, 'N/A') + '"}')
         FROM inserted i2
         LEFT JOIN ODS.TAB_MERCADO m ON i2.idMercado = m.idMercado
         WHERE i2.idConfiguracion = i.idConfiguracion)
    FROM inserted i
    LEFT JOIN ODS.TAB_MERCADO m ON i.idMercado = m.idMercado;
END;

-- Trigger para UPDATE en TAB_CONFIGURACION
CREATE TRIGGER TR_CONFIGURACION_UPDATE
ON ODS.TAB_CONFIGURACION
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    INSERT INTO ODS.TAB_LOGS (
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosAnteriores,
        datosNuevos,
        fechaAccion,
        detallesAdicionales
    )
    SELECT 
        'UPDATE',
        'TAB_CONFIGURACION',
        'Cambio de producto entre mercados',
        CAST(i.idConfiguracion AS VARCHAR(50)),
        (SELECT 
            d.idConfiguracion,
            d.codigo,
            d.idMercado,
            d.fuente,
            d.fechaRegistro,
            d.fechaModificacion,
            m_old.mercado
         FROM deleted d2 
         LEFT JOIN ODS.TAB_MERCADO m_old ON d2.idMercado = m_old.idMercado
         WHERE d2.idConfiguracion = d.idConfiguracion
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        (SELECT 
            i.idConfiguracion,
            i.codigo,
            i.idMercado,
            i.fuente,
            i.fechaRegistro,
            i.fechaModificacion,
            m_new.mercado
         FROM inserted i2 
         LEFT JOIN ODS.TAB_MERCADO m_new ON i2.idMercado = m_new.idMercado
         WHERE i2.idConfiguracion = i.idConfiguracion
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        GETDATE(),
        (SELECT 
            JSON_QUERY('{"descripcion": "Se ha movido un producto entre mercados", "codigoProducto": "' + i.codigo + '", "mercadoAnterior": "' + ISNULL(m_old.mercado, 'N/A') + '", "mercadoNuevo": "' + ISNULL(m_new.mercado, 'N/A') + '"}')
         FROM inserted i2
         INNER JOIN deleted d2 ON i2.idConfiguracion = d2.idConfiguracion
         LEFT JOIN ODS.TAB_MERCADO m_old ON d2.idMercado = m_old.idMercado
         LEFT JOIN ODS.TAB_MERCADO m_new ON i2.idMercado = m_new.idMercado
         WHERE i2.idConfiguracion = i.idConfiguracion)
    FROM inserted i
    INNER JOIN deleted d ON i.idConfiguracion = d.idConfiguracion
    LEFT JOIN ODS.TAB_MERCADO m_old ON d.idMercado = m_old.idMercado
    LEFT JOIN ODS.TAB_MERCADO m_new ON i.idMercado = m_new.idMercado;
END;

-- Trigger para DELETE en TAB_CONFIGURACION
CREATE TRIGGER TR_CONFIGURACION_DELETE
ON ODS.TAB_CONFIGURACION
AFTER DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    INSERT INTO ODS.TAB_LOGS (
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosAnteriores,
        fechaAccion,
        detallesAdicionales
    )
    SELECT 
        'DELETE',
        'TAB_CONFIGURACION',
        'Remoción de producto de mercado',
        CAST(d.idConfiguracion AS VARCHAR(50)),
        (SELECT 
            d.idConfiguracion,
            d.codigo,
            d.idMercado,
            d.fuente,
            d.fechaRegistro,
            d.fechaModificacion,
            m.mercado
         FROM deleted d2 
         LEFT JOIN ODS.TAB_MERCADO m ON d2.idMercado = m.idMercado
         WHERE d2.idConfiguracion = d.idConfiguracion
         FOR JSON PATH, WITHOUT_ARRAY_WRAPPER),
        GETDATE(),
        (SELECT 
            JSON_QUERY('{"descripcion": "Se ha removido un producto de un mercado", "codigoProducto": "' + d.codigo + '", "mercado": "' + ISNULL(m.mercado, 'N/A') + '"}')
         FROM deleted d2
         LEFT JOIN ODS.TAB_MERCADO m ON d2.idMercado = m.idMercado
         WHERE d2.idConfiguracion = d.idConfiguracion)
    FROM deleted d
    LEFT JOIN ODS.TAB_MERCADO m ON d.idMercado = m.idMercado;
END;

-- =====================================================
-- 4. PROCEDIMIENTOS PARA CONSULTAR LOGS
-- =====================================================

-- Procedimiento para obtener logs por rango de fechas
CREATE PROCEDURE ODS.SP_GET_LOGS_BY_DATE
    @FechaInicio DATETIME2,
    @FechaFin DATETIME2,
    @TipoAccion VARCHAR(50) = NULL,
    @TablaAfectada VARCHAR(100) = NULL
AS
BEGIN
    SELECT 
        idLog,
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosAnteriores,
        datosNuevos,
        idUsuario,
        nombreUsuario,
        direccionIP,
        fechaAccion,
        detallesAdicionales,
        aplicacion
    FROM ODS.TAB_LOGS
    WHERE fechaAccion BETWEEN @FechaInicio AND @FechaFin
        AND (@TipoAccion IS NULL OR tipoAccion = @TipoAccion)
        AND (@TablaAfectada IS NULL OR tablaAfectada = @TablaAfectada)
    ORDER BY fechaAccion DESC;
END;

-- Procedimiento para obtener logs por usuario
CREATE PROCEDURE ODS.SP_GET_LOGS_BY_USER
    @IdUsuario INT,
    @Top INT = 100
AS
BEGIN
    SELECT TOP (@Top)
        idLog,
        tipoAccion,
        tablaAfectada,
        operacion,
        idRegistro,
        datosAnteriores,
        datosNuevos,
        fechaAccion,
        detallesAdicionales
    FROM ODS.TAB_LOGS
    WHERE idUsuario = @IdUsuario
    ORDER BY fechaAccion DESC;
END;

-- =====================================================
-- 5. COMENTARIOS Y EJEMPLOS DE USO
-- =====================================================

/*
EJEMPLOS DE CONSULTAS:

-- Ver todos los logs de hoy
SELECT * FROM ODS.TAB_LOGS 
WHERE CAST(fechaAccion AS DATE) = CAST(GETDATE() AS DATE)
ORDER BY fechaAccion DESC;

-- Ver logs de creación de mercados
SELECT * FROM ODS.TAB_LOGS 
WHERE tablaAfectada = 'TAB_MERCADO' AND tipoAccion = 'INSERT'
ORDER BY fechaAccion DESC;

-- Ver logs de movimiento de productos
SELECT * FROM ODS.TAB_LOGS 
WHERE tablaAfectada = 'TAB_CONFIGURACION' AND tipoAccion = 'UPDATE'
ORDER BY fechaAccion DESC;

-- Ver logs con detalles JSON parseados
SELECT 
    idLog,
    tipoAccion,
    operacion,
    fechaAccion,
    JSON_VALUE(detallesAdicionales, '$.descripcion') as Descripcion,
    JSON_VALUE(detallesAdicionales, '$.codigoProducto') as CodigoProducto,
    JSON_VALUE(detallesAdicionales, '$.mercado') as Mercado
FROM ODS.TAB_LOGS
WHERE detallesAdicionales IS NOT NULL
ORDER BY fechaAccion DESC;

-- Usar procedimientos
EXEC ODS.SP_GET_LOGS_BY_DATE @FechaInicio = '2024-01-01', @FechaFin = '2024-12-31';
EXEC ODS.SP_GET_LOGS_BY_USER @IdUsuario = 1, @Top = 50;
*/

-- =====================================================
-- NOTAS IMPORTANTES:
-- =====================================================
/*
1. Los triggers capturan automáticamente todas las operaciones INSERT, UPDATE, DELETE
2. Los datos se almacenan en formato JSON para flexibilidad
3. Se incluyen índices para optimizar consultas frecuentes
4. Los triggers no interfieren con el rendimiento normal de las operaciones
5. Se puede extender fácilmente agregando más campos o triggers para otras tablas
6. Los procedimientos almacenados facilitan la consulta de logs
*/
