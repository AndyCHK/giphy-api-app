# API de Integración con GIPHY

Esta aplicación proporciona una API RESTful que integra con GIPHY, permitiendo a los usuarios buscar y guardar sus GIFs favoritos.

## Requisitos Previos

- Docker
- Docker Compose
- Git

## Estructura del Proyecto

El proyecto sigue los principios de Clean Architecture y está estructurado de la siguiente manera:

```
.
├── app/
│   ├── Domain/          # Entidades y reglas de negocio
│   ├── Application/     # Casos de uso
│   └── Infrastructure/  # Implementaciones concretas
├── docker/             # Configuración de Docker
├── database/          # Migraciones y seeders
└── routes/            # Definición de rutas API
```

## Configuración del Entorno

1. Clonar el repositorio:
```bash
git clone <url-del-repositorio>
cd <nombre-del-proyecto>
```

2. Copiar el archivo de configuración:
```bash
cp .env.example .env
```

3. Configurar las variables de entorno en el archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Despliegue con Docker

1. Construir y levantar los contenedores:
```bash
docker-compose up -d
```

2. Instalar dependencias de PHP:
```bash
docker-compose exec app composer install
```

3. Generar la clave de la aplicación:
```bash
docker-compose exec app php artisan key:generate
```

4. Ejecutar las migraciones:
```bash
docker-compose exec app php artisan migrate
```

5. Configurar OAuth2 (Laravel Passport):
```bash
docker-compose exec app php artisan passport:install
```

## Servicios Disponibles

Después del despliegue, los siguientes servicios estarán disponibles:

- **API**: http://localhost:8000
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

## Endpoints de la API

- `POST /api/auth/register`: Registro de usuarios
- `POST /api/auth/login`: Inicio de sesión
- `GET /api/gifs/search`: Búsqueda de GIFs
- `POST /api/gifs/favorite`: Guardar GIF como favorito
- `GET /api/gifs/favorites`: Listar GIFs favoritos

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
docker-compose exec app php artisan cache:clear

# Ejecutar pruebas
docker-compose exec app php artisan test
```

## Solución de Problemas

1. **Error de permisos en storage:**
```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

2. **Error de conexión a la base de datos:**
- Verificar que el servicio de MySQL esté activo
- Confirmar las credenciales en el archivo .env
- Asegurarse de que el host sea "db" en el .env

3. **Error 500:**
- Verificar los logs de Laravel:
```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

## Mantenimiento

Para mantener el sistema actualizado:

1. Actualizar dependencias:
```bash
docker-compose exec app composer update
```

2. Actualizar migraciones:
```bash
docker-compose exec app php artisan migrate
```

## Seguridad

- Las claves de API y credenciales sensibles deben almacenarse en el archivo .env
- Asegúrese de que el archivo .env esté incluido en .gitignore
- Mantenga Laravel y todas las dependencias actualizadas
- Use HTTPS en producción

## Contribución

1. Fork el repositorio
2. Cree una rama para su feature (`git checkout -b feature/AmazingFeature`)
3. Commit sus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abra un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - vea el archivo LICENSE.md para más detalles.
