# Despliegue de Recolix con Docker — Guía paso a paso

Esta guía documenta cómo se desplegó Recolix en una VM Rocky Linux 9 haciendo
los 3 roles del manual de la cátedra (`srv-app`, `srv-db`, `srv-backup`) en
una sola máquina, siguiendo la estructura del manual `ESI - Manual de Docker
instalación, configuración y migración de código`.

## Arquitectura general

```
VM Rocky Linux 9 (hostname srv-db, IP 192.168.10.20 en enp0s8)
│
├── Rol srv-db: MySQL 8.0 nativo (fuera de Docker)
│   └── Base "recolix", usuario "recolix_app"
│
├── Rol srv-app: Docker + Docker Compose (red "recolix_red")
│   ├── gateway        → Nginx, único que publica 80/443, hace reverse proxy
│   ├── landing         → Landing page pública (vecinos)
│   ├── backoffice      → Portal interno (empleados)
│   ├── api-usuarios    → API REST de usuarios/roles
│   ├── api-gestion     → API REST de contenedores/incidencias
│   └── api-recoleccion → API REST de camiones/circuitos
│
└── Rol srv-backup: usuario "respaldo" + llave SSH dedicada
    └── Recibe por rsync los respaldos full/incrementales
```

El navegador solo habla con el `gateway` por HTTPS; el gateway reparte cada
ruta al contenedor correspondiente; las APIs llegan a MySQL por la red
interna de Docker.

---

## Paso 1 — Instalar Docker

Rocky trae Podman preinstalado, que choca con Docker:

```bash
sudo dnf remove -y podman buildah runc docker docker-client docker-client-latest \
  docker-common docker-latest docker-latest-logrotate docker-logrotate docker-engine
sudo dnf install -y dnf-plugins-core
sudo dnf config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
sudo dnf install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable --now docker
```

Usar Docker sin `sudo`:

```bash
sudo groupadd docker          # puede que ya exista
sudo usermod -aG docker $USER
newgrp docker                 # o cerrar sesión y volver a entrar
```

Configurar rotación de logs (`/etc/docker/daemon.json`):

```json
{
  "log-driver": "json-file",
  "log-opts": { "max-size": "10m", "max-file": "3" },
  "features": { "buildkit": true }
}
```

```bash
sudo systemctl restart docker
sudo firewall-cmd --permanent --add-service=http --add-service=https
sudo firewall-cmd --reload
```

⚠️ **Importante:** después de este `firewall-cmd --reload`, el DNS interno
de Docker (usado por nginx para resolver nombres de contenedores como
`landing`, `api-usuarios`, etc.) puede romperse. Si el `gateway` entra en
bucle de reinicio con error `host not found in upstream`, solucionarlo con:

```bash
sudo systemctl restart docker
cd ~/Recolix && docker compose up -d
```

---

## Paso 2 — Instalar y configurar MySQL (rol `srv-db`)

Rocky 9 trae MariaDB preinstalado, que choca con `mysql-server`:

```bash
sudo dnf install -y mysql-server --allowerasing
```

### ⚠️ Punto crítico: `lower_case_table_names`

El código PHP de Recolix (controladores y modelos de las 3 APIs) fue escrito
asumiendo nombres de tabla en **MAYÚSCULAS** (`CONTENEDOR`, `USUARIO`,
`INCIDENCIA_CONTENEDOR`, etc.), pero `recolix.sql` crea las tablas en
**minúsculas**. En Windows/Mac esto nunca se nota porque el sistema de
archivos (y por lo tanto MySQL) no distingue mayúsculas; en Linux, MySQL por
defecto **sí** las distingue, y todo el código rompe con errores
`Table 'recolix.CONTENEDOR' doesn't exist`.

La solución limpia es hacer que MySQL se comporte como en Windows, pero esta
variable **solo se puede fijar al inicializar el directorio de datos**, no
después (intentarlo sobre una base ya creada tira el error
`MY-011087: Different lower_case_table_names settings`). Por eso hay que
configurarla **antes del primer arranque**:

```bash
sudo tee /etc/my.cnf.d/mysql-server.cnf > /dev/null << 'EOF'
[mysqld]
lower_case_table_names=1
datadir=/var/lib/mysql
EOF

sudo systemctl enable --now mysqld
```

Si por error ya se inicializó MySQL sin esta variable, hay que reinicializar
todo el directorio de datos:

```bash
sudo systemctl stop mysqld
sudo mv /var/lib/mysql /var/lib/mysql.bak   # por las dudas, no borrar directo
sudo mkdir -p /var/lib/mysql
sudo chown mysql:mysql /var/lib/mysql
# (agregar lower_case_table_names=1 al cnf si no estaba)
sudo systemctl start mysqld
sudo restorecon -Rv /var/lib/mysql   # si falla con "Permission denied" en auto.cnf (contexto SELinux)
```

