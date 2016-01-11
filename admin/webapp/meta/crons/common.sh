PHP_BIN=`/usr/bin/which php`
NICE_BIN=`/usr/bin/which nice`
TOUCH_BIN=`/usr/bin/which touch`
CHGRP_BIN=`/usr/bin/which chgrp`
CHMOD_BIN=`/usr/bin/which chmod`

PWD=`/usr/bin/dirname $0`
if [ "$PWD" == "." ];then
	PWD=`/bin/pwd`
fi
META_DIR=`/usr/bin/dirname $PWD`
WEBAPP_DIR=`/usr/bin/dirname $META_DIR`
BASE_DIR=`/usr/bin/dirname $WEBAPP_DIR`
DOCROOT_DIR="$BASE_DIR/docroot"
SCRIPT_NAME=`/bin/basename $0`
LOG_FILE="/var/log/smta/${SCRIPT_NAME}${1:+_}${1:-}.log"
DISABLE_LOGGING="0"

function execute() {
	 if [ "$COMMAND" != "" ]; then
	 	if [ $DISABLE_LOGGING = "1" ]; then
	 		$NICE_BIN -n19 $COMMAND 2>&1
	 	elif ([ $DISABLE_LOGGING != "1" ] && [ ! -e "$LOG_FILE" ] && [ -w `/usr/bin/dirname $LOG_FILE` ]) || [ -w "$LOG_FILE" ]; then
	 		if ([ ! -e "$LOG_FILE" ]); then
		 		$TOUCH_BIN $LOG_FILE
		 		$CHGRP_BIN apache $LOG_FILE
		 		$CHMOD_BIN 775 $LOG_FILE
		 	fi
			$NICE_BIN -n19 $COMMAND &>>$LOG_FILE &
		else 
			echo "WARNING: We cannot write to the log file at $LOG_FILE" 1>&2
			echo "WARNING: We can still proceed, but will not background this process." 1>&2
			echo "WARNING: Running command: $NICE_BIN -n19 $COMMAND 2>&1" 1>&2
			echo "" 1>&2
			$NICE_BIN -n19 $COMMAND 2>&1
		fi

		return 0
	else
		echo "ERROR: Missing the COMMAND variable, please specify the COMMAND variable in the calling script at ${PWD}/${SCRIPT_NAME}" 1>&2
	fi
	return 1
}