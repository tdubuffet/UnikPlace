Noname
==

## Introduction

Noname runs under PHP 5.6 and 7 with MySQL and ElasticSearch 1.7.\*.

This documentation will help you to install this project.

- [Environment](#environment)
- [Website installation](#website-installation)
- [Apache2 webserver configuration](#apache2-webserver-configuration)

## Environment

### Apache2, PHP and Memcached installation
First, install the following dependencies :

    apt-get install apache2 curl sudo git php5 php5-curl php5-gd php5-mysql mysql-server memcached imagemagick php5-imagick php5-memcached


### Elasticsearch installation

You can install Elasticsearch by following instructions on https://www.elastic.co/downloads/elasticsearch

If you want to setup a repository for Elasticsearch https://www.elastic.co/guide/en/elasticsearch/reference/current/setup-repositories.html

Here are the commands to setup Elasticsearch via repository

    wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
    echo "deb http://packages.elastic.co/elasticsearch/2.x/debian stable main" | sudo tee -a /etc/apt/sources.list.d/elasticsearch-2.x.list
    sudo apt-get update && sudo apt-get install elasticsearch
    sudo systemctl start elasticsearch


## Website installation

### Project cloning

On production environment use www-data user and on development environment use your user.

Make sure you have setup a ssh key for your user and enabled access to the repository. Then clone the repository :

    cd /var/www
    git@github.com:Kicherchekoi/noname.git

### Vendors installation and configuration

Install vendors with composer https://getcomposer.org/

Download composer https://getcomposer.org/download/

Then run the following command to install vendors

    php composer.phar install

Composer will autogenerate the configuration file (app/config/parameters.yml) for you. You just have to enter information interactively.

If you need to edit the configuration manually, just edit app/config/parameters.yml

### Symfony requirements

Check that you environment is correct by running the following command

    php bin/symfony_requirements

Please fix if there are any errors

### Database

Create database tables structures

    php bin/console doctrine:schema:update --force

Populate the database by loading fixtures

    php bin/console doctrine:fixtures:load


### Assets management

We use bower to handle asset librairies. You must have npm installed on your machine.

    sudo npm -g install bower
    bower install

We use gulp to generate some of our assets (like our javascripts files and sass files)

    npm install
    gulp installAssets
    gulp

## Apache2 webserver configuration

### Hostnames
Edit your /etc/hosts file to resolve the development domains.

    echo "127.0.0.1 noname.dev" | sudo tee -a /etc/hosts

Or in production

    echo "127.0.0.1 noname.com" | sudo tee -a /etc/hosts

### Enable mod_rewrite

    sudo a2enmod rewrite
    sudo systemctl restart apache2

### vhost configuration

Configure your apache vhosts for the development.

Create a new vhost file /etc/apache2/sites-available/noname (Depending of your distribution, the location can differ)

Make sure .htaccess files are loaded from vhosts. Set domains accordingly to your configuration.

Copy paste the following in the file :

    <VirtualHost *:80>
       DocumentRoot /var/www/noname/web
       ServerName noname.dev
       <Directory "/var/www/noname/web">
          AllowOverride All
       </Directory>
    </VirtualHost>

Then enable it by running

    sudo a2ensite noname

Reload the webserver

    sudo systemctl restart apache2
