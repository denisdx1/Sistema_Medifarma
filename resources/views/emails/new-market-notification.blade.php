<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Mercado Creado</title>
</head>
<body style="background-color: #f3f4f6; font-family: sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden;">
            <!-- Header -->
            <div style="background-color: #1f2937; color: white; text-align: center; padding: 20px 24px;">
                <h1 style="font-size: 20px; font-weight: normal; margin: 0;">🎯 Nuevo Mercado Creado</h1>
                <p style="font-size: 14px; margin: 8px 0 0 0;">Sistema Medifarma - Gestión de Mercados</p>
            </div>
            
            <!-- Content -->
            <div style="padding: 24px;">
                <!-- Notification Badge -->
                <div style="background-color: #059669; color: white; text-align: center; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600;">
                    ✅ Se ha creado un nuevo mercado en el sistema
                </div>
                
                <!-- Market Details -->
                <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 20px; margin: 20px 0; border-radius: 4px;">
                    <!-- Nombre del mercado como campo normal -->
                    <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Nombre del mercado:</span>
                        <span style="color: #111827; font-weight: 600;">{{ $marketName }}</span>
                    </div>
                    
                    <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Creado por:</span>
                        <span style="color: #111827;">{{ $createdBy }}</span>
                    </div>
                    
                    @if($userFranquicia)
                    <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Franquicia:</span>
                        <span style="color: #111827;">{{ $userFranquicia }}</span>
                    </div>
                    @endif
                    
                    <div style="padding: 12px 0; border-bottom: 1px solid #d1d5db;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Fecha creación:</span>
                        <span style="color: #111827;">{{ date('d/m/Y H:i:s', strtotime($createdAt)) }}</span>
                    </div>
                    
                    <div style="padding: 12px 0;">
                        <span style="font-weight: bold; color: #4b5563; display: inline-block; width: 140px;">Estado inicial:</span>
                        <span style="color: #065f46; background-color: #d1fae5; padding: 4px 8px; border-radius: 4px; font-size: 14px; font-weight: 500;">ACTIVO</span>
                    </div>
                </div>
                
                <!-- Actions Box -->
                <div style="background-color: #fffbeb; border: 1px solid: #fde68a; padding: 16px; margin: 20px 0; border-radius: 4px;">
                    <div style="font-weight: 600; color: #92400e; margin-bottom: 12px;">📋 Próximos pasos:</div>
                    <ul style="list-style-type: disc; padding-left: 20px; color: #92400e; margin: 0;">
                        <li style="margin-bottom: 4px;">El mercado está disponible para asignación de productos</li>
                        <li style="margin-bottom: 4px;">Se puede configurar según las necesidades del negocio</li>
                        <li style="margin-bottom: 4px;">Verifique las configuraciones del nuevo mercado en el sistema</li>
                    </ul>
                </div>
                
                <!-- System Note -->
                <div style="margin-top: 24px; padding: 16px; background-color: #e5e7eb; border-radius: 4px; text-align: center; font-size: 14px; color: #4b5563;">
                    Esta es una notificación automática del sistema de gestión de mercados de Medifarma.
                </div>
            </div>
            
            <!-- Footer -->
            <div style="background-color: #f3f4f6; border-top: 1px solid #d1d5db; text-align: center; padding: 16px; font-size: 12px; color: #6b7280;">
                <div style="font-weight: 600; color: #374151;">Sistema Medifarma</div>
                <div style="margin-top: 4px;">Gestión de Mercados y Productos</div>
                <div style="margin-top: 4px;">Generado el {{ date('d/m/Y H:i:s') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
