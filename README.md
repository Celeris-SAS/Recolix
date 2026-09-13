# Recolix

Sistema de gestión de recolección de residuos de Montevideo: contenedores,
circuitos, camiones, incidencias y solicitudes de vecinos.

## Cómo correr el sistema

**Requisitos:** XAMPP (Apache + PHP 8.1+ + MySQL/MariaDB).

1. Copiar el proyecto a `htdocs` e iniciar Apache y MySQL desde el panel de XAMPP.
2. Importar `recolix.sql` (crea la base `recolix` desde cero).
3. `conexion.php` se conecta por defecto a `localhost` / usuario `root` / sin
   contraseña / base `recolix` (los valores típicos de XAMPP). Se puede
   sobreescribir con las variables de entorno `RECOLIX_DB_HOST`,
   `RECOLIX_DB_USER`, `RECOLIX_DB_PASS` y `RECOLIX_DB_NAME`.
4. Acceder:
   - Portal de vecinos: `http://localhost/Recolix/LandingPage/index_LandingPage.html`
   - Portal de empleados (Backoffice): `http://localhost/Recolix/Backoffice/login_backoffice.php`
5. Para el primer login al Backoffice, abrir una vez
   `Backoffice/crear_admin_test.php` (crea/corrige un admin: CI `12345678`,
   contraseña `admin1234`) y después borrar ese archivo.

## Cómo correr los tests

Cada API (`API_Usuarios`, `API_Gestion`, `API_Recoleccion`) tiene su carpeta
`Testing` con PHPUnit, separado en testsuite `Unit` (mocks) e `Integration`
(base de datos real). Requiere PHP CLI 8.1+ y MySQL/MariaDB corriendo; si los
scripts no encuentran los binarios, se pueden fijar con `RECOLIX_PHP_BIN` /
`RECOLIX_MYSQL_BIN`.

```bash
# 1) Una sola vez por API: descarga phpunit.phar (no se versiona en git)
./API_Gestion/Testing/install.sh

# 2) Recrea recolix_test desde recolix.sql y corre toda la suite
#    (nunca toca la base `recolix` real)
./API_Gestion/Testing/correr.sh

# Solo una testsuite, o un archivo/clase puntual
php API_Gestion/Testing/phpunit.phar --configuration API_Gestion/Testing/phpunit.xml --testsuite Unit
php API_Gestion/Testing/phpunit.phar --configuration API_Gestion/Testing/phpunit.xml API_Gestion/Testing/clases/ContenedorControllerTest.php
```

Repetir con `API_Usuarios` y `API_Recoleccion` (scripts idénticos). El
`setup-test-db.sh` exige que el nombre de la base destino contenga `test`,
como salvaguarda contra pisar la base real.

## Información del sistema

El sistema se organiza en 3 APIs independientes (requisito de la letra),
cada una con responsabilidades propias que casi no comparten reglas de
negocio, lo que permitió repartir el trabajo por módulo sin pisarnos:

- **API_Usuarios** — autenticación, perfiles, roles y solicitudes de alta de vecinos.
- **API_Gestion** — contenedores, tipos de residuo y predicciones de llenado.
- **API_Recoleccion** — camiones, traslados de residuos y mantenimientos.

Además del backend, el proyecto incluye:

- **LandingPage** — sitio público para vecinos, con mapa de contenedores y login.
- **Backoffice** — portal interno para empleados (administración, cuadrillas, operarios, mapa operativo).
- **i18n** — soporte multi-idioma (es/en/pt) vía `i18n/switcher.php`.

Cada API expone un único `api.php` que resuelve el endpoint contra una tabla
de configuración (`$config['resources']`) en vez de un switch por recurso:
agregar algo nuevo es agregar una entrada a esa tabla, no tocar el
enrutamiento. Los controladores no ejecutan SQL: validan y delegan en el
modelo. Cada controller expone además `reglasvalidacion()`, que devuelve en
JSON los límites de cada campo para que la Vista los pida antes de mostrar
un formulario, en vez de duplicarlos a mano en JavaScript.

Seguridad: sentencias preparadas en todas las consultas, contraseñas
hasheadas (`password_verify()`, nunca texto plano), y validación de rol y
estado de habilitación en cada request al Backoffice (`autenticacion.php`).
