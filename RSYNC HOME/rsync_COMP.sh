#!/bin/bash

# tail -f /tmp/rsync.log

# 192.168.1.110 - Raspberry PI SERVER
# 192.168.1.190 - HP Omni
# 192.168.1.191 - Acer
# 192.168.1.192 - Compaq
# 192.168.1.193 - ASUS


TMP_FILE="/tmp/rsync_main_ip.txt"
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

echo "----PULL FROM SERVER: rsync -av --delete $SERVER_USER@$SERVER_IP:$SERVER_DIR/$SYNC_DIR $COMP_DIR" > /tmp/rsync.log
rsync -avu --delete $SERVER_USER@$SERVER_IP:$SERVER_DIR/$SYNC_DIR $COMP_DIR --log-file=/tmp/rsync.log

#modify,create,delete,move,open,moved_to,moved_from
inotifywait -e create,modify,delete,move -r -m $COMP_DIR/$SYNC_DIR --exclude '\.sh$' |
while read dir action file; 
do

	if [[ $file == ".goutputstream"* ]] && [ $action != "MODIFY" ]; then
	  continue
	fi

	if [ $action == "CREATE" ] || [ $action == "CREATE,ISDIR" ]; then
		continue
	fi

	if [[ (($action == "MOVED_FROM,ISDIR") && ($file == "untitled folder")) ]]; then
		continue
	fi

	if [[ (($action == "MOVED_FROM") && ($file == "new file")) ]]; then
		continue
	fi

	echo "-------------"
	echo "---$dir---"
	echo "---$action---"
	echo "---$file---"
	echo "-------------"
	
	#continue

	if ssh $SERVER_USER@$SERVER_IP "test -e $TMP_FILE"; then
		echo "-----$TMP_FILE IS EXISTS-----" > /tmp/rsync.log
	else
		echo "---SEND FILE TO SERVER: $TMP_FILE" > /tmp/rsync.log
		echo $IP > $TMP_FILE | scp $TMP_FILE $SERVER_USER@$SERVER_IP:/tmp
		rm $TMP_FILE
	fi

	echo -en "\n----------------\nPUSH TO SERVER\nrsync -av --delete $COMP_DIR/$SYNC_DIR $SERVER_USER@$SERVER_IP:$SERVER_DIR\n----------------\n\n" > /tmp/rsync.log
	rsync -avu --delete $COMP_DIR/$SYNC_DIR $SERVER_USER@$SERVER_IP:$SERVER_DIR --log-file=/tmp/rsync.log

done



