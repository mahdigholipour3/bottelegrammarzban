#!/bin/bash


if [ "$(id -u)" -ne 0 ]; then
    echo -e "\033[33mPlease run as root\033[0m"
    exit
fi

wait

#sleep
echo -e "\e[32mInstalling mirzapanelbot script ... \033[0m\n"
sleep 5

# Update & upgrade
sudo apt update && apt upgrade -y
echo -e "\e[92mThe server was successfully updated ...\033[0m\n"

# Install Git
sudo apt install git -y
echo -e "\n\033[33mThe git was installed successfully\033[0m\n"


PKG=(
    lamp-server^
    libapache2-mod-php 
    mysql-server 
    apache2 
    php-mbstring 
    php-zip 
    php-gd 
    php-json 
    php-curl 
#     phpmyadmin
)

for i in "${PKG[@]}"
do
    dpkg -s $i &> /dev/null
    if [ $? -eq 0 ]; then
        echo "$i is already installed"
    else
        apt install $i -y
        if [ $? -ne 0 ]; then
            echo "Error installing $i"
            exit 1
        fi
    fi
done

echo -e "\n\e[92mPackages Installed Continuing ...\033[0m\n"

echo 'phpmyadmin phpmyadmin/dbconfig-install boolean true' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/app-password-confirm password wizwizhipass' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/admin-pass password wizwizhipass' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/app-pass password wizwizhipass' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
sudo apt-get install phpmyadmin -y
sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf
sudo a2enconf phpmyadmin.conf
sudo systemctl restart apache2

wait

sudo apt-get install -y php-soap

sudo apt-get install libapache2-mod-php

# extension=soap.so
# echo "extension=soap.so" >> /usr/local/lib/php.ini
# sed -i 's/;extension=soap/extension=soap/g' /usr/local/lib/php.ini


# services
sudo systemctl enable mysql.service
sudo systemctl start mysql.service
sudo systemctl enable apache2
sudo systemctl start apache2

echo -e "\n\e[92m Setting Up UFW...\033[0m\n"

ufw allow 'Apache'

sudo systemctl restart apache2

echo -e "\n\e[92mInstalling ...\033[0m\n"
sleep 1
git clone https://github.com/mahdigholipour3/bottelegrammarzban.git /var/www/html/bottelegrammarzban
sudo chown -R www-data:www-data /var/www/html/bottelegrammarzban/
sudo chmod -R 755 /var/www/html/bottelegrammarzban/
echo -e "\n\033[33MarzBot has been installed successfully\033[0m"

wait

if [ "$(id -u)" -ne 0 ]; then
    echo -e "\033[33mPlease run as root\033[0m\n"
    exit
fi

read -p "Enter the domain: " domainname
if [ "$domainname" = "" ]; then

echo -e "\n\033[91mPlease wait ...\033[0m\n"
sleep 3

echo -e "\e[36mNothing was registered for the domain.\033[0m\n"

echo -e "\n\033[0m Good Luck Baby\n"

else
# variables
DOMAIN_NAME="$domainname"
# WILDCARD_DOMAIN="*.$wildcarddomain"
# curl-php
sudo apt-get install php-curl -y

# Allow HTTP and HTTPS traffic
echo -e "\n\033[1;7;31mAllowing HTTP and HTTPS traffic...\033[0m\n"
sudo ufw allow 80
sudo ufw allow 443

# Let's Encrypt
echo -e "\n\033[1;7;32mInstalling Let's Encrypt...\033[0m\n"
sudo apt install letsencrypt -y

# automatic certificate renewal
echo -e "\n\033[1;7;33mEnabling automatic certificate renewal...\033[0m\n"
sudo systemctl enable certbot.timer

# SSL certificate using standalone mode
echo -e "\n\033[1;7;34mObtaining SSL certificate using standalone mode...\033[0m\n"
sudo certbot certonly --standalone --agree-tos --preferred-challenges http -d $DOMAIN_NAME

# Certbot Apache plugin
echo -e "\n\033[1;7;35mInstalling Certbot Apache plugin...\033[0m\n"
sudo apt install python3-certbot-apache -y

# SSL certificate using Apache plugin
echo -e "\n\033[1;7;36mObtaining SSL certificate using Apache plugin...\033[0m\n"
sudo certbot --apache --agree-tos --preferred-challenges http -d $DOMAIN_NAME

