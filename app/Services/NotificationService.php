<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\NewMarketNotification;

class NotificationService
{
    /**
     * Lista de correos que recibirán notificaciones de administrador (fallback)
     */
    private $adminEmails = [
        'druizp@medifarma.com.pe'
    ];

    /**
     * Incluir siempre el email del usuario que realiza la acción
     */
    private $includeUserEmail = true;

    /**
     * Obtener todos los correos de usuarios activos del sistema (solo emails reales)
     */
    private function getAllUserEmails()
    {
        try {
            $userEmails = DB::connection('sqlsrv')
                ->table('ODS.TAB_USUARIO as u')
                ->join('ODS.TAB_ESTADO as e', 'u.idEstado', '=', 'e.idEstado')
                ->where('e.estado', 'ACTIVO')
                ->whereNotNull('u.email')
                ->where('u.email', '!=', '')
                ->where('u.email', 'NOT LIKE', '%@medifarma.com') // Excluir emails temporales
                ->pluck('u.email')
                ->toArray();

            // Si no hay emails reales en la base de datos, usar los emails de admin como fallback
            return !empty($userEmails) ? $userEmails : $this->adminEmails;

        } catch (\Exception $e) {
            Log::error('Error obteniendo emails de usuarios', [
                'error' => $e->getMessage()
            ]);
            
            // En caso de error, usar emails de admin como fallback
            return $this->adminEmails;
        }
    }

    /**
     * Enviar notificación genérica para cualquier acción de mercado
     * 
     * @param string $actionType Tipo de acción: 'create', 'update', 'assign_product', 'move_product', 'remove_product'
     * @param array $actionData Datos específicos de la acción
     * @param object $user Usuario que realizó la acción
     * @param string|null $note Nota adicional del usuario
     * @return bool
     */
    public function notifyMarketAction($actionType, $actionData, $user, $note = null)
    {
        try {
            $userFranquicia = $this->getUserFranquicia($user);
            $performedAt = now()->format('Y-m-d H:i:s');
            
            // Obtener todos los correos de usuarios activos
            $allUserEmails = $this->getAllUserEmails();
            
            // Empezar con los emails de admin
            $emailsToSend = $this->adminEmails;
            
            // Agregar el email del usuario que realiza la acción si está configurado y existe
            $userEmail = null;
            if ($this->includeUserEmail) {
                $userEmail = $this->getUserEmail($user);
                if ($userEmail && !in_array($userEmail, $emailsToSend)) {
                    $emailsToSend[] = $userEmail;
                }
            }
            
            // Agregar nota si existe
            if ($note) {
                $actionData['user_note'] = $note;
            }
            
            // Log detallado para debugging
            Log::info('DEBUG: Preparando envío de notificación', [
                'accion' => $actionType,
                'realizado_por' => $user->usuario ?? 'Unknown',
                'user_id' => $user->idUsuario ?? 'Unknown',
                'user_role' => $user->idRol ?? 'Unknown',
                'user_email_from_accessor' => $user->email ?? 'No disponible',
                'user_email_real_found' => $userEmail,
                'user_email_will_be_included' => $userEmail ? 'YES' : 'NO',
                'admin_emails' => $this->adminEmails,
                'all_active_user_emails' => $allUserEmails,
                'final_emails_to_send' => $emailsToSend,
                'total_recipients' => count($emailsToSend),
                'datos_accion' => $actionData,
                'nota_usuario' => $note
            ]);
            
            // Crear instancia del Mailable genérico
            $mail = new NewMarketNotification(
                $actionType,
                $actionData,
                $user->usuario,
                $performedAt,
                $userFranquicia
            );

            // Enviar a los emails configurados
            if (!empty($emailsToSend)) {
                Mail::to($emailsToSend[0])
                    ->cc(array_slice($emailsToSend, 1))
                    ->send($mail);
                    
                Log::info('DEBUG: Email enviado exitosamente', [
                    'destinatarios' => $emailsToSend,
                    'incluye_usuario_accion' => $userEmail ? 'Sí' : 'No'
                ]);
            } else {
                Log::warning('DEBUG: No hay emails configurados para envío');
            }

            // Log de la notificación enviada
            Log::info('Notificación de acción de mercado enviada', [
                'accion' => $actionType,
                'datos_accion' => $actionData,
                'realizado_por' => $user->usuario,
                'email_usuario' => $userEmail,
                'franquicia' => $userFranquicia,
                'fecha' => $performedAt,
                'correos_enviados' => $emailsToSend,
                'total_destinatarios' => count($emailsToSend)
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error enviando notificación de acción de mercado', [
                'error' => $e->getMessage(),
                'accion' => $actionType,
                'datos_accion' => $actionData,
                'usuario' => $user->usuario ?? 'Unknown'
            ]);
            
            return false;
        }
    }

    /**
     * Notificar creación de mercado
     */
    public function notifyNewMarket($marketName, $user, $note = null)
    {
        return $this->notifyMarketAction('create', [
            'market_name' => $marketName
        ], $user, $note);
    }

    /**
     * Notificar actualización de mercado
     */
    public function notifyMarketUpdate($oldName, $newName, $user, $note = null)
    {
        return $this->notifyMarketAction('update', [
            'market_name' => $newName,
            'old_name' => $oldName,
            'new_name' => $newName
        ], $user, $note);
    }

