# Sistema JWT para htdocspanel

Este proyecto incluye una implementación propia de JWT (JSON Web Tokens) sin dependencias externas.

## Archivos

- **`jwt_handler.php`**: Manejador principal de JWT con clase `JWTHandler` y funciones de conveniencia
- **`generate_jwt_secret.php`**: Script PHP para generar un JWT_SECRET seguro
- **`auth/user_auth.php`**: API de autenticación que usa el manejador JWT
- **`auth/login.php`**: Endpoint de login que genera tokens JWT

## Configuración

### 1. Generar JWT_SECRET

**Opción A: Usar script de generación (Recomendado)**

**Windows:**
```cmd
generate_jwt_secret.bat
```

**Linux/Mac:**
```bash
bash generate_jwt_secret.sh
```

**Opción B: Usar script PHP**
```bash
php htdocspanel/generate_jwt_secret.php
```

**Opción C: Manualmente en config.json**
```json
{
  "htdocspanel": {
    "JWT_SECRET": "tu_secreto_seguro_aqui"
  }
}
```

### 2. Configurar en .env

Después de ejecutar `setup_env.bat` o `setup_env.sh`, el JWT_SECRET se configurará automáticamente desde `config.json`.

O manualmente en `htdocspanel/.env`:
```
JWT_SECRET=tu_secreto_seguro_aqui
```

## Uso

### Generar Token

```php
require_once __DIR__ . '/jwt_handler.php';

// Opción 1: Usar función de conveniencia
$user = [
    'id' => 1,
    'nombre' => 'Juan Pérez',
    'email' => 'juan@example.com'
];
$token = generateUserJWT($user); // Expira en 7 días por defecto
$token = generateUserJWT($user, 3600); // Expira en 1 hora

// Opción 2: Usar la clase directamente
$jwt = new JWTHandler();
$payload = [
    'sub' => $user['id'],
    'type' => 'user',
    'nombre' => $user['nombre'],
    'email' => $user['email']
];
$token = $jwt->generateToken($payload, 60 * 60 * 24 * 7); // 7 días
```

### Validar Token

```php
require_once __DIR__ . '/jwt_handler.php';

// Opción 1: Usar función de conveniencia
$token = $_GET['token']; // o desde header Authorization
$userData = validateUserToken($token);

if ($userData) {
    echo "Usuario ID: " . $userData['sub'];
    echo "Nombre: " . $userData['nombre'];
} else {
    echo "Token inválido o expirado";
}

// Opción 2: Usar la clase directamente
$jwt = new JWTHandler();
$payload = $jwt->validateToken($token);
```

### Requerir Autenticación

```php
require_once __DIR__ . '/jwt_handler.php';

// Esta función automáticamente:
// 1. Lee el token del header Authorization: Bearer <token>
// 2. Valida el token
// 3. Retorna los datos del usuario o termina con error 401
$userData = requireUserAuth();

// Si llegamos aquí, el usuario está autenticado
echo "Usuario autenticado: " . $userData['nombre'];
```

### Obtener Token del Header

```php
require_once __DIR__ . '/jwt_handler.php';

$token = JWTHandler::getTokenFromHeader();
if ($token) {
    // Procesar token
}
```

## Estructura del Token

Los tokens JWT generados tienen la siguiente estructura:

**Header:**
```json
{
  "typ": "JWT",
  "alg": "HS256"
}
```

**Payload (ejemplo):**
```json
{
  "sub": 1,
  "type": "user",
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "iat": 1234567890,
  "exp": 1235173890,
  "nbf": 1234567890
}
```

**Campos estándar:**
- `sub`: Subject (ID del usuario)
- `type`: Tipo de token (user, admin, etc.)
- `iat`: Issued at (tiempo de emisión)
- `exp`: Expiration (tiempo de expiración)
- `nbf`: Not before (no válido antes de)

## Seguridad

### Mejores Prácticas

1. **JWT_SECRET seguro**: Usa al menos 64 caracteres aleatorios
2. **HTTPS**: Siempre usa HTTPS en producción para proteger los tokens
3. **Expiración corta**: Configura tiempos de expiración razonables
4. **Validación**: Siempre valida el token antes de confiar en sus datos
5. **No almacenar datos sensibles**: El payload es decodificable, no incluyas contraseñas

### Rotación de Secretos

Si necesitas rotar el JWT_SECRET:

1. Genera un nuevo secreto
2. Actualiza `config.json` y `.env`
3. Los tokens antiguos dejarán de ser válidos
4. Los usuarios necesitarán iniciar sesión nuevamente

## Compatibilidad

El sistema es compatible con:
- ✅ Código existente que usa `generateUserJWT()`
- ✅ Código existente que usa `validateUserToken()`
- ✅ Código existente que usa `requireUserAuth()`
- ✅ Código existente que usa `generateJWT()`

## Ejemplo Completo

```php
<?php
require_once __DIR__ . '/jwt_handler.php';

// 1. Usuario inicia sesión
$user = [
    'id' => 123,
    'nombre' => 'Juan Pérez',
    'email' => 'juan@example.com'
];

$token = generateUserJWT($user);
echo "Token: " . $token;

// 2. En otra petición, validar el token
$token = JWTHandler::getTokenFromHeader();
$userData = validateUserToken($token);

if ($userData) {
    echo "Usuario autenticado: " . $userData['nombre'];
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
}
?>
```

## Troubleshooting

### Error: "JWT_SECRET no está configurado"
- Verifica que `JWT_SECRET` esté en tu archivo `.env`
- Ejecuta `setup_env.bat` o `setup_env.sh` para configurar desde `config.json`

### Error: "Token inválido o expirado"
- Verifica que el token no haya expirado
- Verifica que el JWT_SECRET sea el mismo usado para generar el token
- Verifica que el token esté completo (3 partes separadas por puntos)

### Error: "Firma inválida"
- El JWT_SECRET usado para validar debe ser el mismo usado para generar
- Verifica que no haya espacios o caracteres especiales en el secreto

