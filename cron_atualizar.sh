#!/bin/bash
# /var/www/arremate-br/cron_atualizar.sh
# Cron: 0 6 * * * /var/www/arremate-br/cron_atualizar.sh >> /var/log/arremate_cron.log 2>&1
LOG="/var/log/arremate_cron.log"
echo "=== $(date) ===" >> $LOG
php /var/www/arremate-br/scraper_caixa.php --csv-only >> $LOG 2>&1
chown www-data:www-data /var/www/dados/imoveis.db 2>/dev/null
chmod 664 /var/www/dados/imoveis.db 2>/dev/null
find /tmp/arremate-fotos -name "*.jpg" -mtime +7 -delete 2>/dev/null
echo "=== $(date) FIM ===" >> $LOG
