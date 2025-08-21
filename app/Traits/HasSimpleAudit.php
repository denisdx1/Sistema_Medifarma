<?php

namespace App\Traits;

// Trait eliminado por solicitud del usuario. Contenía lógica de logs y auditoría.
?>
    /**
     * Obtener el nombre del usuario actual
     */
    protected function getCurrentUserName(): string
    {
        $user = Auth::user();
        
        if (!$user) {
            return 'SISTEMA';
        }

        // Si el usuario tiene un campo 'name', usarlo
        if (isset($user->name)) {
            return $user->name;
        }

        // Si tiene un campo 'username', usarlo
        if (isset($user->username)) {
            return $user->username;
        }

        // Si tiene email, usarlo
        if (isset($user->email)) {
            return $user->email;
        }

        // Fallback al ID del usuario
        return "Usuario #{$user->id}";
    }

    /**
     * Registrar un log de auditoría
     */
    protected function auditLog(string $accion, string $descripcion, ?string $nombreUsuario = null): void
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        SimpleAuditService::log($usuario, $accion, $descripcion);
    }

    /**
     * Crear mercado con auditoría automática
     */
    protected function auditCreateMercado(string $mercado, ?string $nombreUsuario = null): array
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        return SimpleAuditService::createMercado($mercado, $usuario);
    }

    /**
     * Actualizar mercado con auditoría automática
     */
    protected function auditUpdateMercado(int $idMercado, string $nuevoNombre, ?string $nombreUsuario = null): array
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        return SimpleAuditService::updateMercado($idMercado, $nuevoNombre, $usuario);
    }

    /**
     * Aprobar mercado con auditoría automática
     */
    protected function auditApproveMercado(int $idMercado, ?string $nombreUsuario = null): array
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        return SimpleAuditService::approveMercado($idMercado, $usuario);
    }

    /**
     * Logs específicos para acciones comunes
     */
    protected function auditLogin(?string $nombreUsuario = null): void
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        SimpleAuditService::logLogin($usuario);
    }

    protected function auditLogout(?string $nombreUsuario = null): void
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        SimpleAuditService::logLogout($usuario);
    }

    protected function auditView(string $vista, ?string $nombreUsuario = null): void
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        SimpleAuditService::logView($usuario, $vista);
    }

    protected function auditSearch(string $criterio, ?string $nombreUsuario = null): void
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        SimpleAuditService::logSearch($usuario, $criterio);
    }

    protected function auditExport(string $tipo, ?string $nombreUsuario = null): void
    {
        $usuario = $nombreUsuario ?? $this->getCurrentUserName();
        SimpleAuditService::logExport($usuario, $tipo);
    }
}
