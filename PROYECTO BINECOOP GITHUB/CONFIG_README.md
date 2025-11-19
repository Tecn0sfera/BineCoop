# Configuración de Variables de Entorno

Este proyecto utiliza archivos JSON para configurar las variables de entorno de forma automatizada.

## Uso

### 1. Crear el archivo de configuración

Copia el archivo de ejemplo y personalízalo con tus valores:

**Windows:**
```cmd
copy config.example.json config.json
```

**Linux/Mac:**
```bash
cp config.example.json config.json
```

### 2. Editar config.json

Abre `config.json` y completa los valores según tu entorno:

```json
{
  "htdocscop": {
    "DB_HOST": "tu_servidor_db",
    "DB_PORT": "3306",
    "DB_NAME": "tu_base_datos",
    "DB_USER": "tu_usuario",
    "DB_PASS": "tu_contraseña",
    "ADMIN_API_KEY": "tu_clave_api"
  },
  "htdocspanel": {
    "DB_HOST": "localhost",
    "DB_PORT": "3306",
    "DB_NAME": "nombre_base_datos",
    "DB_USER": "usuario",
    "DB_PASS": "contraseña",
    "DB_CHARSET": "utf8mb4",
    "ADMIN_API_KEY": "clave_api",
    "JWT_SECRET": "clave_jwt",
    "APP_ENV": "development",
    "DISPLAY_ERRORS": "false"
  }
}
```

### 3. Ejecutar el script

**Windows:**
```cmd
setup_env.bat
```

**Linux/Mac/Git Bash:**
```bash
bash setup_env.sh
# o
chmod +x setup_env.sh
./setup_env.sh
```

## Generar Secretos Seguros

Antes de configurar, es recomendable generar secretos seguros para `ADMIN_API_KEY` y `JWT_SECRET`:

### Generar ambos secretos (Recomendado)

**Windows:**
```cmd
generate_secrets.bat
```

**Linux/Mac:**
```bash
bash generate_secrets.sh
```

Este script generará:
- `JWT_SECRET` para htdocspanel
- `ADMIN_API_KEY` para ambas carpetas (htdocscop y htdocspanel)

### Generar secretos individuales

**ADMIN_API_KEY:**
- Windows: `generate_admin_api_key.bat`
- Linux/Mac: `bash generate_admin_api_key.sh`
- PHP: `php htdocspanel/generate_admin_api_key.php`

**JWT_SECRET:**
- Windows: `generate_jwt_secret.bat`
- Linux/Mac: `bash generate_jwt_secret.sh`
- PHP: `php htdocspanel/generate_jwt_secret.php`

## Características

- ✅ Lee automáticamente los valores desde `config.json`
- ✅ Si no existe `config.json`, usa valores por defecto
- ✅ Permite sobrescribir archivos `.env` existentes
- ✅ Configuración separada para `htdocscop` y `htdocspanel`
- ✅ Generadores de secretos seguros incluidos

## Requisitos

### Windows
- PowerShell (incluido por defecto en Windows 10+)

### Linux/Mac
- `jq` (recomendado) o `python3` para leer JSON
- Si no tienes ninguna de estas herramientas, el script usará valores por defecto

**Instalar jq:**
- Ubuntu/Debian: `sudo apt-get install jq`
- macOS: `brew install jq`
- CentOS/RHEL: `sudo yum install jq`

## Seguridad

⚠️ **IMPORTANTE**: 
- El archivo `config.json` contiene información sensible
- **NO** subas `config.json` al repositorio Git
- Asegúrate de que `config.json` esté en `.gitignore`
- Los archivos `.env` generados también deben estar en `.gitignore`

## Estructura del JSON

El archivo `config.json` tiene dos secciones principales:

- **htdocscop**: Configuración para la aplicación principal
- **htdocspanel**: Configuración para el panel de administración

Cada sección contiene las variables de entorno necesarias para su respectiva aplicación.

## Flujo de Trabajo Recomendado

1. **Copiar archivo de ejemplo:**
   ```cmd
   copy config.example.json config.json
   ```

2. **Generar secretos seguros:**
   ```cmd
   generate_secrets.bat
   ```
   Esto actualizará automáticamente `JWT_SECRET` y `ADMIN_API_KEY` en `config.json`

3. **Completar configuración de base de datos:**
   Edita `config.json` y completa los valores de conexión a la base de datos

4. **Aplicar configuración:**
   ```cmd
   setup_env.bat
   ```
   Esto creará los archivos `.env` en ambas carpetas

## Notas sobre Secretos

- **ADMIN_API_KEY**: Se usa en ambas carpetas (htdocscop y htdocspanel) para autenticación entre servicios
- **JWT_SECRET**: Solo se usa en htdocspanel para generar tokens JWT de usuarios
- Ambos secretos deben ser únicos y seguros (64 caracteres hexadecimales recomendado)
- Los secretos generados son criptográficamente seguros usando `random_bytes()` / `openssl rand`

