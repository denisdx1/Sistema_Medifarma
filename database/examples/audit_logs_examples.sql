-- Ejemplos de uso del sistema de auditoría con Stored Procedures
-- Sistema Medifarma - Logs de Auditoría para SP

-- ==========================================
-- EJEMPLO 1: Crear mercado con SP_INSERT_MERCADO
-- ==========================================

-- Ejecución del SP con auditoría automática:

EXEC ODS.SP_INSERT_MERCADO_WITH_AUDIT
    @mercado = 'Mercado Premium Plus',
    @idUsuario = 1,
    @nombreUsuario = 'Juan Pérez',
    @emailUsuario = 'juan.perez@medifarma.com',
    @ip = '192.168.1.100',
    @userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    @sesionId = 'ABC123DEF456',
    @modulo = 'market-management',
    @controlador = 'MarketManagementController';

-- Esto generará un log en TAB_AUDIT_LOGS como:
/*
idLog: 1
idUsuario: 1
nombreUsuario: 'Juan Pérez'
emailUsuario: 'juan.perez@medifarma.com'
accion: 'SP_INSERT_MERCADO'
tabla: 'TAB_MERCADO'
descripcion: 'Creación de mercado mediante stored procedure (SP: SP_INSERT_MERCADO)'
registroId: '15' (ID del mercado creado)
valoresNuevos: '{"mercado": "Mercado Premium Plus", "idMercado": 15, "solicitud": "ESPERA"}'
ip: '192.168.1.100'
userAgent: 'Mozilla/5.0...'
sesionId: 'ABC123DEF456'
exitoso: 1
fechaHora: '2025-08-19 14:30:15.123'
duracionMs: 45
modulo: 'market-management'
controlador: 'MarketManagementController'
metadatos: '{"stored_procedure": "SP_INSERT_MERCADO", "sp_parameters": "{\"mercado\": \"Mercado Premium Plus\"}"}'
*/

-- ==========================================
-- EJEMPLO 2: Actualizar mercado con SP_UPDATE_MERCADO
-- ==========================================

EXEC ODS.SP_UPDATE_MERCADO_WITH_AUDIT
    @idMercado = 15,
    @nuevoNombre = 'Mercado Premium Plus Actualizado',
    @idUsuario = 1,
    @nombreUsuario = 'Juan Pérez',
    @emailUsuario = 'juan.perez@medifarma.com',
    @ip = '192.168.1.100',
    @modulo = 'market-management',
    @controlador = 'MarketManagementController';

-- Esto generará un log como:
/*
accion: 'SP_UPDATE_MERCADO'
descripcion: 'Actualización de nombre de mercado (SP: SP_UPDATE_MERCADO)'
registroId: '15'
valoresAnteriores: '{"mercado": "Mercado Premium Plus"}'
valoresNuevos: '{"mercado": "Mercado Premium Plus Actualizado"}'
metadatos: '{"stored_procedure": "SP_UPDATE_MERCADO", "sp_parameters": "{\"idMercado\": 15, \"nuevoNombre\": \"Mercado Premium Plus Actualizado\"}"}'
*/

-- ==========================================
-- EJEMPLO 3: Intento fallido (mercado duplicado)
-- ==========================================

EXEC ODS.SP_INSERT_MERCADO_WITH_AUDIT
    @mercado = 'Mercado Premium Plus Actualizado', -- Nombre que ya existe
    @idUsuario = 2,
    @nombreUsuario = 'María García',
    @emailUsuario = 'maria.garcia@medifarma.com',
    @ip = '192.168.1.101';

-- Esto generará un log de error como:
/*
accion: 'SP_INSERT_MERCADO_FAILED'
descripcion: 'Intento de crear mercado con nombre duplicado (SP: SP_INSERT_MERCADO)'
valoresNuevos: 'Mercado Premium Plus Actualizado'
exitoso: 0
mensajeError: 'Ya existe un mercado con ese nombre'
*/

-- ==========================================
-- CONSULTAS ÚTILES PARA AUDITORÍA
-- ==========================================

-- 1. Ver todos los logs de un usuario específico
SELECT 
    fechaHora,
    accion,
    tabla,
    descripcion,
    exitoso,
    mensajeError
FROM ODS.TAB_AUDIT_LOGS 
WHERE idUsuario = 1
ORDER BY fechaHora DESC;

-- 2. Ver todas las operaciones en TAB_MERCADO
SELECT 
    fechaHora,
    nombreUsuario,
    accion,
    descripcion,
    registroId,
    valoresAnteriores,
    valoresNuevos,
    exitoso
FROM ODS.TAB_AUDIT_LOGS 
WHERE tabla = 'TAB_MERCADO'
ORDER BY fechaHora DESC;

-- 3. Ver todas las ejecuciones de stored procedures
SELECT 
    fechaHora,
    nombreUsuario,
    accion,
    tabla,
    descripcion,
    duracionMs,
    exitoso
FROM ODS.TAB_AUDIT_LOGS 
WHERE accion LIKE 'SP_%'
ORDER BY fechaHora DESC;

-- 4. Ver operaciones fallidas
SELECT 
    fechaHora,
    nombreUsuario,
    accion,
    tabla,
    descripcion,
    mensajeError
FROM ODS.TAB_AUDIT_LOGS 
WHERE exitoso = 0
ORDER BY fechaHora DESC;

-- 5. Estadísticas de operaciones por usuario
SELECT 
    nombreUsuario,
    COUNT(*) as TotalOperaciones,
    SUM(CASE WHEN exitoso = 1 THEN 1 ELSE 0 END) as Exitosas,
    SUM(CASE WHEN exitoso = 0 THEN 1 ELSE 0 END) as Fallidas,
    AVG(duracionMs) as DuracionPromedio
FROM ODS.TAB_AUDIT_LOGS 
WHERE fechaHora >= DATEADD(DAY, -7, GETDATE()) -- Última semana
GROUP BY nombreUsuario, idUsuario
ORDER BY TotalOperaciones DESC;

-- 6. Ver cambios en un mercado específico
SELECT 
    fechaHora,
    nombreUsuario,
    accion,
    descripcion,
    valoresAnteriores,
    valoresNuevos
FROM ODS.TAB_AUDIT_LOGS 
WHERE tabla = 'TAB_MERCADO' 
    AND registroId = '15' -- ID del mercado
ORDER BY fechaHora ASC;

-- ==========================================
-- MANTENIMIENTO DE LA TABLA DE AUDITORÍA
-- ==========================================

-- Script para limpiar logs antiguos (ejecutar periódicamente)
-- Mantener solo los últimos 90 días
DELETE FROM ODS.TAB_AUDIT_LOGS 
WHERE fechaHora < DATEADD(DAY, -90, GETDATE());

-- Script para obtener el tamaño de la tabla de auditoría
SELECT 
    COUNT(*) as TotalRegistros,
    MIN(fechaHora) as FechaMinima,
    MAX(fechaHora) as FechaMaxima,
    AVG(DATALENGTH(valoresAnteriores) + DATALENGTH(valoresNuevos)) as TamanoPromedioDatos
FROM ODS.TAB_AUDIT_LOGS;
