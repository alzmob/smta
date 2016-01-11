#!/bin/sh
#
if [ "$1" == "move" ];then 
	CFG_FILE=`/bin/basename $2`
	/bin/mv $2 /etc/sysconfig/network-scripts/$CFG_FILE
elif [ "$1" == "remove" ];then
	CFG_FILE=`/bin/basename $2`
	/bin/rm -f /etc/sysconfig/network-scripts/$CFG_FILE
fi