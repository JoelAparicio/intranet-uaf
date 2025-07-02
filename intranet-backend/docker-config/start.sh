#!/bin/sh
# intranet-backend/docker-config/start.sh
# Script de inicio para Laravel con todas las funcionalidades

echo "🚀 Iniciando Intranet UAF Backend..."

# Crear directorios necesarios
mkdir -p /var/www/html/storage/logs
mkdir -p /var/log/supervisor
touch /var/www/html/storage/logs/laravel.log

# Esperar a que SQL Server esté listo
echo "⏳ Esperando conexión a SQL Server..."
until php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch(Exception \$e) { throw \$e; }" 2>/dev/null | grep -q "OK"; do
    echo "Esperando base de datos..."
    sleep 5
done
echo "✅ SQL Server conectado!"

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    echo "📝 Generando APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Ejecutar migraciones
echo "🔄 Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders específicos (solo si es primera vez)
echo "🌱 Verificando seeders..."

# Verificar si ya hay usuarios (para evitar duplicados)
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 Ejecutando seeders iniciales..."
    php artisan db:seed --class=DepartamentoSeeder --force 2>/dev/null || echo "⚠️ DepartamentoSeeder no disponible"
    php artisan db:seed --class=TiposolicitudSeeder --force 2>/dev/null || echo "⚠️ TiposolicitudSeeder no disponible"
    php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>/dev/null || echo "⚠️ RolesAndPermissionsSeeder no disponible"
    php artisan db:seed --class=UserSeeder --force 2>/dev/null || echo "⚠️ UserSeeder no disponible"
else
    echo "✅ Base de datos ya inicializada (usuarios: $USER_COUNT)"
fi

# Configurar storage
echo "🔗 Configurando storage..."
php artisan storage:link --force

# Configurar sistema de colas
echo "📋 Configurando sistema de colas..."
php artisan queue:table 2>/dev/null || echo "Tabla de jobs ya existe"
php artisan migrate --force

# Limpiar y optimizar cachés
echo "🧹 Optimizando aplicación..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# Establecer permisos finales
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "👷 Iniciando Supervisor (Queue Workers)..."
# Iniciar Supervisor en background
supervisord -c /etc/supervisor/conf.d/supervisord.conf &

echo "🔧 Iniciando PHP-FPM..."
# Iniciar PHP-FPM en foreground (proceso principal)
exec php-fpm

echo "✅ Intranet UAF Backend iniciado correctamente!"
echo "📍 API disponible en: http://localhost/api"
echo "📊 Queue Workers: Activos para procesamiento de PDFs"