    /**
     * Notificar asignación de producto a mercado
     */
    public function notifyProductAssigned($productCode, $productName, $marketName, $user, $note = null)
    {
        return $this->notifyMarketAction('assign_product', [
            'product_code' => $productCode,
            'product_name' => $productName,
            'market_name' => $marketName
        ], $user, $note);
    }

    /**
     * Notificar cambio de producto entre mercados
     */
    public function notifyProductMoved($productCode, $productName, $fromMarket, $toMarket, $user, $note = null)
    {
        return $this->notifyMarketAction('move_product', [
            'product_code' => $productCode,
            'product_name' => $productName,
            'from_market' => $fromMarket,
            'to_market' => $toMarket
        ], $user, $note);
    }

    /**
     * Notificar remoción de producto de mercado
     */
    public function notifyProductRemoved($productCode, $productName, $marketName, $user, $note = null)
    {
        return $this->notifyMarketAction('remove_product', [
            'product_code' => $productCode,
            'product_name' => $productName,
            'market_name' => $marketName
        ], $user, $note);
    }

    /**
     * Obtener información de franquicia del usuario
     */
    private function getUserFranquicia($user)
    {
        if (!$user) {
            return 'Sin franquicia asignada';
        }

        try {
            // Usar el método del modelo User para obtener la primera franquicia
            return $user->getFranquiciaNombre();
        } catch (\Exception $e) {
            Log::error('Error obteniendo franquicia del usuario', [
                'user_id' => $user->idUsuario ?? 'Unknown',
                'error' => $e->getMessage()
            ]);
            return 'Error obteniendo franquicia';
        }
    }

    /**
     * Obtener email del usuario que realiza la acción
     */
    private function getUserEmail($user)
    {
        if (!$user) {
            return null;
        }

        try {
            // Usar el accessor del modelo que ya maneja emails reales vs temporales
            $userEmail = $user->email;
            
            // Verificar que no sea un email temporal generado automáticamente
            // Solo retornar emails que no sean del formato login@medifarma.com
            if ($userEmail && !str_ends_with($userEmail, '@medifarma.com')) {
                return $userEmail;
            }
            
            // Si el email del accessor es temporal, buscar un email real en la base de datos
            $realEmail = DB::connection('sqlsrv')
                ->table('ODS.TAB_USUARIO')
                ->where('idUsuario', $user->idUsuario)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where('email', 'NOT LIKE', '%@medifarma.com')
                ->value('email');

            return $realEmail;

        } catch (\Exception $e) {
            Log::error('Error obteniendo email del usuario', [
                'user_id' => $user->idUsuario ?? 'Unknown',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Configurar correos de administrador
     */
    public function setAdminEmails(array $emails)
    {
        $this->adminEmails = $emails;
    }

    /**
     * Obtener correos de administrador configurados
     */
    public function getAdminEmails()
    {
        return $this->adminEmails;
    }

    /**
     * Configurar si incluir el email del usuario que realiza la acción
     */
    public function setIncludeUserEmail(bool $include)
    {
        $this->includeUserEmail = $include;
    }

    /**
     * Verificar si se incluye el email del usuario
     */
    public function getIncludeUserEmail()
    {
        return $this->includeUserEmail;
    }

    /**
     * Obtener información sobre los destinatarios de las notificaciones
     */
    public function getNotificationRecipientsInfo()
    {
        $allUserEmails = $this->getAllUserEmails();
        
        return [
            'total_recipients' => count($allUserEmails),
            'recipients' => $allUserEmails,
            'using_fallback' => $allUserEmails === $this->adminEmails
        ];
    }

    /**
     * Método para probar el envío de notificaciones
     */
    public function testNotification($user, $note = null)
    {
        $testNote = $note ?: 'Esta es una notificación de prueba del sistema de correos.';
        
        return $this->notifyMarketAction('test', [
            'message' => 'Prueba del sistema de notificaciones',
            'timestamp' => now()->format('Y-m-d H:i:s')
        ], $user, $testNote);
    }

    /**
     * Método de debugging para verificar qué emails se están obteniendo
     */
    public function debugEmailCollection($user = null)
    {
        $debugInfo = [
            'admin_emails' => $this->adminEmails,
            'include_user_email' => $this->includeUserEmail,
            'all_user_emails' => $this->getAllUserEmails(),
        ];

        if ($user) {
            $userEmail = $this->getUserEmail($user);
            $debugInfo['current_user'] = [
                'id' => $user->idUsuario ?? 'Unknown',
                'name' => $user->usuario ?? 'Unknown',
                'email_from_accessor' => $user->email ?? 'No email',
                'real_email_found' => $userEmail,
                'will_receive_notification' => $userEmail ? 'YES' : 'NO'
            ];

            // Obtener email directamente de la base de datos para comparar
            try {
                $dbEmail = DB::connection('sqlsrv')
                    ->table('ODS.TAB_USUARIO')
                    ->where('idUsuario', $user->idUsuario)
                    ->value('email');
                $debugInfo['current_user']['email_in_database'] = $dbEmail;
            } catch (\Exception $e) {
                $debugInfo['current_user']['email_in_database'] = 'Error: ' . $e->getMessage();
            }
        }

        Log::info('DEBUG: Email Collection Info', $debugInfo);
        
        return $debugInfo;
    }
}
