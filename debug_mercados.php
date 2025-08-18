<?php

require_once 'vendor/autoload.php';

use App\Models\Material;

// Inicializar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG: Datos de Mercados ===\n\n";

// Obtener una muestra de materiales con sus mercados
$materiales = Material::select('SKU', 'Descripción_Presentación', 'Mercado')
    ->limit(20)
    ->get();

foreach ($materiales as $material) {
    $mercado = $material->Mercado;
    echo "SKU: {$material->SKU}\n";
    echo "Descripción: " . substr($material->Descripción_Presentación, 0, 50) . "...\n";
    echo "Mercado RAW: '" . ($mercado ?? 'NULL') . "'\n";
    echo "Mercado TIPO: " . gettype($mercado) . "\n";
    echo "Mercado LIMPIO: '" . trim($mercado ?? '') . "'\n";
    echo "Mercado UPPER: '" . strtoupper(trim($mercado ?? '')) . "'\n";
    echo "Es vacío: " . (empty($mercado) ? 'SÍ' : 'NO') . "\n";
    echo "Es null: " . (is_null($mercado) ? 'SÍ' : 'NO') . "\n";
    echo "---\n";
}

echo "\n=== Mercados únicos en la base de datos ===\n";
$mercadosUnicos = Material::select('Mercado')
    ->distinct()
    ->whereNotNull('Mercado')
    ->orderBy('Mercado')
    ->limit(50)
    ->pluck('Mercado');

foreach ($mercadosUnicos as $mercado) {
    echo "'{$mercado}' (longitud: " . strlen($mercado) . ")\n";
}

echo "\n=== Conteos ===\n";
echo "Total materiales: " . Material::count() . "\n";
echo "Con mercado no null: " . Material::whereNotNull('Mercado')->count() . "\n";
echo "Con mercado vacío: " . Material::where('Mercado', '')->count() . "\n";
echo "Con mercado 'RESTO': " . Material::where('Mercado', 'RESTO')->count() . "\n";
