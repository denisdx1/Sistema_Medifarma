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
                <?php if($actionType === 'create'): ?>
                    NUEVO MERCADO CREADO
                <?php elseif($actionType === 'update'): ?>
                    MERCADO ACTUALIZADO
                <?php elseif($actionType === 'assign_product'): ?>
                    PRODUCTO ASIGNADO
                <?php elseif($actionType === 'move_product'): ?>
                    PRODUCTO CAMBIADO DE MERCADO
                <?php elseif($actionType === 'change_market'): ?>
                    CAMBIO MASIVO DE MERCADO
                <?php elseif($actionType === 'remove_product'): ?>
                    PRODUCTOS REMOVIDOS
                <?php else: ?>
                    NOTIFICACIÓN DEL SISTEMA
                <?php endif; ?>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Action Details -->
            <div class="section">
                <?php if($actionType === 'create'): ?>
                    <div class="section-title">Se ha creado un nuevo mercado en el sistema</div>
                    <div class="detail-row">
                        <span class="detail-label">Nombre del mercado:</span>
                        <span class="detail-value"><?php echo e($actionData['market_name']); ?></span>
                    </div>

                <?php elseif($actionType === 'update'): ?>
                    <div class="section-title">Se ha actualizado un mercado en el sistema</div>
                    <div class="detail-row">
                        <span class="detail-label">Mercado:</span>
                        <span class="detail-value"><?php echo e($actionData['market_name']); ?></span>
                    </div>
                    <?php if(isset($actionData['old_name']) && isset($actionData['new_name'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Nombre anterior:</span>
                        <span class="detail-value"><?php echo e($actionData['old_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nombre nuevo:</span>
                        <span class="detail-value"><?php echo e($actionData['new_name']); ?></span>
                    </div>
                    <?php endif; ?>

                <?php elseif($actionType === 'assign_product'): ?>
                    <div class="section-title">Se han asignado productos a un mercado</div>
                    <div class="detail-row">
                        <span class="detail-label">Mercado destino:</span>
                        <span class="detail-value"><?php echo e($actionData['market_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Productos asignados:</span>
                        <span class="detail-value"><?php echo e($actionData['assigned_count']); ?> productos</span>
                    </div>

                    <?php if(isset($actionData['assigned_products']) && is_array($actionData['assigned_products'])): ?>
                    <div class="products-list">
                        <strong>Productos asignados:</strong>
                        <?php $__currentLoopData = $actionData['assigned_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="product-item">
                            <?php if(is_array($product)): ?>
                                • <?php echo e($product['name'] ?? 'Nombre no disponible'); ?> (Código: <?php echo e($product['code']); ?>)
                            <?php else: ?>
                                • <?php echo e($product); ?>

                            <?php endif; ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>

                <?php elseif($actionType === 'move_product'): ?>
                    <div class="section-title">Se ha movido un producto entre mercados</div>
                    <div class="detail-row">
                        <span class="detail-label">Producto:</span>
                        <span class="detail-value"><?php echo e($actionData['product_name'] ?? $actionData['product_code']); ?></span>
                    </div>
                    <?php if(isset($actionData['product_code']) && isset($actionData['product_name'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Código:</span>
                        <span class="detail-value"><?php echo e($actionData['product_code']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($actionData['from_market'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Mercado anterior:</span>
                        <span class="detail-value"><?php echo e($actionData['from_market']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="detail-label">Mercado nuevo:</span>
                        <span class="detail-value"><?php echo e($actionData['to_market']); ?></span>
                    </div>

                <?php elseif($actionType === 'remove_product'): ?>
                    <div class="section-title">Se ha removido un producto de un mercado</div>
                    
                    <?php if(isset($actionData['removed_count']) && isset($actionData['removed_products'])): ?>
                        <!-- Remoción masiva -->
                        <div class="detail-row">
                            <span class="detail-label">Total de productos:</span>
                            <span class="detail-value"><?php echo e($actionData['removed_count']); ?> productos</span>
                        </div>

                        <?php if(isset($actionData['removed_products']) && is_array($actionData['removed_products'])): ?>
                        <div class="products-list">
                            <strong>Productos removidos:</strong>
                            <?php $__currentLoopData = $actionData['removed_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="product-item">
                                <?php if(is_array($product)): ?>
                                    • <?php echo e($product['name'] ?? 'Nombre no disponible'); ?> (Código: <?php echo e($product['code']); ?>)
                                    <br>
                                    <small style="color: #666; margin-left: 20px;">
                                        Removido de: <strong><?php echo e($product['previous_market']); ?></strong>
                                    </small>
                                <?php else: ?>
                                    • <?php echo e($product); ?>

                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Remoción individual -->
                        <div class="detail-row">
                            <span class="detail-label">Producto:</span>
                            <span class="detail-value"><?php echo e($actionData['product_name'] ?? $actionData['product_code']); ?></span>
                        </div>
                        <?php if(isset($actionData['product_code']) && isset($actionData['product_name'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Código:</span>
                            <span class="detail-value"><?php echo e($actionData['product_code']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="detail-label">Removido de:</span>
                            <span class="detail-value"><?php echo e($actionData['market_name']); ?></span>
                        </div>
                    <?php endif; ?>

                <?php elseif($actionType === 'change_market'): ?>
                    <div class="section-title">Se han cambiado productos de mercado masivamente</div>
                    <div class="detail-row">
                        <span class="detail-label">Mercado destino:</span>
                        <span class="detail-value"><?php echo e($actionData['new_market_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total de productos:</span>
                        <span class="detail-value"><?php echo e($actionData['changed_count']); ?> productos</span>
                    </div>

                    <?php if(isset($actionData['changed_products']) && is_array($actionData['changed_products'])): ?>
                    <div class="products-list">
                        <strong>Productos cambiados:</strong>
                        <?php $__currentLoopData = $actionData['changed_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="product-item">
                            <?php if(is_array($product)): ?>
                                • <?php echo e($product['name'] ?? 'Nombre no disponible'); ?> (Código: <?php echo e($product['code']); ?>)
                                <br>
                                <small style="color: #666; margin-left: 20px;">
                                    Cambiado de: <strong><?php echo e($product['previous_market']); ?></strong> → <strong><?php echo e($product['new_market']); ?></strong>
                                </small>
                            <?php else: ?>
                                • <?php echo e($product); ?>

                            <?php endif; ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>

                <?php elseif($actionType === 'test'): ?>
                    <div class="section-title">Notificación de prueba del sistema</div>
                    <div class="detail-row">
                        <span class="detail-label">Mensaje:</span>
                        <span class="detail-value"><?php echo e($actionData['message']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha de prueba:</span>
                        <span class="detail-value"><?php echo e($actionData['timestamp']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Information Section -->
            <div class="section">
                <div class="section-title">Información de la Operación</div>
                <div class="detail-row">
                    <span class="detail-label">Realizado por:</span>
                    <span class="detail-value"><?php echo e($performedBy); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha y hora:</span>
                    <span class="detail-value"><?php echo e(date('d/m/Y H:i:s', strtotime($performedAt))); ?></span>
                </div>
            </div>

            <!-- User Note Section -->
            <?php if(isset($actionData['user_note']) && !empty($actionData['user_note'])): ?>
            <div class="note-section">
                <div class="note-title">Nota del Usuario</div>
                <div class="note-content">"<?php echo e($actionData['user_note']); ?>"</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Esta es una notificación automática del <span class="brand">Sistema Medifarma</span></p>
            <p>Gestión de Mercados • Generado el <?php echo e(date('d/m/Y H:i:s')); ?></p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/emails/market-action-notification.blade.php ENDPATH**/ ?>