#!/bin/bash

function get_logs {
        cp /var/log/mysql/mysql.log /var/www/log/mysql.log
}


if [ 'clear' = "$1" ]; then
        echo "clearing"
        sudo bash -c "cat /dev/null > /var/log/mysql/mysql.log"
        get_logs
fi
if [ 'get' = "$1" ]; then
        echo 'retrieving'
        get_logs
fi

if [ 'off' = "$1" ]; then
        echo "SET GLOBAL general_log = 'OFF'" | mysql -uroot -psecret
        echo "logging disabled"
fi

if [ 'on' = "$1" ]; then
        echo "SET GLOBAL general_log = 'ON'" | mysql -uroot -psecret
        echo "logging enabled"
fi

if [ 'status' = "$1" ]; then
        echo "SHOW VARIABLES" | mysql -uroot -psecret | grep general_log
fi        
