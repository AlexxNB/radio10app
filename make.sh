#!/bin/bash
DEVSRV="root@teplogsm.ru"
DEVSRVPATH="/var/www/sites/hisense.alexxnb.ru/radio10"

DIR=$(dirname $0)
if [[ $DIR == "." ]]; then DIR=$(pwd); fi;

rsync -rlptzv --progress --delete --include=.htaccess --exclude=.* --exclude=*.sh  ${DIR}/ ${DEVSRV}:${DEVSRVPATH}