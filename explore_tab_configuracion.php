<?php
try {
    $pdo = new PDO('sqlsrv:Server=localhost;Database=ODS', 'sa', 'Sql2022**');
    $stmt = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                         FROM INFORMATION_SCHEMA.COLUMNS 
                         WHERE TABLE_SCHEMA = 'ODS' AND TABLE_NAME = 'TAB_CONFIGURACION'
                         ORDER BY ORDINAL_POSITION");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Estructura de la tabla ODS.TAB_CONFIGURACION:\n";
    foreach($columns as $column) {
        echo "- {$column['COLUMN_NAME']} ({$column['DATA_TYPE']}) - Nullable: {$column['IS_NULLABLE']}\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