### Crear la base y el usuario de aplicación

```bash
sudo mysql -e "
CREATE DATABASE recolix CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'recolix_app'@'localhost' IDENTIFIED BY 'TU_PASSWORD';
CREATE USER 'recolix_app'@'172.17.%' IDENTIFIED BY 'TU_PASSWORD';
CREATE USER 'recolix_app'@'172.18.%' IDENTIFIED BY 'TU_PASSWORD';
CREATE USER 'recolix_app'@'192.168.10.20' IDENTIFIED BY 'TU_PASSWORD';
GRANT ALL PRIVILEGES ON recolix.* TO 'recolix_app'@'localhost';
GRANT ALL PRIVILEGES ON recolix.* TO 'recolix_app'@'172.17.%';
GRANT ALL PRIVILEGES ON recolix.* TO 'recolix_app'@'172.18.%';
GRANT ALL PRIVILEGES ON recolix.* TO 'recolix_app'@'192.168.10.20';
FLUSH PRIVILEGES;
"
```

Los distintos hosts (`localhost`, `172.17.%`, `172.18.%`, la IP de la VM)
cubren las distintas formas en que los contenedores pueden "aparecer" ante
MySQL según cómo se conecten. Si se reconstruye la red de Docker y cambia el
rango de IPs, verificar con:

```bash
docker inspect <contenedor> --format '{{json .NetworkSettings.Networks}}'
```

Nota: la política de contraseñas de MySQL 8 exige mayúsculas, minúsculas,
número y símbolo — generar una con `openssl rand -base64 18`, más un símbolo
si no trae ninguno.

---

## Paso 3 — SSH por clave (necesario antes de clonar y para el respaldo)

Generar un par de llaves si no existe:

```bash
ssh-keygen -t ed25519
```

Copiar la clave pública (`cat ~/.ssh/id_ed25519.pub`) y agregarla:
- Si sos admin del repo: como **deploy key** del repositorio en GitHub.
- Si no sos admin (como en este caso): en tu cuenta personal de GitHub
  (Settings → SSH and GPG keys → New SSH key). Si tenés acceso de lectura
  al repo como colaborador, alcanza igual.

⚠️ Si el servidor sigue pidiendo contraseña en vez de usar la llave, revisar
`/etc/ssh/sshd_config` por `PubkeyAuthentication no` o `AllowUsers` sin tu
usuario — se corrige agregando un archivo nuevo en
`/etc/ssh/sshd_config.d/` (no editar el archivo principal), por ejemplo:

```bash
sudo tee /etc/ssh/sshd_config.d/50-recolix.conf << 'EOF'
PubkeyAuthentication yes
AllowUsers tu_usuario respaldo
EOF
sudo sshd -t && sudo systemctl restart sshd
```

---

## Paso 4 — Clonar el repositorio

```bash
git clone -b docker git@github.com:Celeris-SAS/Recolix.git ~/Recolix
cd ~/Recolix
```

Estructura relevante que trae el repo:

```
Recolix/
├── LandingPage/ Backoffice/ API_Usuarios/ API_Gestion/ API_Recoleccion/
│   └── cada una con su Dockerfile, docker/apache.conf
├── extra/          (código compartido: i18n, errores, notificaciones)
├── gateway/nginx.conf
├── compose.yaml       (producción)
├── compose.dev.yaml   (desarrollo, agrega un MySQL en contenedor)
├── .env.example
├── conexion.php
└── recolix.sql
```

---

## Paso 5 — Configurar `.env` y el certificado SSL

```bash
cat > ~/Recolix/.env << 'EOF'
TAG=1.0
RECOLIX_DB_HOST=192.168.10.20
RECOLIX_DB_PORT=3306
RECOLIX_DB_NAME=recolix
RECOLIX_DB_USER=recolix_app
RECOLIX_DB_PASS=TU_PASSWORD
RECOLIX_MAIL_ENABLED=0
EOF
chmod 600 ~/Recolix/.env
```

```bash
mkdir -p ~/Recolix/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout ~/Recolix/certs/recolix.key -out ~/Recolix/certs/recolix.crt \
  -subj "/C=UY/O=Recolix/CN=recolix.local" \
  -addext "subjectAltName=DNS:recolix.local,IP:192.168.10.20"
chmod 600 ~/Recolix/certs/recolix.key
```

(Certificado autofirmado: el navegador va a advertir sobre esto, es
esperable en la red de la escuela — aceptar el riesgo y continuar.)

---

## Paso 6 — Importar la base de datos

```bash
sudo mysql recolix < ~/Recolix/recolix.sql
sudo mysql recolix -e "SHOW TABLES;"
```

