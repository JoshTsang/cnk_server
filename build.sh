# !/bin/bash
mkdir -p /cainaoke/webhome/data
mkdir -p /cainaoke/webhome/config
mkdir /cainaoke/webhome/db
mkdir /cainaoke/webhome/db/temp
mkdir /cainaoke/webhome/db/temporary
mkdir /cainaoke/webhome/db/dev

chmod 777 /cainaoke/webhome/data
chmod 777 /cainaoke/webhome/config
chmod 777 /cainaoke/webhome/db
chmod 777 /cainaoke/webhome/db/temp
chmod 777 /cainaoke/webhome/db/temporary
chmod 777 /cainaoke/webhome/db/dev

#construct db
curl http://127.0.0.1/orderPad/build/db.php

#rc.local
cp /cainaoke/webhome/orderPad/build/rc.local /etc
#Notify for Printer setting
echo "Upload cnk.apk to finish installation.Then reboot the server."

#remove install files
rm -rf /cainaoke/webhome/orderPad/build
rm -f /cainaoke/webhome/orderPad/build.sh
