<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación del Sistema</title>
</head>
<body style="background-color: #f3f4f6; font-family: sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden;">
            <!-- Header -->
            <div style="background-color: #1f2937; color: white; text-align: center; padding: 20px 24px;">
                <?php if($actionType === 'create'): ?>
                    <h1 style="font-size: 20px; font-weight: normal; margin: 0;">🎯 Nuevo Mercado Creado</h1>
                <?php elseif($actionType === 'update'): ?>
                    <h1 style="font-size: 20px; font-weight: normal; margin: 0;">✏️ Mercado Actualizado</h1>
                <?php elseif($actionType === 'assign_product'): ?>
                    <h1 style="font-size: 20px; font-weight: normal; margin: 0;">📦 Producto Asignado</h1>
                <?php elseif($actionType === 'move_product'): ?>
                    <h1 style="font-size: 20px; font-weight: normal; margin: 0;">🔄 Producto Cambiado de Mercado</h1>
                <?php elseif($actionType === 'remove_product'): ?>
                    <h1 style="font-size: 20px; font-weight: normal; margin: 0;">🗑️ Producto Removido</h1>
                <?php else: ?>
                    <h1 style="font-size: 20px; font-weight: normal; margin: 0;">📢 Notificación del Sistema</h1>
                <?php endif; ?>
                <p style="font-size: 14px; margin: 8px 0 0 0;">Sistema Medifarma - Gestión de Mercados</p>
            </div>
            
            <!-- Content -->
            <div style="padding: 24px;">
                <!-- Notification Badge -->
                <?php if($actionType === 'create'): ?>
                    <div style="background-color: #059669; color: white; text-align: center; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600;">
                        ✅ Se ha creado un nuevo mercado en el sistema
                    </div>
                <?php elseif($actionType === 'update'): ?>
                    <div style="background-color: #0369a1; color: white; text-align: center; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600;">
                        🔄 Se ha actualizado un mercado en el sistema
                    </div>
                <?php elseif($actionType === 'assign_product'): ?>
                    <div style="background-color: #7c3aed; color: white; text-align: center; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600;">
                        📦 Se han asignado productos a un mercado
                    </div>
                <?php elseif($actionType === 'move_product'): ?>
                    <div style="background-color: #ea580c; color: white; text-align: center; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600;">
                        🔄 Se ha movido un producto entre mercados
                    </div>
                <?php elseif($actionType === 'remove_product'): ?>
                    <div style="background-color: #dc2626; color: white; text-align: center; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600;">
                        🗑️ Se ha removido un producto de un mercado
                    </div>
                <?php elseif($actionType === 'test'): ?>
                    <div style="background-color: #8b5cf6; color: white; text-align: center; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600;">
                        🧪 Notificación de Prueba del Sistema
                    </div>
                <?php endif; ?>
                
                <!-- Action Details -->
                <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 20px; margin: 20px 0; border-radius: 4px;">
                    <?php if($actionType === 'create'): ?>
                        <!-- Creación de mercado -->
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Nombre del mercado:</span>
                            <span style="color: #111827; font-weight: 600;"><?php echo e($actionData['market_name']); ?></span>
                        </div>
                        
                    <?php elseif($actionType === 'update'): ?>
                        <!-- Actualización de mercado -->
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Mercado:</span>
                            <span style="color: #111827; font-weight: 600;"><?php echo e($actionData['market_name']); ?></span>
                        </div>
                        <?php if(isset($actionData['old_name']) && isset($actionData['new_name'])): ?>
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Nombre anterior:</span>
                            <span style="color: #dc2626;"><?php echo e($actionData['old_name']); ?></span>
                        </div>
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Nombre nuevo:</span>
                            <span style="color: #059669; font-weight: 600;"><?php echo e($actionData['new_name']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                    <?php elseif($actionType === 'assign_product'): ?>
                        <!-- Asignación de productos -->
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Mercado destino:</span>
                            <span style="color: #059669; font-weight: 600;"><?php echo e($actionData['market_name']); ?></span>
                        </div>
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Productos asignados:</span>
                            <span style="color: #059669; font-weight: 600;"><?php echo e($actionData['assigned_count']); ?> productos</span>
                        </div>
                        <?php if(isset($actionData['assigned_products']) && is_array($actionData['assigned_products'])): ?>
                        <div style="padding: 12px 0;">
                            <span style="font-weight: bold; color: #4b5563; display: block; margin-bottom: 8px;">Productos asignados:</span>
                            <div style="background-color: #f9fafb; padding: 12px; border-radius: 4px; border-left: 4px solid #7c3aed;">
                                <?php $__currentLoopData = $actionData['assigned_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div style="margin-bottom: 8px; padding: 8px; background-color: #ffffff; border-radius: 4px; border: 1px solid #e5e7eb;">
                                        <?php if(is_array($product)): ?>
                                            <div style="color: #111827; font-weight: 600; margin-bottom: 4px;"><?php echo e($product['name'] ?? 'Nombre no disponible'); ?></div>
                                            <div style="color: #6b7280; font-family: monospace; font-size: 13px;">Código: <?php echo e($product['code']); ?></div>
                                        <?php else: ?>
                                            <span style="color: #111827; font-family: monospace; font-size: 14px;"><?php echo e($product); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    <?php elseif($actionType === 'move_product'): ?>
                        <!-- Cambio de producto entre mercados -->
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Producto:</span>
                            <span style="color: #111827; font-weight: 600;"><?php echo e($actionData['product_name'] ?? $actionData['product_code']); ?></span>
                        </div>
                        <?php if(isset($actionData['from_market'])): ?>
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Mercado anterior:</span>
                            <span style="color: #dc2626;"><?php echo e($actionData['from_market']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Mercado nuevo:</span>
                            <span style="color: #059669; font-weight: 600;"><?php echo e($actionData['to_market']); ?></span>
                        </div>
                        
                    <?php elseif($actionType === 'remove_product'): ?>
                        <!-- Remoción de producto -->
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Producto:</span>
                            <span style="color: #111827; font-weight: 600;"><?php echo e($actionData['product_name'] ?? $actionData['product_code']); ?></span>
                        </div>
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Removido de:</span>
                            <span style="color: #dc2626;"><?php echo e($actionData['market_name']); ?></span>
                        </div>
                        
                    <?php elseif($actionType === 'test'): ?>
                        <!-- Notificación de prueba -->
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Mensaje:</span>
                            <span style="color: #111827; font-weight: 600;"><?php echo e($actionData['message']); ?></span>
                        </div>
                        <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                            <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Fecha de prueba:</span>
                            <span style="color: #6b7280;"><?php echo e($actionData['timestamp']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Información común -->
                    <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Realizado por:</span>
                        <span style="color: #111827;"><?php echo e($performedBy); ?></span>
                    </div>
                    
                    <?php if($userFranquicia): ?>
                    <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Franquicia:</span>
                        <span style="color: #111827;"><?php echo e($userFranquicia); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div style="padding: 12px 0;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Fecha y hora:</span>
                        <span style="color: #111827;"><?php echo e(date('d/m/Y H:i:s', strtotime($performedAt))); ?></span>
                    </div>
                </div>
                
                <!-- Nota del usuario si existe -->
                <?php if(isset($actionData['user_note']) && !empty($actionData['user_note'])): ?>
                <div style="background-color: #fffbeb; border: 1px solid #fde68a; padding: 16px; margin: 20px 0; border-radius: 4px; border-left: 4px solid #f59e0b;">
                    <div style="font-weight: 600; color: #92400e; margin-bottom: 8px; display: flex; align-items: center;">
                        📝 Nota del Usuario:
                    </div>
                    <div style="color: #92400e; font-style: italic; line-height: 1.5;">
                        "<?php echo e($actionData['user_note']); ?>"
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- System Note -->
                <div style="margin-top: 24px; padding: 16px; background-color: #e5e7eb; border-radius: 4px; text-align: center; font-size: 14px; color: #4b5563;">
                    Esta es una notificación automática del sistema de gestión de mercados de Medifarma.
                </div>
            </div>
            
            <!-- Footer -->
            <div style="background-color: #f3f4f6; border-top: 1px solid #d1d5db; text-align: center; padding: 16px; font-size: 12px; color: #6b7280;">
                <div style="font-weight: 600; color: #374151;">Sistema Medifarma</div>
                <div style="margin-top: 4px;">Gestión de Mercados y Productos</div>
                <div style="margin-top: 4px;">Generado el <?php echo e(date('d/m/Y H:i:s')); ?></div>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/emails/market-action-notification.blade.php ENDPATH**/ ?>