# !/bin/bash
mkdir -p /cainaoke/webhome/orderPad/data
mkdir -p /cainaoke/webhome/orderPad/conf
mkdir /cainaoke/webhome/db
mkdir /cainaoke/webhome/db/temp
mkdir /cainaoke/webhome/db/temporary
mkdir /cainaoke/webhome/db/dev

chmod 777 /cainaoke/webhome/orderPad/data
chmod 777 /cainaoke/webhome/orderPad/conf
chmod 777 /cainaoke/webhome/db
chmod 777 /cainaoke/webhome/db/temp
chmod 777 /cainaoke/webhome/db/temporary
chmod 777 /cainaoke/webhome/db/dev

#construct db
curl http://127.0.0.1/orderPad/build/db.php

#rc.local
cp /cainaoke/webhome/orderPad/build/rc.local /etc

ln -s /sbin/ifconfig /bin/ifconfig
#Notify for apk upload
echo "*************************************************************";
echo "Upload cnk.apk to finish installation.Then reboot the server."
echo "*************************************************************";

#remove install files
rm -rf /cainaoke/webhome/orderPad/build
rm -f /cainaoke/webhome/orderPad/build.sh
