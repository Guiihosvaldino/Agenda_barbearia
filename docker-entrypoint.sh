#!/bin/sh
set -eu

for module_file in /etc/apache2/mods-enabled/mpm_*.load; do
    [ -e "$module_file" ] || continue
    module="${module_file##*/}"
    module="${module%.load}"
    [ "$module" = "mpm_prefork" ] || a2dismod "$module"
done

a2enmod mpm_prefork
test "$(apache2ctl -M 2>/dev/null | grep -c 'mpm_')" -eq 1

port="${PORT:-8080}"

case "$port" in
    *[!0-9]*|'')
        echo "PORT must contain a valid port number." >&2
        exit 1
        ;;
esac

printf 'Listen %s\n' "$port" > /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
