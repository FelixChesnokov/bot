#!/bin/sh

exec supervisord --nodaemon --configuration /etc/supervisor/supervisord.conf
