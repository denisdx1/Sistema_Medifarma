<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ExtraFarmaciaSeeder extends Seeder{
    public function run(){
        // Franquicias farmacéuticas
        DB::table('franchises')->insert([
            ['name' => 'Farmacias Cruz Verde', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias Salcobrand', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias Ahumada', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias del Dr. Simi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias San Pablo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias Guadalajara', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias del Ahorro', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias Similares', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias Benavides', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Farmacias YZA', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Unidades de negocio farmacéuticas
        DB::table('business_units')->insert([
            ['name' => 'Medicamentos de Venta Libre', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medicamentos con Receta', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Suplementos Nutricionales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Productos de Higiene Personal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cosméticos y Belleza', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Productos para Bebés', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dispositivos Médicos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Productos Naturales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Productos Ortopédicos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Productos Veterinarios', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Marcas farmacéuticas reales
        DB::table('brands')->insert([
            ['name' => 'Bayer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pfizer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Johnson & Johnson', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Novartis', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Roche', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GSK (GlaxoSmithKline)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sanofi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Abbott', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Merck', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AstraZeneca', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Boehringer Ingelheim', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bristol Myers Squibb', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Eli Lilly', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Takeda', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Amgen', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Genfar', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tecnoquímicas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lafrancol', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MK (Merck Sharp & Dohme)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siegfried', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}