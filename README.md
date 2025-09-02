# 🏥 Sistema de Gestión Medifarma

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Sistema integral de gestión empresarial desarrollado para Medifarma, que permite la administración de mercados, productos, usuarios y configuraciones del sistema de manera eficiente y segura.

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [API y Endpoints](#-api-y-endpoints)
- [Base de Datos](#-base-de-datos)
- [Seguridad](#-seguridad)
- [Contribución](#-contribución)
- [Licencia](#-licencia)

## ✨ Características

### 🎯 **Gestión de Mercados**
- Creación y administración de mercados
- Configuración de parámetros por mercado
- Asignación masiva de productos a mercados
- Gestión de estados y configuraciones

### 📦 **Base de Productos**
- Catálogo completo de productos IQVIA
- Búsqueda avanzada y filtros
- Gestión de información de productos
- Asignación masiva de productos

### 👥 **Gestión de Usuarios**
- Sistema de autenticación seguro
- Roles y permisos granulares
- Gestión de franquicias por usuario
- Cambio obligatorio de contraseña temporal

### 🔧 **Administración del Sistema**
- Configuraciones globales
- Logs de auditoría
- Gestión de parámetros del sistema
- Monitoreo de actividades

## 🛠️ Tecnologías

### **Backend**
- **Laravel 12.x** - Framework PHP moderno y robusto
- **PHP 8.2+** - Versión más reciente de PHP
- **SQL Server** - Base de datos principal
- **Stored Procedures** - Lógica de negocio optimizada

### **Frontend**
- **Tailwind CSS 3.x** - Framework CSS utilitario
- **Alpine.js** - JavaScript reactivo ligero
- **Bootstrap 5** - Componentes UI adicionales
- **Font Awesome** - Iconografía profesional

### **Herramientas de Desarrollo**
- **Vite** - Bundler y servidor de desarrollo
- **Laravel Sail** - Entorno Docker para desarrollo
- **PHPUnit** - Testing automatizado
- **Laravel Pint** - Formateo de código

## 💻 Requisitos del Sistema

### **Servidor Web**
- **PHP**: 8.2 o superior
- **Extensiones PHP**: 
  - BCMath PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - SQL Server drivers

### **Base de Datos**
- **SQL Server** 2016 o superior
- **ODBC Driver 17** para SQL Server

### **Servidor Web**
- **Apache** 2.4+ o **Nginx** 1.18+
- **Composer** 2.0+
- **Node.js** 16+ y **npm** 8+

## 🚀 Instalación

### 1. **Clonar el Repositorio**
```bash
git clone https://github.com/tu-usuario/sistema-medifarma.git
cd sistema-medifarma
```

### 2. **Instalar Dependencias PHP**
```bash
composer install --optimize-autoloader --no-dev
```

### 3. **Instalar Dependencias Node.js**
```bash
npm install
npm run build
```

### 4. **Configurar Variables de Entorno**
```bash
cp .env.example .env
php artisan key:generate
```

### 5. **Configurar Base de Datos**
Editar `.env` con las credenciales de SQL Server:
```env
DB_CONNECTION=sqlsrv
DB_HOST=tu-servidor-sql
DB_PORT=1433
DB_DATABASE=Medifarma
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-password
```

### 6. **Ejecutar Migraciones**
```bash
php artisan migrate
```

### 7. **Configurar Permisos**
```bash
chmod -R 775 storage bootstrap/cache
```

## ⚙️ Configuración

### **Configuración de LDAP (Opcional)**
```env
LDAP_HOST=tu-servidor-ldap
LDAP_USERNAME=tu-usuario-ldap
LDAP_PASSWORD=tu-password-ldap
LDAP_BASE_DN=DC=medifarma,DC=com
```

### **Configuración de Correo**
```env
MAIL_MAILER=smtp
MAIL_HOST=tu-servidor-smtp
MAIL_PORT=587
MAIL_USERNAME=tu-usuario
MAIL_PASSWORD=tu-password
MAIL_ENCRYPTION=tls
```

## 📖 Uso

### **Acceso al Sistema**
1. Navegar a la URL del sistema
2. Ingresar credenciales de usuario
3. Cambiar contraseña temporal (primer acceso)
4. Acceder a las funcionalidades según el rol

### **Roles de Usuario**
- **Administrador**: Acceso completo al sistema
- **Gerente de Producto**: Gestión de productos y mercados
- **Usuario Estándar**: Acceso limitado según permisos

### **Funcionalidades Principales**
- **Dashboard**: Vista general del sistema
- **Mercados**: Administración de mercados
- **Productos**: Gestión de base de productos
- **Usuarios**: Administración de usuarios
- **Configuración**: Parámetros del sistema

## 🏗️ Estructura del Proyecto

```
sistema-medifarma/
├── app/
│   ├── Http/Controllers/     # Controladores principales
│   ├── Models/               # Modelos de datos
│   ├── Services/             # Servicios de negocio
│   └── Middleware/           # Middlewares personalizados
├── database/
│   ├── migrations/           # Migraciones de BD
│   ├── seeders/              # Datos iniciales
│   └── stored_procedures/    # Procedimientos almacenados
├── resources/
│   ├── views/                # Vistas Blade
│   ├── css/                  # Estilos CSS
│   └── js/                   # JavaScript del frontend
├── routes/
│   ├── web.php               # Rutas web
│   └── api.php               # Rutas API
└── public/                   # Archivos públicos
```

## 🔌 API y Endpoints

### **Autenticación**
- `POST /login` - Inicio de sesión
- `POST /logout` - Cierre de sesión
- `POST /cambio-password-obligatorio` - Cambio de contraseña

### **Mercados**
- `GET /market-management` - Lista de mercados
- `POST /market-management` - Crear mercado
- `PUT /market-management/{id}` - Actualizar mercado
- `DELETE /market-management/{id}` - Eliminar mercado

### **Productos**
- `GET /productos` - Lista de productos
- `POST /productos` - Crear producto
- `PUT /productos/{id}` - Actualizar producto
- `POST /productos/bulk-actions` - Acciones masivas

### **Usuarios**
- `GET /usuarios` - Lista de usuarios
- `POST /usuarios` - Crear usuario
- `PUT /usuarios/{id}` - Actualizar usuario
- `POST /usuarios/{id}/toggle-estado` - Cambiar estado

## 🗄️ Base de Datos

### **Tablas Principales**
- `ODS.TAB_USUARIO` - Usuarios del sistema
- `ODS.TAB_MERCADO` - Configuración de mercados
- `ODS.TAB_PRODUCTO` - Catálogo de productos
- `ODS.TAB_FRANQUICIA` - Franquicias disponibles
- `ODS.TAB_CONFIGURACION` - Parámetros del sistema

### **Stored Procedures**
- `ODS.SP_INSERT_USUARIO` - Crear usuario
- `ODS.SP_UPDATE_USUARIO` - Actualizar usuario
- `ODS.SP_DELETE_USUARIO` - Eliminar usuario
- `ODS.SP_ASIGNAR_PRODUCTOS_MERCADO` - Asignar productos

## 🔒 Seguridad

### **Autenticación**
- Sistema de login seguro
- Contraseñas hasheadas con SHA-256
- Cambio obligatorio de contraseña temporal
- Sesiones seguras

### **Autorización**
- Control de acceso basado en roles
- Permisos granulares por funcionalidad
- Validación de datos en frontend y backend
- Protección CSRF

### **Auditoría**
- Logs de todas las acciones críticas
- Registro de cambios en configuraciones
- Trazabilidad de operaciones masivas
- Historial de accesos

## 🧪 Testing

### **Ejecutar Tests**
```bash
# Tests unitarios
php artisan test

# Tests con cobertura
php artisan test --coverage

# Tests específicos
php artisan test --filter=UserTest
```

### **Entorno de Testing**
```bash
# Usar base de datos de testing
php artisan test --env=testing

# Ejecutar tests en paralelo
php artisan test --parallel
```

## 🐳 Docker (Opcional)

### **Usar Laravel Sail**
```bash
# Iniciar servicios
./vendor/bin/sail up

# Ejecutar comandos
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev

# Detener servicios
./vendor/bin/sail down
```

## 📝 Contribución

1. **Fork** el proyecto
2. **Crea** una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Push** a la rama (`git push origin feature/AmazingFeature`)
5. **Abre** un Pull Request

### **Estándares de Código**
- Seguir PSR-12 para PHP
- Usar Laravel Pint para formateo
- Documentar funciones y clases
- Escribir tests para nuevas funcionalidades

## 🚨 Solución de Problemas

### **Problemas Comunes**

#### **Error de Conexión a BD**
```bash
# Verificar configuración
php artisan config:cache
php artisan config:clear

# Verificar drivers SQL Server
php -m | grep sqlsrv
```

#### **Problemas de Permisos**
```bash
# Corregir permisos de storage
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### **Problemas de Vite**
```bash
# Limpiar cache
npm run build
php artisan view:clear
```

## 📞 Soporte

- **Email**: soporte@medifarma.com
- **Documentación**: [Wiki del Proyecto](link-al-wiki)
- **Issues**: [GitHub Issues](link-a-issues)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## �� Agradecimientos

- **Laravel Team** por el framework excepcional
- **Tailwind CSS** por el sistema de diseño
- **Medifarma** por la confianza en el desarrollo
- **Contribuidores** del proyecto

---

<div align="center">

**Desarrollado con ❤️ para Medifarma**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)

</div>
