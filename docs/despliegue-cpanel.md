# Despliegue en cPanel (PHP 8.4 + MySQL 8)

Guía para el hosting objetivo (CLAUDE.md §15). Antes de desplegar, la suite debe estar en
verde en **MySQL 8** en GitHub Actions.

Los comandos usan `~/espe-academico` como carpeta del proyecto y `demo.midominio.com` como
subdominio de ejemplo. Sustitúyalos por los suyos.

## 0. Comprobaciones previas (por SSH)

```bash
php -v                         # debe ser 8.4.x; si no, use MultiPHP Manager (o ea-php84)
php -m | grep -Ei 'pdo_mysql|mbstring|openssl|tokenizer|xml|ctype|fileinfo|intl'
composer --version             # si no existe: /opt/cpanel/composer/bin/composer
mysql --version                # MySQL 8.0.16 o superior (necesario para CHECK)
```

En cPanel, compruebe también:

- **MultiPHP Manager:** el subdominio usa PHP 8.4.
- **SSL/TLS Status:** AutoSSL está activo para el subdominio.
- **Dominios:** se puede cambiar el *Document Root* del subdominio.

## 1. Base de datos

En **cPanel → MySQL Databases**:

1. Cree la base (por ejemplo, `usuario_espe`) y un usuario exclusivo con una contraseña fuerte.
2. Asigne al usuario solo los privilegios necesarios: `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES, LOCK TABLES`. `CREATE`, `ALTER`, `INDEX`, `DROP` y `REFERENCES` son para las migraciones.

## 2. Código (fuera de `public_html`)

```bash
cd ~
git clone <url-del-repositorio> espe-academico
cd espe-academico
composer install --no-dev --optimize-autoloader --no-interaction
```

## 3. Variables de entorno

```bash
cp .env.example .env
php artisan key:generate
chmod 600 .env
nano .env
```

Valores de producción:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://demo.midominio.com
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=usuario_espe
DB_USERNAME=usuario_espeapp
DB_PASSWORD=********

SESSION_SECURE_COOKIE=true
DEMO_PASSWORD=********        # solo si se cargarán los datos de demostración
```

## 4. Document Root → `public/`

Solo la carpeta `public` debe ser accesible desde la web.

- **Opción A (recomendada):** en **cPanel → Dominios**, cambie el *Document Root* del subdominio a `espe-academico/public`.
- **Opción B:** si no se puede cambiar el Document Root, use un enlace simbólico:

  ```bash
  mv ~/public_html ~/public_html.bak      # o la carpeta del subdominio
  ln -s ~/espe-academico/public ~/public_html
  ```

**Nunca** copie el proyecto completo dentro de `public_html`.

## 5. Permisos

```bash
chmod -R 775 storage bootstrap/cache
find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

## 6. Migraciones (tras un respaldo)

Si la base ya tiene datos, haga primero un respaldo (cPanel → Backup, o
`mysqldump -u usuario_espeapp -p usuario_espe > ~/respaldo-$(date +%F).sql`).

```bash
php artisan migrate --force
php artisan db:seed --force       # solo para el entorno de demostración
```

**No** ejecute `migrate:fresh`, `migrate:refresh`, `db:wipe` ni `migrate:rollback` en producción.

## 6-bis. Sin terminal: ruta `/init`

Si no puede usar SSH para los pasos 6 y 7:

1. En el `.env` del servidor, defina `INIT_TOKEN` con al menos 32 caracteres aleatorios.
2. Abra `https://espe.tudominio.com/init?token=SU_TOKEN`. Para cargar también los datos de demostración, añada `&seed=1`.
3. La página muestra la salida de `optimize:clear`, `migrate --force`, `db:seed` (si se pidió), `route:cache` y `view:cache`.
4. Al terminar, **vacíe `INIT_TOKEN`** en `.env`: la ruta queda desactivada y responde 404.

La ruta no acepta comandos del cliente y nunca ejecuta `migrate:fresh`, `db:wipe` ni `rollback`.

## 7. Optimización

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Después de cada cambio de `.env` o de código, vuelva a ejecutar `php artisan optimize:clear`
y los tres comandos anteriores.

## 8. HTTPS

Active **Force HTTPS Redirect** para el subdominio en **cPanel → Dominios**. La aplicación
envía `Strict-Transport-Security` cuando la solicitud llega por HTTPS, y con
`SESSION_SECURE_COOKIE=true` la cookie de sesión solo viaja cifrada.

## 9. Verificación posterior

Abra `https://demo.midominio.com` y compruebe:

- [ ] El login funciona y `inactivo@demo.test` no puede entrar.
- [ ] El docente 1 recibe 403 en `/admin/users` y en la planilla del NRC 10235.
- [ ] Matricular, registrar notas y el reporte del NRC 10234 funcionan (41,99 → REPROBADO).
- [ ] `https://demo.midominio.com/.env` y `/composer.json` devuelven 404.
- [ ] Un error no muestra trazas (APP_DEBUG=false).
- [ ] `storage/logs/laravel.log` no contiene errores nuevos.

## 10. Respaldos

- Programe en cPanel respaldos de la base y de la carpeta `~/espe-academico/storage`.
- Pruebe una restauración en otra base antes de confiar en ellos.

## Actualizar a una nueva versión

```bash
cd ~/espe-academico
php artisan down
git pull
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force      # tras respaldo
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```
