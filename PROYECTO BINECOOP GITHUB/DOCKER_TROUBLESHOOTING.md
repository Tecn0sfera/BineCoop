# Troubleshooting: Problemas de Conexión en Docker

## Problema: La configuración funciona en hosting pero no en Docker

Si `DB_HOST` está configurado como un hostname externo (ej: `sql211.infinityfree.com`) o una dirección IP y funciona en hosting pero no en Docker, aquí están las posibles causas y soluciones:

## Posibles Causas

### 1. Problema de DNS en Docker

Docker puede tener problemas resolviendo hostnames externos. Verifica:

```bash
# Desde dentro del contenedor
docker exec -it php-apache-web nslookup sql211.infinityfree.com

# O usando ping
docker exec -it php-apache-web ping -c 3 sql211.infinityfree.com
```

**Solución**: Ya tienes DNS configurado en `docker-compose.yml`:
```yaml
dns:
  - 8.8.8.8
  - 8.8.4.4
  - 1.1.1.1
```

Si sigue sin funcionar, reinicia el contenedor:
```bash
docker-compose restart web
```

### 2. Problema de Conectividad de Red

El contenedor puede no tener acceso a internet o estar bloqueado por firewall.

**Verificar conectividad**:
```bash
docker exec -it php-apache-web curl -I https://google.com
```

**Solución**: Asegúrate de que Docker tenga acceso a internet y que no haya restricciones de firewall.

### 3. Timeout de Conexión

Las conexiones externas desde Docker pueden tardar más.

**Solución**: El código ahora usa un timeout de 30 segundos para hosts externos (automático).

### 4. Puerto MySQL Bloqueado

Algunos proveedores de hosting bloquean conexiones MySQL desde fuera de su red.

**Solución**: Verifica que el hosting permita conexiones externas a MySQL. Puede requerir:
- Agregar tu IP a la lista blanca
- Usar un túnel SSH
- Configurar un proxy

## Verificación de Logs

Revisa los logs de PHP para ver qué está pasando:

```bash
# Ver logs del contenedor
docker logs php-apache-web --tail 100

# O desde dentro del contenedor
docker exec -it php-apache-web tail -f /var/log/apache2/error.log
```

Busca mensajes como:
- `"Cargando configuración desde config.json local"`
- `"Configuración completa cargada desde config.json local"`
- Mensajes de conexión a la base de datos

## Soluciones Alternativas

### Opción 1: Usar IP en lugar de hostname

Si el DNS no funciona, puedes usar la IP directamente en `DB_HOST`:

```json
{
    "htdocspanel": {
        "DB_HOST": "123.456.789.0",  // IP en lugar de hostname
        "DB_PORT": "3306",
        ...
    }
}
```

**Nota**: Sí, `DB_HOST` puede contener una dirección IP directamente. MySQL acepta tanto hostnames como direcciones IP.

**Obtener IP del hostname**:
```bash
nslookup sql211.infinityfree.com
# O
ping sql211.infinityfree.com
```

### Opción 2: Usar base de datos local en Docker

Si solo necesitas desarrollo local, usa la base de datos Docker:

```json
{
    "htdocspanel": {
        "DB_HOST": "db",   // Servicio Docker
        "DB_PORT": "3306",
        ...
    }
}
```

### Opción 3: Usar túnel SSH

Si el hosting solo permite conexiones desde ciertas IPs, puedes usar un túnel SSH:

```bash
ssh -L 3306:sql211.infinityfree.com:3306 usuario@servidor-intermedio
```

Luego en `config.json`:
```json
{
    "htdocspanel": {
        "DB_HOST": "127.0.0.1",  // Túnel local
        "DB_PORT": "3306",
        ...
    }
}
```

## Verificación Rápida

Ejecuta este script de prueba desde el contenedor:

```bash
docker exec -it php-apache-web php -r "
require '/var/www/html/htdocspanel/env_loader.php';
echo 'DB_HOST: ' . env('DB_HOST') . PHP_EOL;
echo 'Resolución DNS: ' . gethostbyname(env('DB_HOST')) . PHP_EOL;
"
```

## Configuración Recomendada para Docker

Para desarrollo local con Docker, usa:

```json
{
    "htdocspanel": {
        "DB_HOST": "db",
        "DB_PORT": "3306",
        "DB_NAME": "if0_39215471_admin_panel",
        "DB_USER": "if0_39215471",
        "DB_PASS": "RockGuidoNetaNa"
    }
}
```

Para producción o cuando necesites conectarte a hosting externo, usa:

```json
{
    "htdocspanel": {
        "DB_HOST": "sql211.infinityfree.com",  // O una dirección IP como "123.456.789.0"
        "DB_PORT": "3306",
        "DB_NAME": "if0_39215471_admin_panel",
        "DB_USER": "if0_39215471",
        "DB_PASS": "RockGuidoNetaNa"
    }
}
```

## Nota sobre DB_HOST

**¿Puede `DB_HOST` contener una dirección IP?** Sí, absolutamente. MySQL/MariaDB acepta tanto hostnames (como `sql211.infinityfree.com` o `db`) como direcciones IP directas (como `192.168.1.100` o `123.456.789.0`). Puedes usar cualquiera de las dos opciones en `DB_HOST` según tu necesidad.

## Contacto

Si el problema persiste después de intentar estas soluciones, revisa:
1. Los logs de Docker (`docker logs php-apache-web`)
2. Los logs de PHP (dentro del contenedor)
3. La configuración de red de Docker (`docker network inspect binecoop-network`)
