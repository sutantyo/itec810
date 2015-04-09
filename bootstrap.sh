#!/usr/bin/env bash

DBPASSWD=secret

sudo apt-get update


sudo debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password password secret'
sudo debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password_again password secret'


sudo apt-get -y install mysql-server-5.5 php5 php5-mysql apache2 
sudo apt-get -y install php5-gd php-apc php5-curl php5-mcrypt php5-xdebug php5-ldap
sudo apt-get -y install vim
sudo rm -rf /var/www
sudo ln -fs /vagrant /var/www
sudo /etc/init.d/apache2 start


#Create a database
mysql -u root -p"secret" -e "DROP DATABASE IF EXISTS quiz_db; CREATE DATABASE quiz_db;"
#Create db user
echo "GRANT ALL ON quiz_db.* TO quizuser@localhost IDENTIFIED BY '123456'" | mysql -uroot -p"secret"
#Load initial data
mysql -uroot -p"secret" quiz_db < /var/www/db/schema.sql


# install phpmyadmin
#mkdir /vagrant/phpmyadmin/ 2> /dev/null
#wget -O /vagrant/phpmyadmin/index.html http://www.phpmyadmin.net/
#awk 'BEGIN{ RS="<a *href *= *\""} NR>2 {sub(/".*/,"");print; }' /vvagrant/phpmyadmin/index.html >> /vagrant/phpmyadmin/url-list.txt
#grep "http://sourceforge.net/projects/phpmyadmin/files/phpMyAdmin/" /vagrant/phpmyadmin/url-list.txt > /vagrant/phpmyadmin/phpmyadmin.url
#sed -i 's/.zip/.tar.bz2/' /vagrant/phpmyadmin/phpmyadmin.url
#wget -O /vagrant/phpmyadmin/phpMyAdmin.tar.bz2 `cat /vagrant/phpmyadmin/phpmyadmin.url`
#mkdir /vagrant/myadm.localhost
#tar jxvf /vagrant/phpmyadmin/phpMyAdmin.tar.bz2 -C /vagrant/myadm.localhost --strip 1
#rm -rf /vagrant/phpmyadmin 2> /dev/null
sudo apt-get install debconf-utils
echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/app-password-confirm password $DBPASSWD" | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/admin-pass password $DBPASSWD" | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/app-pass password $DBPASSWD" | sudo debconf-set-selections
echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect none" | sudo debconf-set-selections
sudo apt-get -y install phpmyadmin
sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf.d/phpmyadmin.conf



#sudo sudo chown -R vagrant:www-data /var/www/sites/default/files
#chmod -R 775 /var/www/sites/default/files

#Install PHPUnit
wget https://phar.phpunit.de/phpunit.phar
chmod +x phpunit.phar
sudo mv phpunit.phar /usr/local/bin/phpunit

#rewrite
sudo a2enmod rewrite
#allow .htaccess
sudo sed -i '/AllowOverride None/c AllowOverride All' /etc/apache2/sites-available/default
sudo service apache2 restart

#mysql logging
sudo sed -i '/general_log             = 1/c general_log             = 1' /etc/mysql/my.cnf
sudo sed -i '/general_log_file        = \/var\/log\/mysql\/mysql.log/c general_log_file        = \/var\/log\/mysql\/mysql.log' /etc/mysql/my.cnf
sudo service mysql restart
ln -s /var/log/mysql/mysql.log /var/www/log/mysql.log



#install java
sudo apt-get -y install python-software-properties
sudo add-apt-repository ppa:webupd8team/java -y
sudo apt-get update -y
echo debconf shared/accepted-oracle-license-v1-1 select true | sudo debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | sudo debconf-set-selections
sudo apt-get install oracle-java7-installer -y