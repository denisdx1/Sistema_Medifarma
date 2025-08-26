<?php
// Script de prueba para el sistema de notas
// Ejecutar con: php test_note_email.php

require_once 'vendor/autoload.php';

use App\Services\NotificationService;
use App\Models\User;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Crear usuario de prueba
$testUser = new \stdClass();
$testUser->idUsuario = 1;
$testUser->usuario = 'Usuario de Prueba';
$testUser->email = 'test@medifarma.com.pe';

// Crear servicio de notificación
$notificationService = new NotificationService();

// Probar notificación con nota
echo "Enviando email de prueba con nota...\n";

$result = $notificationService->notifyNewMarket(
    'Mercado de Prueba - Con Nota',
    $testUser,
    'Esta es una nota de prueba para verificar que el sistema de notas funciona correctamente. El mercado fue creado como parte de las pruebas del sistema de notificaciones.'
);

if ($result) {
    echo "✅ Email enviado exitosamente con nota incluida!\n";
    echo "Revisa tu bandeja de entrada para ver cómo se muestra la nota.\n";
} else {
    echo "❌ Error enviando el email\n";
}
