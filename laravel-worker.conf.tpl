[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan bot:trading
autostart=true
autorestart=true
numprocs=1
startretries=10
stdout_events_enabled=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log

[program:cron]
command=/usr/sbin/cron -f
autostart=true
autorestart=true
stdout_logfile=/var/log/cron.log
stderr_logfile=/var/log/cron.log

[eventlistener:supervisord-watchdog]
command=/usr/bin/python3 /opt/supervisord-watchdog.py
events=PROCESS_STATE_FATAL
