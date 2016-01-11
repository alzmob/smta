#! /bin/sh
#
# Extended PMTA script
#
# $Id: pmta-init 8268 2006-05-25 23:06:05Z avery $
#
# chkconfig: 2345 80 30
# description: Starts and stops the PowerMTA ESMTP mailer
#
### BEGIN INIT INFO
# Provides: pmta
# Required-Start: $network $named $syslog $time $remote_fs
# Should-Start: gmsmux
# Required-Stop:
# Should-Stop:
# Default-Start: 2 3 4 5
# Default-Stop:
# Short-Description: Port25's Message Transfer Agent (MTA)
# Description: PowerMTA is an extremely fast Message Transfer Agent (MTA).
### END INIT INFO
#

RETVAL=0

PMTA_SCRIPT=/etc/init.d/pmta

# detect the version of linux
VERSION=generic
if [ -f /etc/redhat-release ] ; then
    VERSION=redhat
    . /etc/rc.d/init.d/functions
elif [ -f /etc/SuSE-release ] ; then
    VERSION=suse
    . /etc/rc.status
    rc_reset
fi


set_limits() {
    OLIMIT=$(ulimit -Hn)
    if [ "$OLIMIT" != "unlimited" ] && [ "$OLIMIT" -lt 32768 ] ; then
        # raise RLIMIT_NOFILE (max file descriptors)
        ulimit -Hn 32768
    fi

    OLIMIT=$(ulimit -Hu)
    if [ "$OLIMIT" != "unlimited" ] && [ "$OLIMIT" -lt 32768 ] ; then
        # raise RLIMIT_NPROC (max user processes)
        ulimit -Hu 32768
    fi
}


case "$1" in
    start)
        if [ "$VERSION" == "suse" ] ; then
            echo -n "Starting PowerMTA "
        else
            echo -n "Starting PowerMTA: "
        fi

        set_limits

        # Tell PowerMTA to use 'ioctl' rather than 'getifaddrs' for detecting
        # local IP addresses.  This is normally only needed for older kernels
        #export PMTA_USE_GETIFADDRS=0

        # Use pmtawatch rather than 'daemon' or 'startproc', as the latter may
        # not catch some startup errors
        /usr/sbin/pmtawatch --start >/dev/null 2>&1 </dev/null
        RETVAL=$?

        case "$VERSION" in
            redhat)
                [ $RETVAL -eq 0 ] && success "$text" || failure "$text"
                [ $RETVAL -eq 0 ] && touch /var/lock/subsys/pmta
                echo
                ;;
            suse)
                [ $RETVAL -eq 0 ] || rc_failed
                rc_status -v
                ;;
            generic)
                [ $RETVAL -eq 0 ] && echo "done." || echo "failed."
                ;;
        esac
        [ $RETVAL -ne 0 ] && $0 log
        ;;
    stop)
        $PMTA_SCRIPT $1
        ;;
    try-restart|condrestart)
        $PMTA_SCRIPT $1
        ;;
    restart)
        $PMTA_SCRIPT $1
        ;;
    force-reload|reload)
        $PMTA_SCRIPT $1
        ;;
    status)
        $PMTA_SCRIPT $1
        ;;
    console)
        pmta show status
        ;;
    log)
        tail -n500 /var/log/pmta/log
        ;;
    logstartup)
        tail -n10 /var/log/messages | grep "pmta"
        ;;
    flush|clear-spools|cs)
        $0 stop
        if [ "$VERSION" == "suse" ] ; then
            echo -n "Flushing PowerMTA Queue"
        else
            echo -n "Flushing PowerMTA Queue: "
        fi

        find /var/spool/pmta/ -mindepth 1 -type d | xargs rm -rf  # clear spools
        RETVAL=$?

        case "$VERSION" in
            redhat)
                [ $RETVAL -eq 0 ] && success "$text" || failure "$text"
                echo
                ;;
            suse)
                [ $RETVAL -eq 0 ] || rc_failed
                rc_status -v
                ;;
            generic)
                [ $RETVAL -eq 0 ] && echo "done." || echo "failed."
                ;;
        esac
        $0 start
        ;;
    *)
        echo "Usage: $0 {start|stop|status|try-restart|restart|force-reload|reload|clear-spools|log|console}"
        exit 1
        ;;
esac

if [ "$VERSION" == "suse" ] ; then
    rc_exit
else
    exit $RETVAL
fi
