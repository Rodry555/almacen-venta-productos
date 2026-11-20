#!/bin/sh
set -e

# Render asigna el puerto mediante la variable PORT. Apache usa 80 por defecto.
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
    echo "Listen ${PORT}" > /etc/apache2/ports.conf
    sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

exec "$@"
