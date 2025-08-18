<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Market;

class MarketSeederComplete extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $markets = [
            [
                'name' => 'Mercado Nacional',
                'code' => 'NAC',
                'description' => 'Mercado para productos de distribución nacional en todo el territorio peruano',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Regional Lima',
                'code' => 'LIM',
                'description' => 'Mercado específico para la región de Lima Metropolitana y Callao',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Regional Norte',
                'code' => 'NOR',
                'description' => 'Mercado para las regiones del norte: Piura, Lambayeque, La Libertad, Cajamarca',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Regional Sur',
                'code' => 'SUR',
                'description' => 'Mercado para las regiones del sur: Arequipa, Cusco, Puno, Tacna',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Exportación',
                'code' => 'EXP',
                'description' => 'Mercado para productos destinados a la exportación internacional',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Institucional',
                'code' => 'INST',
                'description' => 'Mercado para ventas a instituciones públicas y privadas (hospitales, clínicas)',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Farmacias Cadena',
                'code' => 'CADENA',
                'description' => 'Mercado específico para grandes cadenas de farmacias',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Farmacias Independientes',
                'code' => 'INDEP',
                'description' => 'Mercado para farmacias independientes y boticas locales',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Online',
                'code' => 'ONLINE',
                'description' => 'Mercado para ventas a través de plataformas digitales y e-commerce',
                'is_active' => true
            ],
            [
                'name' => 'Mercado Genéricos',
                'code' => 'GEN',
                'description' => 'Mercado específico para medicamentos genéricos de bajo costo',
                'is_active' => true
            ]
        ];

        foreach ($markets as $marketData) {
            Market::updateOrCreate(
                ['code' => $marketData['code']],
                $marketData
            );
        }

        $this->command->info('Mercados creados exitosamente.');
    }
}
