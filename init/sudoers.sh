#!/bin/bash
# Make changes to the sudoers configuration
if [ -f /etc/sudoers ]; then
  /bin/sed -i 's/^Defaults *requiretty/#Defaults    requiretty/' /etc/sudoers
  if [ `grep -c "^apache  ALL=NOPASSWD: PMTA" /etc/sudoers` = "0" ]; then
    echo "## Pmta" >> /etc/sudoers
    echo "Cmnd_Alias PMTA = /usr/sbin/pmta, /etc/init.d/pmta, /sbin/ifconfig, /sbin/ip, /sbin/arping, /home/smta/admin/webapp/meta/crons/pmta.sh, /home/smta/admin/webapp/meta/crons/test_ips.sh, /home/smta/admin/webapp/meta/crons/move_eth_file.sh" >> /etc/sudoers
    echo "apache  ALL=NOPASSWD: PMTA" >> /etc/sudoers
  else
    /bin/sed -i 's/^Cmnd_Alias PMTA.*/Cmnd_Alias PMTA = \/usr\/sbin\/pmta, \/etc\/init.d\/pmta, \/sbin\/ifconfig, \/sbin\/ip, \/sbin\/arping, \/home\/smta\/admin\/webapp\/meta\/crons\/pmta.sh, \/home\/smta\/admin\/webapp\/meta\/crons\/test_ips.sh, \/home\/smta\/admin\/webapp\/meta\/crons\/move_eth_file.sh/' /etc/sudoers
  fi
fi