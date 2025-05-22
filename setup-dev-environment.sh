#!/bin/bash

echo "Configurando entorno de desarrollo..."

echo "Instalando dependencias..."
docker-compose exec app composer install

echo "Configurando Git Hooks..."

if [ -f .git/hooks/pre-commit ]; then
    echo "El hook pre-commit ya existe, creando respaldo..."
    mv .git/hooks/pre-commit .git/hooks/pre-commit.bak
fi

cp .git/hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
echo "Hook pre-commit instalado correctamente."

echo "Configurando PHP-CS-Fixer..."
docker-compose exec app composer require --dev friendsofphp/php-cs-fixer

echo "Ejecutando análisis inicial de código..."
docker-compose exec app composer cs:check

echo "¡Configuración completada!"
echo "Puedes usar los siguientes comandos:"
echo "  - composer cs:check    : Verificar estilo de código"
echo "  - composer cs:fix      : Corregir estilo de código"
echo "  - composer lint        : Alias para verificar el estilo"
echo "  - composer format      : Alias para corregir el estilo" 