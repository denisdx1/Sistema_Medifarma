<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\MarketConfigurationController;
use Illuminate\Http\Request;

try {
    echo "Probando el método getAllMarkets()...\n\n";
    
    $controller = new MarketConfigurationController(new \App\Services\MarketConfigurationService());
    $response = $controller->getAllMarkets();
    
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ El método getAllMarkets() funciona correctamente\n";
        echo "Total de mercados: " . count($data['markets']) . "\n";
        echo "Primeros 3 mercados:\n";
        
        foreach (array_slice($data['markets'], 0, 3) as $market) {
            echo "- ID: {$market['id_mercado']}, Nombre: {$market['mercado']}\n";
        }
    } else {
        echo "❌ Error en getAllMarkets(): " . ($data['message'] ?? 'Sin mensaje') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al probar getAllMarkets():\n";
    echo $e->getMessage() . "\n";
}