Si tira un error de sintaxis en una línea con `ADD INDEX IF NOT EXISTS`,
quitar el `IF NOT EXISTS` de esa línea en `recolix.sql` (ya corregido en
este repo) y reintentar.

---

## Paso 7 — Levantar los contenedores

```bash
cd ~/Recolix
docker compose up -d --build
docker compose ps
```

Los 6 contenedores deben quedar en estado `Up`. Verificación:

```bash
curl -k https://localhost/                                        # 302 a la Landing Page
curl -k https://localhost/recolix/LandingPage/index_LandingPage.html  # 200
```

⚠️ Cada vez que se reconstruye un servicio individual (`docker compose up -d
--build <servicio>`) después de que el `gateway` ya estaba corriendo, hay
que reiniciarlo para que vuelva a resolver las IPs internas de los
contenedores recreados:

```bash
docker compose restart gateway
```

---

## Paso 8 — Respaldo (rol `srv-backup`)

Usuario dedicado y carpetas destino:

```bash
sudo useradd -m respaldo
sudo mkdir -p /respaldos/recolix/db /respaldos/recolix/app/full_semanal /respaldos/recolix/app/incremental_diario
sudo chown -R respaldo: /respaldos/recolix
```

Llave SSH dedicada (solo la lee `root`, porque los scripts corren por cron
de `root`):

```bash
sudo ssh-keygen -t ed25519 -f /root/.ssh/id_respaldo -N ""
sudo install -d -m 700 -o respaldo -g respaldo /home/respaldo/.ssh
sudo cp /root/.ssh/id_respaldo.pub /home/respaldo/.ssh/authorized_keys
sudo chown respaldo:respaldo /home/respaldo/.ssh/authorized_keys
sudo chmod 600 /home/respaldo/.ssh/authorized_keys
```

Los scripts `backup_full.sh` (semanal, completo) y `backup_incremental.sh`
(diario, con `rsync --link-dest`) respaldan `~/Recolix/` a una carpeta local
y al final mandan ese respaldo por SSH con la llave dedicada:

```bash
rsync -az -e "ssh -i /root/.ssh/id_respaldo" "$DESTINO/" \
  respaldo@192.168.10.20:/respaldos/recolix/app/full_semanal/$FECHA/
```

Programados en el cron de `root`:

```bash
sudo crontab -e
```
```
0 2 * * 0 /home/TU_USUARIO/bin/backup_full.sh >> /home/TU_USUARIO/Recolix_respaldos/cron.log 2>&1
0 1 * * 1-6 /home/TU_USUARIO/bin/backup_incremental.sh >> /home/TU_USUARIO/Recolix_respaldos/cron.log 2>&1
```

---

## Paso 9 — Bugs de la aplicación encontrados al verificar (no de infraestructura)

Estos no son parte del despliegue en sí, pero bloqueaban que el sistema
funcionara una vez levantado — documentados porque reaparecerían si se
repite el despliegue con una copia vieja del código:

1. **`API_Recoleccion/Controlador/camionController.php`** pedía
   `Modelo/CamionModel.php` (C mayúscula) pero el archivo real es
   `camionModel.php` (minúscula) — falla en Linux, no en Windows/Mac.
2. **`require_once '../../Backoffice/verificacion_sesion.php'`** en las
   Vistas de las 3 APIs asume un filesystem compartido entre todas las
   carpetas del repo (como XAMPP), pero cada contenedor Docker está
   aislado. Se solucionó agregando `COPY Backoffice/verificacion_sesion.php
   /var/www/Backoffice/verificacion_sesion.php` en los Dockerfiles de
   `API_Gestion`, `API_Recoleccion` y `API_Usuarios`.
3. **Sesiones de PHP no compartidas entre contenedores**: por defecto cada
   contenedor guarda las sesiones en su propio disco. Se agregó un volumen
   de Docker (`php_sessions`) montado en `/var/www/sessions` en los 5
   contenedores de apps PHP (`landing`, `backoffice`, `api-usuarios`,
   `api-gestion`, `api-recoleccion`), con `session.save_path` apuntando ahí
   en cada Dockerfile — así un login en el Backoffice es reconocido por las
   APIs.

---

## Verificación final (checklist de demo)

```bash
docker compose ps                              # los 6 contenedores "Up"
curl -k https://192.168.10.20/                  # Landing Page
curl -k https://192.168.10.20/recolix/API_Gestion/contenedores   # datos reales en JSON
sudo mysql recolix -e "SHOW TABLES;"            # 28 tablas
sudo crontab -l                                 # respaldo programado
ls ~/Recolix_respaldos/full_semanal/            # respaldos locales generados
```

Y la prueba visual: iniciar sesión en `https://192.168.10.20/recolix/Backoffice/`
desde un navegador y navegar Camiones, Circuitos, Contenedores, Notificaciones
desde el menú — todo debe cargar sin volver a pedir login.
