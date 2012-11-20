# !/bin/bash
mkdir /cainaoke/webhome/orderPad
mkdir -p /cainaoke/webhome/data
mkdir -p /cainaoke/webhome/config

#copy orderPad source code
echo "Fetching Source Code..."
scp -r root@192.168.0.1:/mnt/sda1/cnk_server/cnk_orderPad/* /cainaoke/webhome/orderPad/

#copy front & mangement source code
scp -r root@192.168.0.1:/mnt/sda1/cnk_server/cnk_mangement/cnk_web/* /cainaoke/webhome/orderPad/

#install orderPad
echo "Installing..."
wget http://127.0.0.1/orderPad/install.php

#install front & mangement

#copy menu & table settings
echo "Downloading menu for Demostration..."
scp -r root@192.168.0.1:/mnt/sda1/cnk_server/data /cainaoke/webhome/data
scp -r root@192.168.0.1:/mnt/sda1/cnk_server/data /cainaoke/webhome/data

#replace index for install
mv /cainaoke/webhome/index.php /cainaoke/webhome/index_1.php
cp /cainaoke/webhome/orderPad/install.php /cainaoke/webhome/index.php

#prepare for installation
sh /cainaoke/webhome/orderPad/install.sh

#Notify for Printer setting
echo "Done.\nPlease reboot the server.\nConfig Cainaoke after rebooting to finish installation."
