#!/bin/bash
# THIS FILE FOR SERVER

IP=$(hostname -I | awk '{print $1}')
MySyncDirectory="/media/pi/07b6b844-0c3a-4e62-bb3d-b51cc61a5c0a/MOE"

#MainTxtFile="main_sync_computer.txt"
#echo $IP > $MainTxtFile | scp $MainTxtFile $SUB_USER@$SUB_IP:$SUB_DEST

# Define each array and then add it to the main one

# SYNC COMPUTERS
SUB_1=("192.168.1.190" "laptopsr" "~") # HP Omni
SUB_2=("192.168.1.191" "laptopsr" "~") # Acer
SUB_3=("192.168.1.192" "laptopsr" "~") # Compaq

MAIN_ARRAY=(
  SUB_1[@]
  SUB_2[@]
  SUB_3[@]
)

#modify,create,delete,move,open,moved_to,moved_from
inotifywait -e create,modify,delete,move -r -m $MySyncDirectory --exclude '\.sh$' |
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
	
	# Loop and print it.  Using offset and length to extract values
	COUNT=${#MAIN_ARRAY[@]}
	for ((i=0; i<$COUNT; i++))
	do
		SUB_IP=${!MAIN_ARRAY[i]:0:1}
		SUB_USER=${!MAIN_ARRAY[i]:1:1}
		SUB_DEST=${!MAIN_ARRAY[i]:2:1}

		if ping -c 1 $SUB_IP > /dev/null 2>&1;
		then
			if [ "$IP" != "$SUB_IP" ]; then
				echo "----ACTION: ($action)" > /tmp/rsync.log
				echo "------FILE: ($file)" > /tmp/rsync.log
				echo "---SEND TO: $SUB_USER@$SUB_IP:$SUB_DEST" > /tmp/rsync.log
				rsync -av --delete $MySyncDirectory $SUB_USER@$SUB_IP:$SUB_DEST --log-file=/tmp/rsync.log
			fi
			
		else
			echo "ERROR connection $SUB_IP"
		fi
	done
done



