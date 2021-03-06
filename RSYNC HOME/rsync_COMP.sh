#!/bin/bash

# tail -f /tmp/rsync.log

# 192.168.1.110 - Raspberry PI SERVER
# 192.168.1.190 - HP Omni
# 192.168.1.191 - Acer
# 192.168.1.192 - Compaq
# 192.168.1.193 - ASUS

IP=$(hostname -I | awk '{print $1}')

# THIS COMP
COMP_DIR=$HOME

if [ $IP == "192.168.1.193" ]; then
	COMP_DIR="/media/laptopsr/sdb1"
fi

# SERVER
SERVER_IP="192.168.1.110"
SERVER_USER="pi"
SERVER_DIR="/media/pi/07b6b844-0c3a-4e62-bb3d-b51cc61a5c0a"

# SYNC DIR
SYNC_DIR="MOE"

# Check this IP is on server
TMP_FILE="/tmp/rsync_main_ip.txt"
if ssh $SERVER_USER@$SERVER_IP "test -e $TMP_FILE"; then
	echo "-----$TMP_FILE IS EXISTS-----" > /tmp/rsync.log
	scp $SERVER_USER@$SERVER_IP:$TMP_FILE $TMP_FILE
	MAIN_IP_FROM=$(cat $TMP_FILE)
	if [ "$IP" == "$MAIN_IP_FROM" ]; then
		echo -en "\n----------------\n----CLEAR MY IP FROM $TMP_FILE\n----------------\n\n" > /tmp/rsync.log
		cat /dev/null > $TMP_FILE | scp $TMP_FILE $SERVER_USER@$SERVER_IP:$TMP_FILE
		rm $TMP_FILE
	fi
fi

echo "----PULL FROM SERVER: rsync -av --delete $SERVER_USER@$SERVER_IP:$SERVER_DIR/$SYNC_DIR $COMP_DIR" > /tmp/rsync.log
rsync -avu --delete $SERVER_USER@$SERVER_IP:$SERVER_DIR/$SYNC_DIR $COMP_DIR --log-file=/tmp/rsync.log

#close_write,create,modify,delete,move,open,moved_to,moved_from
inotifywait -e close_write,delete,move -r -m $COMP_DIR/$SYNC_DIR --format '%e %f' |
while read action file; 
do

	if [[ (($action == "CLOSE_WRITE,CLOSE") && ($file == "new file")) ]]; then
		continue
	fi

	echo -en "\n----------------\n$action - ($file)\n----------------\n\n"
	
	#continue

	#if ssh $SERVER_USER@$SERVER_IP "test -e $TMP_FILE"; then
	#	echo -en "\n----------------\n$TMP_FILE IS EXISTS\n----------------\n\n"
	#	echo -en "\n----------------\n$TMP_FILE IS EXISTS\n----------------\n\n" > /tmp/rsync.log
	#else
		echo -en "\n----------------\nSEND NEW $TMP_FILE\n----------------\n\n"
		echo -en "\n----------------\nSEND NEW $TMP_FILE\n----------------\n\n" > /tmp/rsync.log
		echo $IP > $TMP_FILE | scp $TMP_FILE $SERVER_USER@$SERVER_IP:$TMP_FILE
		rm $TMP_FILE
	#fi

	echo -en "\n----------------\nACTION: ($action)\nFILE: ($file)\nPUSH TO SERVER\nrsync -avu --delete $COMP_DIR/$SYNC_DIR $SERVER_USER@$SERVER_IP:$SERVER_DIR\n----------------\n\n"
	echo -en "\n----------------\nACTION: ($action)\nFILE: ($file)\nPUSH TO SERVER\nrsync -avu --delete $COMP_DIR/$SYNC_DIR $SERVER_USER@$SERVER_IP:$SERVER_DIR\n----------------\n\n" > /tmp/rsync.log
	rsync -avu --inplace --delete $COMP_DIR/$SYNC_DIR $SERVER_USER@$SERVER_IP:$SERVER_DIR --log-file=/tmp/rsync.log

	LAST_ACTION=$action
	LAST_FILE=$file
done



