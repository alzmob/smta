#!/bin/bash
echo "If PMTA fails to start, then check /var/log/messages for errors.  Some common errors are:
	
  Startup error: Insufficient process resources
    Look in /etc/init.d/pmta and change the line that says 'ulimit -Hn 16384' to read 'ulimit -Hn 32676'
	
  Startup error: Error initializing thread: Error binding socket to 0.0.0.0:25, status = EADDRINUSE
    Turn off Sendmail by running 'service sendmail stop'.  You may want to turn off sendmail on start as well
    by running 'chkconfig sendmail off' and enabling pmta on startup  by running 'chkconfig pmta on'.
    
  Startup error: specification does not match any valid local IP addresses
    You have a vmta configuration file that has IP addresses in it that are not bound to the box.  Remove the 
    configuration file in /home/rad/cli/webapp/meta/vmta/ (or bind the IPs to the box) and restart pmta.
    
  Startup error: Error starting thread: "Bad mail" directory
  	The bad mail folder does not exist.  Check in /etc/pmta/config for the pickup and bad mail folders.  Make 
  	sure that the folders exist and are writeable by the pmta user.
";