# SSL certificate using manual DNS mode (wildcard)
# echo -e "\n\033[1;7;33mObtaining SSL certificate using manual DNS mode (wildcard)...\033[0m\n"
# sudo certbot certonly --manual --agree-tos --preferred-challenges dns -d $DOMAIN_NAME -d $WILDCARD_DOMAIN


systemctl restart cron
systemctl restart apache2

echo -e "\e[32m======================================"
echo -e "SSL certificate obtained successfully!"
echo -e "======================================\033[0m"

#mysqlpass
read -p "Enter the mysql password: " mysqlpass
if [ "$mysqlpass" = "" ]; then

echo -e "\n\033[91mPlease wait ...\033[0m\n"
sleep 3

echo -e "\e[36mNothing was Entered.\033[0m\n"

echo -e "\n\033[0m Good Luck Baby\n"
else
# variables
MYSQL_PASS="$mysqlpass"

sudo mysql -u root -pPASSWORD -e "alter user 'root'@'localhost' identified with mysql_native_password by '$MYSQL_PASS';FLUSH PRIVILEGES;"
fi

fi

#!/bin/bash


if [ "$(id -u)" -ne 0 ]; then
    echo -e "\033[33mPlease run as root\033[0m"
    exit
fi

wait

echo " "

read -p "Enter your root password: " ROOT_PASSWORD
# read -p "Enter your root : " ROOT_USER
ROOT_USER="root"
echo "SELECT 1" | mysql -u$ROOT_USER -p$ROOT_PASSWORD 2>/dev/null


if [ $? -eq 0 ]; then

wait

    randomdbpass=$(openssl rand -base64 10 | tr -dc 'a-zA-Z0-9' | cut -c1-8)

    randomdbdb=$(openssl rand -base64 10 | tr -dc 'a-zA-Z' | cut -c1-8)

    if [[ $(mysql -u root -p$ROOT_PASSWORD -e "SHOW DATABASES LIKE 'mirzapanel'") ]]; then
        clear
        echo -e "\n\e[91mYou have already created the database\033[0m\n"
    else
        dbname=marzbot
        clear
        echo -e "\n\e[32mPlease enter the database username!\033[0m"
        printf "[+] Default user name is \e[91m${randomdbdb}\e[0m ( let it blank to use this user name ): "
        read dbuser
        if [ "$dbuser" = "" ]; then
        dbuser=$randomdbdb
        fi

        echo -e "\n\e[32mPlease enter the database password!\033[0m"
        printf "[+] Default user name is \e[91m${randomdbpass}\e[0m ( let it blank to use this user name ): "
        read dbpass
        if [ "$dbpass" = "" ]; then
        dbpass=$randomdbpass
        fi

        mysql -u root -p$ROOT_PASSWORD -e "CREATE DATABASE $dbname;" -e "CREATE USER '$dbuser'@'%' IDENTIFIED WITH mysql_native_password BY '$dbpass';GRANT ALL PRIVILEGES ON * . * TO '$dbuser'@'%';FLUSH PRIVILEGES;" -e "CREATE USER '$dbuser'@'localhost' IDENTIFIED WITH mysql_native_password BY '$dbpass';GRANT ALL PRIVILEGES ON * . * TO '$dbuser'@'localhost';FLUSH PRIVILEGES;"
        echo -e "\n\e[95mDatabase Created Cotinuing...\033[0m"
        
        sleep 2
        
        # Database
        echo -e "\n\e[100mDatabase information:\033[0m"
        echo -e "\e[33mDatabase name: \e[36m${dbname}\033[0m" 
        echo -e "\e[33mDatabase username: \e[36m${dbuser}\033[0m"
        echo -e "\e[33mDatabase password: \e[36m${dbpass}\033[0m\n"

        wait
        
        echo -e "done!"

    fi


elif [ "$ROOT_PASSWORD" = "" ] || [ "$ROOT_USER" = "" ]; then
echo -e "\n\e[36mThe password is empty.\033[0m\n"
else 
# Install addres
echo -e "\n\e[36mThe password is not correct.\033[0m\n"
  
fi
echo -e "\n\e[91mInstall addres:\033[0m https://\e[92m${domainname}\033[0m/bottelegrammarzban/install"
echo -e "\e[91mphpmyadmin addres:\033[0m https://\e[92m${domainname}\033[0m/phpmyadmin\n"
