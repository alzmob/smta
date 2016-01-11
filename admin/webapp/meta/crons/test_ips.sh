#!/bin/sh
#
# This script is used to test IPs on a server
# Author: Mark Hobson
# Date: 11/16/2010
# Description: 	This script is used to test IPs on a server
#
# Include the command functions (this will import the variables PHP_BIN, DOCROOT_DIR, WEBAPP_DIR, LOG_FILE, etc)
#
PWD=`/usr/bin/dirname $0`
if [ "$PWD" == "." ];then
	PWD=`/bin/pwd`
fi
source $PWD/common.sh
#
# The following line is used for the live system
COMMAND="$PHP_BIN $DOCROOT_DIR/cron.php -m Cron -a IpTest $@"

DISABLE_LOGGING="1"
#
# Run the command by calling the execute() function defined in common.sh
#
execute
exit $?