<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Mercado;

try {
    echo "Verificando estructura de la vista DM.MERCADO...\n\n";
    
    // Obtener un mercado para ver su estructura
    $mercado = Mercado::first();
    
    if ($mercado) {
        echo "Estructura del modelo Mercado:\n";
        echo "ID: " . $mercado->idMercado . "\n";
        echo "Nombre: " . $mercado->mercado . "\n";
        echo "Atributos disponibles: " . implode(', ', array_keys($mercado->getAttributes())) . "\n\n";
        
        echo "✅ La vista DM.MERCADO funciona correctamente\n";
        echo "Total de mercados: " . Mercado::count() . "\n";
    } else {
        echo "❌ No se encontraron mercados en la vista DM.MERCADO\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al consultar la vista DM.MERCADO:\n";
    echo $e->getMessage() . "\n";
}
