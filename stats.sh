#!/bin/bash
#http://www.kinvey.com/blog/89/how-to-set-up-metric-collection-using-graphite-and-statsd-on-ubuntu-1204-lts
 
# node.js using PPA (for statsd)
sudo apt-get install python-software-properties
sudo apt-add-repository -y ppa:chris-lea/node.js
sudo apt-get update
sudo apt-get -y install nodejs
 
# Install git to get statsd
sudo apt-get -y install git
 
# System level dependencies for Graphite
sudo apt-get install -y memcached python-dev python-pip sqlite3 libcairo2 \
 libcairo2-dev python-cairo pkg-config
 
# Get latest pip
sudo pip install --upgrade pip 
 
# Install carbon and graphite deps 
cat >> /tmp/graphite_reqs.txt << EOF
django==1.4.5
python-memcached
django-tagging
twisted<12.0
whisper==0.9.12
carbon==0.9.10
graphite-web==0.9.12
EOF
 
sudo pip install -r /tmp/graphite_reqs.txt
 
#
# Configure carbon
#
cd /opt/graphite/conf/
sudo cp carbon.conf.example carbon.conf
 
# Create storage schema and copy it over
# Using the sample as provided in the statsd README
# https://github.com/etsy/statsd#graphite-schema
 
cat >> /tmp/storage-schemas.conf << EOF
# Schema definitions for Whisper files. Entries are scanned in order,
# and first match wins. This file is scanned for changes every 60 seconds.
#
#  [name]
#  pattern = regex
#  retentions = timePerPoint:timeToStore, timePerPoint:timeToStore, ...
[stats]
priority = 110
pattern = ^stats\..*
retentions = 10s:6h,1m:7d,10m:1y
EOF
 
sudo cp /tmp/storage-schemas.conf storage-schemas.conf
 
# Make sure log dir exists for webapp
sudo mkdir -p /opt/graphite/storage/log/webapp
 
# Copy over the local settings file and initialize database
cd /opt/graphite/webapp/graphite/
sudo cp local_settings.py.example local_settings.py
sudo python manage.py syncdb  # Follow the prompts, creating a superuser is optional
 
# statsd
cd /opt && sudo git clone git://github.com/etsy/statsd.git
 
# StatsD configuration
cat >> /tmp/localConfig.js << EOF
{
  graphitePort: 2003
, graphiteHost: "127.0.0.1"
, port: 8125
}
EOF
 
sudo cp /tmp/localConfig.js /opt/statsd/localConfig.js

#Run carbon-cache:
#cd /opt/graphite && sudo ./bin/carbon-cache.py –debug start

#Run graphite-web:
#cd /opt/graphite && sudo ./bin/run-graphite-devel-server.py . --port 8081

#Run statsd:
#cd /opt/statsd && node ./stats.js ./localConfig.js

#Run the example client (any one will suffice, python client shown here):
#cd /opt/statsd/examples && python ./python_example.py