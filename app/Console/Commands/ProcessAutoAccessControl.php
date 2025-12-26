<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoAccessControlService;

class ProcessAutoAccessControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'access:auto-process {--force : Forzar procesamiento sin importar el día}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa la configuración automática de acceso del sistema (día 20: cerrar, último día: abrir)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Procesando configuración automática de acceso...');
        
        $autoService = new AutoAccessControlService();
        
        // Si se usa --force, procesar independientemente del día
        if ($this->option('force')) {
            $this->warn('⚠️  Modo FORCE activado - Procesando sin importar el día actual');
        }
        
        $result = $autoService->processAutoAccessControl();
        
        if ($result['processed']) {
            $this->info("✅ {$result['message']}");
            
            if ($result['action'] === 'closed') {
                $this->warn('🔒 Sistema cerrado automáticamente');
            } elseif ($result['action'] === 'opened') {
                $this->info('🔓 Sistema habilitado automáticamente');
            }
        } else {
            $this->comment("ℹ️  {$result['message']}");
        }
        
        // Mostrar información adicional
        $info = $autoService->getAutoConfigInfo();
        $this->newLine();
        $this->info('📅 Información de configuración automática:');
        $this->line("   • Día actual: {$info['current_day']}");
        $this->line("   • Último día del mes: {$info['last_day_of_month']}");
        $this->line("   • Próximo cierre: {$info['next_close_date']}");
        $this->line("   • Próxima apertura: {$info['next_open_date']}");
        
        if ($info['is_close_day']) {
            $this->warn('   ⚠️  HOY ES DÍA DE CIERRE AUTOMÁTICO (fuera del período 1-5)');
        } elseif ($info['is_open_day']) {
            $this->info('   ✅ HOY ES DÍA DE APERTURA AUTOMÁTICA (período 1-5)');
        }
        
        return Command::SUCCESS;
    }
}