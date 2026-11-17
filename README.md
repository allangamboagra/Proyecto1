# SIGEL: Sistema de Gestión de Préstamo de Equipos de Laboratorio

Aplicación web en PHP 8 y MySQL/MariaDB, sin framework, para gestionar el inventario y los préstamos de equipos de un laboratorio.

## Requisitos

- macOS con XAMPP (Apache, PHP 8.x y MariaDB/MySQL). En Windows o Linux los pasos son los mismos, cambiando las rutas.
- Un navegador con cookies habilitadas.

## Instalación desde cero

1. Abra el panel de XAMPP e inicie **Apache** y **MySQL**.
2. Copie la carpeta `proyecto1` dentro de `htdocs`, de modo que quede en `/Applications/XAMPP/xamppfiles/htdocs/proyecto1`.
3. Cree la base de datos `Proyecto1AllanG` con cotejamiento `utf8mb4_unicode_ci`. Luego, en phpMyAdmin, seleccione esa base e importe primero `sql/schema.sql` y después `sql/seed.sql`.
4. Revise `config/database.php` y ponga el usuario y la contraseña de su MySQL. Este archivo contiene credenciales, así que no debe subirse al repositorio.
5. Configure el virtual host. Abra `/Applications/XAMPP/xamppfiles/etc/extra/httpd-vhosts.conf` y agregue:

   ```apache
   <VirtualHost *:80>
       ServerName proyecto1.test
       DocumentRoot "/Applications/XAMPP/xamppfiles/htdocs/proyecto1/public"
       <Directory "/Applications/XAMPP/xamppfiles/htdocs/proyecto1/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   Verifique que en `httpd.conf` esté activa la línea `Include etc/extra/httpd-vhosts.conf`.
6. Agregue el dominio local. Desde Terminal ejecute `sudo nano /etc/hosts` y agregue la línea `127.0.0.1 proyecto1.test`.
7. Reinicie Apache desde el panel de XAMPP y abra `http://proyecto1.test`.
8. Permisos de la carpeta de fotos. Apache debe poder escribir en `storage/uploads`. En desarrollo local puede usar:

   ```bash
   sudo chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/proyecto1/storage
   ```

   Esto es solo para desarrollo. Si vuelve a copiar el proyecto, repita este paso.

## Actualizar una base de datos existente

Si la base se creó antes de agregar el bloqueo de cuentas, ejecute en `Proyecto1AllanG`:

```sql
ALTER TABLE usuarios ADD COLUMN bloqueado_hasta DATETIME NULL AFTER intentos_fallidos;
```

Una instalación nueva ya incluye esta columna en `sql/schema.sql`.

## Credenciales de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@lab.local | admin |
| Operador de inventario | operador@lab.local | operador |

## Qué puede hacer cada rol

- **Administrador:** gestiona usuarios, categorías y equipos. Sube, reemplaza y elimina fotos de los equipos. Consulta reportes y configura los días de anticipación para los préstamos.
- **Operador de inventario:** registra préstamos y cambia su estado. Consulta equipos y ve sus fotos, sin poder modificarlas.

## Seguridad y funciones principales

- **Sesión:** se cierra después de 30 minutos sin actividad.
- **Bloqueo de cuenta:** tras 10 intentos fallidos seguidos, la cuenta se bloquea 15 minutos.
- **Recordarme:** mantiene la sesión durante 14 días mediante un token guardado en la base. Cada uso genera un token nuevo.
- **Cookies:** las cookies de sesión y de "Recordarme" usan HttpOnly y SameSite=Lax. Secure se activa cuando el sitio usa HTTPS.
- **CSRF:** todos los formularios que modifican datos incluyen un token de la sesión, y el servidor lo valida antes de actuar.
- **Fotos:** JPG, PNG o WEBP, de 2 MB o menos cada una, con un máximo de 5 por equipo. Se guardan en `storage/uploads`, fuera de `public/`, con nombres generados por el servidor.
- **Cookies de preferencia:** la cantidad de filas por página se recuerda en cada pantalla, y los equipos consultados recientemente por el operador se muestran en la pantalla de equipos.

## Estructura del proyecto

```
proyecto1/
├── config/        conexión a la base de datos (no se sube al repositorio)
├── includes/      sesión, CSRF, helpers, consultas y reglas de negocio
├── public/        único directorio expuesto por Apache
│   ├── pages/     vistas
│   ├── actions/   procesamiento de formularios (POST)
│   ├── assets/    CSS
│   └── index.php  inicio de sesión
├── sql/           schema.sql y seed.sql
├── storage/       fotos subidas (sin acceso directo por URL)
└── README.md
```
