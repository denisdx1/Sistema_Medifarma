<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Medifarma - Notificación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
            color: #333333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #dddddd;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #dee2e6;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333333;
        }
        .notification-type {
            margin-top: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #666666;
        }
        .content {
            padding: 25px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 15px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 5px;
        }
        .detail-row {
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #666666;
            display: inline-block;
            min-width: 140px;
        }
        .detail-value {
            color: #333333;
        }
        .products-list {
            background-color: #f8f9fa;
            padding: 15px;
            margin-top: 10px;
            border-left: 3px solid #dee2e6;
        }
        .product-item {
            padding: 3px 0;
            color: #555555;
        }
        .note-section {
            background-color: #fffbf0;
            padding: 15px;
            border-left: 3px solid #ffc107;
            margin-top: 20px;
        }
        .note-title {
            font-weight: bold;
            color: #333333;
            margin-bottom: 8px;
        }
        .note-content {
            color: #555555;
            font-style: italic;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #666666;
        }
        .brand {
            font-weight: bold;
            color: #333333;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .content {
                padding: 15px;
            }
            .detail-label {
                min-width: auto;
                display: block;
                margin-bottom: 3px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>SISTEMA MEDIFARMA</h1>
            <div class="notification-type">
                @if($actionType === 'create')
                    NUEVO MERCADO CREADO
                @elseif($actionType === 'update')
                    MERCADO ACTUALIZADO
                @elseif($actionType === 'assign_product')
                    PRODUCTO ASIGNADO
                @elseif($actionType === 'move_product')
                    PRODUCTO CAMBIADO DE MERCADO
                @elseif($actionType === 'change_market')
                    CAMBIO MASIVO DE MERCADO
                @elseif($actionType === 'remove_product')
                    PRODUCTOS REMOVIDOS
                @else
                    NOTIFICACIÓN DEL SISTEMA
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Action Details -->
            <div class="section">
                @if($actionType === 'create')
                    <div class="section-title">Se ha creado un nuevo mercado en el sistema</div>
                    <div class="detail-row">
                        <span class="detail-label">Nombre del mercado:</span>
                        <span class="detail-value">{{ $actionData['market_name'] }}</span>
                    </div>

                @elseif($actionType === 'update')
                    <div class="section-title">Se ha actualizado un mercado en el sistema</div>
                    <div class="detail-row">
                        <span class="detail-label">Mercado:</span>
                        <span class="detail-value">{{ $actionData['market_name'] }}</span>
                    </div>
                    @if(isset($actionData['old_name']) && isset($actionData['new_name']))
                    <div class="detail-row">
                        <span class="detail-label">Nombre anterior:</span>
                        <span class="detail-value">{{ $actionData['old_name'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nombre nuevo:</span>
                        <span class="detail-value">{{ $actionData['new_name'] }}</span>
                    </div>
                    @endif

                @elseif($actionType === 'assign_product')
                    <div class="section-title">Se han asignado productos a un mercado</div>
                    <div class="detail-row">
                        <span class="detail-label">Mercado destino:</span>
                        <span class="detail-value">{{ $actionData['market_name'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Productos asignados:</span>
                        <span class="detail-value">{{ $actionData['assigned_count'] }} productos</span>
                    </div>

                    @if(isset($actionData['assigned_products']) && is_array($actionData['assigned_products']))
                    <div class="products-list">
                        <strong>Productos asignados:</strong>
                        @foreach($actionData['assigned_products'] as $index => $product)
                        <div class="product-item">
                            @if(is_array($product))
                                • {{ $product['name'] ?? 'Nombre no disponible' }} (Código: {{ $product['code'] }})
                            @else
                                • {{ $product }}
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                @elseif($actionType === 'move_product')
                    <div class="section-title">Se ha movido un producto entre mercados</div>
                    <div class="detail-row">
                        <span class="detail-label">Producto:</span>
                        <span class="detail-value">{{ $actionData['product_name'] ?? $actionData['product_code'] }}</span>
                    </div>
                    @if(isset($actionData['product_code']) && isset($actionData['product_name']))
                    <div class="detail-row">
                        <span class="detail-label">Código:</span>
                        <span class="detail-value">{{ $actionData['product_code'] }}</span>
                    </div>
                    @endif
                    @if(isset($actionData['from_market']))
                    <div class="detail-row">
                        <span class="detail-label">Mercado anterior:</span>
                        <span class="detail-value">{{ $actionData['from_market'] }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Mercado nuevo:</span>
                        <span class="detail-value">{{ $actionData['to_market'] }}</span>
                    </div>

                @elseif($actionType === 'remove_product')
                    <div class="section-title">Se han removido productos de mercados</div>
                    <div class="detail-row">
                        <span class="detail-label">Total de productos:</span>
                        <span class="detail-value">{{ $actionData['removed_count'] }} productos</span>
                    </div>

                    @if(isset($actionData['removed_products']) && is_array($actionData['removed_products']))
                    <div class="products-list">
                        <strong>Productos removidos:</strong>
                        @foreach($actionData['removed_products'] as $index => $product)
                        <div class="product-item">
                            @if(is_array($product))
                                • {{ $product['name'] ?? 'Nombre no disponible' }} (Código: {{ $product['code'] }})
                                <br>
                                <small style="color: #666; margin-left: 20px;">
                                    Removido de: <strong>{{ $product['previous_market'] }}</strong>
                                </small>
                            @else
                                • {{ $product }}
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                @elseif($actionType === 'change_market')
                    <div class="section-title">Se han cambiado productos de mercado masivamente</div>
                    <div class="detail-row">
                        <span class="detail-label">Mercado destino:</span>
                        <span class="detail-value">{{ $actionData['new_market_name'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total de productos:</span>
                        <span class="detail-value">{{ $actionData['changed_count'] }} productos</span>
                    </div>

                    @if(isset($actionData['changed_products']) && is_array($actionData['changed_products']))
                    <div class="products-list">
                        <strong>Productos cambiados:</strong>
                        @foreach($actionData['changed_products'] as $index => $product)
                        <div class="product-item">
                            @if(is_array($product))
                                • {{ $product['name'] ?? 'Nombre no disponible' }} (Código: {{ $product['code'] }})
                                <br>
                                <small style="color: #666; margin-left: 20px;">
                                    Cambiado de: <strong>{{ $product['previous_market'] }}</strong> → <strong>{{ $product['new_market'] }}</strong>
                                </small>
                            @else
                                • {{ $product }}
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                @elseif($actionType === 'test')
                    <div class="section-title">Notificación de prueba del sistema</div>
                    <div class="detail-row">
                        <span class="detail-label">Mensaje:</span>
                        <span class="detail-value">{{ $actionData['message'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha de prueba:</span>
                        <span class="detail-value">{{ $actionData['timestamp'] }}</span>
                    </div>
                @endif
            </div>

            <!-- Information Section -->
            <div class="section">
                <div class="section-title">Información de la Operación</div>
                <div class="detail-row">
                    <span class="detail-label">Realizado por:</span>
                    <span class="detail-value">{{ $performedBy }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha y hora:</span>
                    <span class="detail-value">{{ date('d/m/Y H:i:s', strtotime($performedAt)) }}</span>
                </div>
            </div>

            <!-- User Note Section -->
            @if(isset($actionData['user_note']) && !empty($actionData['user_note']))
            <div class="note-section">
                <div class="note-title">Nota del Usuario</div>
                <div class="note-content">"{{ $actionData['user_note'] }}"</div>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Esta es una notificación automática del <span class="brand">Sistema Medifarma</span></p>
            <p>Gestión de Mercados • Generado el {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
