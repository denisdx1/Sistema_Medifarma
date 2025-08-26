<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMarketNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $actionType;
    public $actionData;
    public $performedBy;
    public $performedAt;
    public $userFranquicia;

    /**
     * Create a new message instance.
     * 
     * @param string $actionType Tipo de acción: 'create', 'update', 'assign_product', 'move_product', 'remove_product'
     * @param array $actionData Datos específicos de la acción
     * @param string $performedBy Usuario que realizó la acción
     * @param string $performedAt Fecha/hora de la acción
     * @param string|null $userFranquicia Franquicia del usuario
     */
    public function __construct($actionType, $actionData, $performedBy, $performedAt, $userFranquicia = null)
    {
        $this->actionType = $actionType;
        $this->actionData = $actionData;
        $this->performedBy = $performedBy;
        $this->performedAt = $performedAt;
        $this->userFranquicia = $userFranquicia;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'create' => 'Nuevo Mercado Creado - ' . ($this->actionData['market_name'] ?? ''),
            'update' => 'Mercado Actualizado - ' . ($this->actionData['market_name'] ?? ''),
            'assign_product' => 'Producto Asignado a Mercado - ' . ($this->actionData['market_name'] ?? ''),
            'move_product' => 'Producto Cambiado de Mercado - ' . ($this->actionData['product_name'] ?? ''),
            'remove_product' => 'Producto Removido de Mercado - ' . ($this->actionData['market_name'] ?? ''),
        ];

        $subject = $subjects[$this->actionType] ?? 'Notificación del Sistema - Gestión de Mercados';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.market-action-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
