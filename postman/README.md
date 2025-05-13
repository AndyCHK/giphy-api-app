# Colección de Postman para Giphy API Challenge

Esta carpeta contiene los archivos necesarios para importar y utilizar la colección de Postman para interactuar con la API del proyecto Giphy API Challenge.

## Archivos incluidos

- `GiphyAPI.postman_collection.json`: Colección con todos los endpoints de la API
- `GiphyAPI.postman_environment.json`: Variables de entorno para la colección

## Cómo importar

1. Abre Postman
2. Haz clic en "Import" en la esquina superior izquierda
3. Selecciona los archivos `GiphyAPI.postman_collection.json` y `GiphyAPI.postman_environment.json`
4. Haz clic en "Import"

## Configuración del entorno

1. En la esquina superior derecha, selecciona el entorno "Giphy API Local" de la lista desplegable
2. Asegúrate de que la variable `base_url` coincida con la URL donde está corriendo tu aplicación (por defecto: http://localhost)

## Uso de la colección

La colección incluye tres carpetas principales:

1. **Autenticación**: Endpoints para registro, login y logout
2. **Giphy**: Endpoints para buscar GIFs y obtener información de un GIF específico
3. **Favoritos**: Endpoints para gestionar GIFs favoritos

### Automatización de token

La colección está configurada para almacenar automáticamente el token de acceso cuando se realiza un login exitoso:

1. Ejecuta el endpoint "Login" con credenciales válidas
2. El token se guardará automáticamente en la variable de entorno `access_token`
3. Todos los demás endpoints que requieren autenticación utilizarán este token automáticamente

## Endpoints disponibles

### Autenticación

- **Registro de Usuario**: `POST /api/auth/register`
- **Login**: `POST /api/auth/login`
- **Logout**: `POST /api/auth/logout`

### Giphy

- **Buscar GIFs**: `GET /api/gifs/search?query={término}`
- **Obtener GIF por ID**: `GET /api/gifs/{id}`

### Favoritos

- **Listar Favoritos**: `GET /api/favorites`
- **Guardar Favorito**: `POST /api/favorites`
- **Eliminar Favorito**: `DELETE /api/favorites/{id}` 