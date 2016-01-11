#!/bin/bash
PWD=`/usr/bin/dirname $0`
cp $PWD/config /etc/pmta/config
cp $PWD/domain_config /etc/pmta/domain_config
cp $PWD/backoff_config /etc/pmta/backoff_config
cp $PWD/local_config /etc/pmta/local_config
cp $PWD/dkim.public /etc/pmta/dkim.public
cp $PWD/dkim.private /etc/pmta/dkim.private
$PWD/pmta_help.sh