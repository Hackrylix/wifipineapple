#!/bin/sh
#Downloads and installs infusions
#Do not touch.

#Set up variables for more readability.
name=$1
version=$2
dest=$3
md5=$4

#Tell the installer that it is working.
sed -i 's/"done"/"working"/g' /pineapple/modules/installer.php
sed -i 's/"md5"/"working"/g' /pineapple/modules/installer.php


#Remove any left-overs.
rm -rf /usb/tmp/modules
rm -rf /tmp/modules

#Download infusion. Do the magic.
if [[ $dest == "usb" ]]
	then
		mkdir -p /usb/tmp/modules
		wget -O /usb/tmp/modules/mk4-module-$name-$version.tar.gz "http://cloud.wifipineapple.com/index.php?downloads&downloadModule=$name&moduleVersion=$version"
		if [[ $(md5sum /usb/tmp/modules/mk4-module-$name-$version.tar.gz | head -c 33) == $md5 ]]
			then
				mkdir -p /usb/modules/
				rm /pineapple/modules/usbModules
				ln -s /usb/modules /pineapple/modules/usbModules
				tar -xzf /usb/tmp/modules/mk4-module-$name-$version.tar.gz -C /usb/tmp/modules/
					#get config stuff
					config=$(cat /usb/tmp/modules/mk4-module-$name-$version/module.conf)
					confName=$(echo "$config" | grep -i name | awk '{split($0,array,"=")} END{print array[2]}')
					confVersion=$(echo "$config" | grep -i name | awk '{split($0,array,"=")} END{print array[2]}')
					confAuthor=$(echo "$config" | grep -i author | awk '{split($0,array,"=")} END{print array[2]}')
					confStartPage=$(echo "$config" | grep -i startPage | awk '{split($0,array,"=")} END{print array[2]}')
					confSupportLink=$(echo "$config" | grep -i supportLink | sed 's/supportLink=//g')
				mv /usb/tmp/modules/mk4-module-$name-$version/$confName /usb/modules/
				rm -rf /usb/tmp/modules
				echo "$confName|$confVersion|$dest|$confStartPage|$confSupportLink" >> /pineapple/modules/moduleList
			else
				sed -i 's/working/md5/g' /pineapple/modules/installer.php
				rm -rf /usb/tmp/modules
				exit
		fi
	else
		mkdir -p /tmp/modules
		wget -O /tmp/modules/mk4-module-$name-$version.tar.gz "http://cloud.wifipineapple.com/index.php?downloads&downloadModule=$name&moduleVersion=$version"
		if [[ $(md5sum /tmp/modules/mk4-module-$name-$version.tar.gz | head -c 33) == $md5 ]]
			then
				tar -xzf /tmp/modules/mk4-module-$name-$version.tar.gz -C /tmp/modules/
					#get config stuff
					config=$(cat /tmp/modules/mk4-module-$name-$version/module.conf)
					confName=$(echo "$config" | grep -i name | awk '{split($0,array,"=")} END{print array[2]}')
					confVersion=$(echo "$config" | grep -i name | awk '{split($0,array,"=")} END{print array[2]}')
					confAuthor=$(echo "$config" | grep -i author | awk '{split($0,array,"=")} END{print array[2]}')
					confStartPage=$(echo "$config" | grep -i startPage | awk '{split($0,array,"=")} END{print array[2]}')
					confSupportLink=$(echo "$config" | grep -i supportLink | sed 's/supportLink=//g')
				mv /tmp/modules/mk4-module-$name-$version/$confName /pineapple/modules/
				rm -rf /tmp/modules
				echo "$confName|$confVersion|$dest|$confStartPage|$confSupportLink" >> /pineapple/modules/moduleList
			else
				sed -i 's/working/md5/g' /pineapple/modules/installer.php
				rm -rf /tmp/modules
				exit
		fi
fi


#Tell the installer that it is done
sed -i 's/working/done/g' /pineapple/modules/installer.php