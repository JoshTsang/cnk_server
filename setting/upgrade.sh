#!/bin/bash
echo "remove\ upgrade\ dir"
rmdir -rvf ../../upgrade

echo "make dirs"
mkdir ../../upgrade
mkdir ../../upgrade/orderPad
mkdir ../../upgrade/orderPad/setting
mkdir ../../upgrade/orderPad/setting/css
mkdir ../../upgrade/orderPad/setting/js
mkdir ../../upgrade/orderPad/classes

echo "cp setting"
cp *.php ../../upgrade/orderPad/setting
cp *.apk ../../upgrade/orderPad/setting
cp ver ../../upgrade/orderPad/setting
rm ../../upgrade/orderPad/settinggetUpgradePack.php

echo "cp css"
cp css/*.css ../../upgrade/orderPad/setting/css
cp css/*.gif ../../upgrade/orderPad/setting/css

echo "cp classes"
cp ../classes/*.php ../../upgrade/orderPad/classes

echo "cp js"
cp js/*.js ../../upgrade/orderPad/setting/js

echo "cp php"
cp ../*.php ../../upgrade/orderPad

echo "construct updgrade package"
cd ../../upgrade/orderPad/setting
tar -cvf ../../cnk.bin orderPad -C ../../