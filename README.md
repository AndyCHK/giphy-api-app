# API de Integración con GIPHY

Esta aplicación implementa una API RESTful que se integra con la API de GIPHY, permitiendo buscar y guardar GIFs favoritos. El proyecto sigue los principios SOLID y utiliza Clean Architecture.

## Requisitos Previos

- Docker y Docker Compose
- Git

## Documentación Técnica

En la carpeta `docs/diagrams` se encuentran los diagramas que documentan la arquitectura del sistema:

1. **Diagrama de Casos de Uso** (`caso_de_uso.puml`)
2. **Diagrama de Secuencia** (`secuencia_autenticacion.puml`, `secuencia_gifs.puml`)
3. **Diagrama Entidad-Relación (DER)** (`der.puml`)

En la carpeta `postman/` se encuentra la colección de Postman con los endpoints de la API.

## Despliegue con Docker

1. Clonar el repositorio y acceder al directorio:
```bash
git clone <url-del-repositorio>
cd <nombre-del-proyecto>
```

2. Configurar el entorno:
```bash
cp .env.example .env
```

3. Configurar las variables de entorno en el archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=172.28.1.20
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret

# Configuración para la API de Giphy
GIPHY_API_KEY=XXXXXXXXXXXXXXXXXXXXXXXXX
GIPHY_BASE_URL=https://api.giphy.com/v1
GIPHY_RETRY_ATTEMPTS=5
GIPHY_RETRY_DELAY=1000
```

> **IMPORTANTE**: La API de Giphy requiere una clave API válida. Se proporciona una clave de ejemplo, pero es recomendable [obtener una clave propia](https://developers.giphy.com/dashboard/) para uso en producción.

4. Construir y levantar los contenedores:
```bash
docker-compose up -d
```

5. Instalar dependencias y configurar la aplicación:
```bash
docker-compose exec giphy-api-app composer install
docker-compose exec giphy-api-app php artisan key:generate
docker-compose exec giphy-api-app php artisan migrate:fresh --seed
docker-compose exec giphy-api-app php artisan passport:install
```

## Servicios Disponibles

Después del despliegue, los siguientes servicios estarán disponibles:

- **API**: http://localhost:8000
- **MySQL**: 172.28.1.20:3306

Los contenedores que se crean son:
- `giphy-api-app`: Aplicación Laravel
- `giphy-api-db`: Base de datos MySQL
- `giphy-api-nginx`: Servidor web Nginx

## Endpoints de la API

La API implementa los 4 servicios requeridos en el challenge:

1. **Login:**
   - `POST /api/auth/login`: Autenticación para operar con la API

2. **Buscar GIFs:**
   - `GET /api/gifs/search`: Buscar GIFs por una frase o término

3. **Buscar GIF por ID:**
   - `GET /api/gifs/{id}`: Obtener información de un GIF específico

4. **Guardar GIF favorito:**
   - `POST /api/favorites`: Almacenar un GIF favorito para un usuario

Endpoints adicionales:

- `POST /api/auth/register`: Registro de usuarios
- `POST /api/auth/logout`: Cierre de sesión
- `GET /api/favorites`: Listar GIFs favoritos
- `DELETE /api/favorites/{id}`: Eliminar un GIF de favoritos

## Arquitectura

El proyecto está organizado siguiendo principios de Clean Architecture:

- **Domain**: Contiene la lógica de negocio, entidades, interfaces y reglas
- **Application**: Implementa los casos de uso
- **Infrastructure**: Maneja aspectos técnicos (controladores, repositorios, etc.)

## Comandos Útiles

### Docker
```bash
# Detener contenedores
docker-compose down

# Ver logs
docker-compose logs -f

# Reiniciar un servicio específico
docker-compose restart [servicio]
```

### Laravel
```bash
# Limpiar caché
docker-compose exec giphy-api-app php artisan cache:clear

# Ejecutar pruebas
docker-compose exec giphy-api-app php artisan test
```

## Solución de Problemas

1. **Error de permisos en storage:**
```bash
docker-compose exec giphy-api-app chmod -R 777 storage bootstrap/cache
```

2. **Error de conexión a la base de datos:**
- Verificar que el servicio de MySQL esté activo
- Confirmar las credenciales en el archivo .env

3. **Errores con la API de Giphy:**
- Asegurarse de que la clave API (`GIPHY_API_KEY`) sea válida
- Verificar la conectividad a `api.giphy.com`
- Revisar los logs para ver errores específicos: `docker-compose logs -f giphy-api-app`

## Seguridad

La API utiliza OAuth 2.0 mediante Laravel Passport. Todos los endpoints, excepto login y registro, requieren un token de autenticación.
