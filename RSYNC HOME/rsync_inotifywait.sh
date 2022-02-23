#!/bin/bash
# 192.168.1.110 - Raspberry PI SERVER
# 192.168.1.190 - HP Omni
# 192.168.1.191 - Acer
# 192.168.1.192 - Compaq

IP=$(hostname -I | awk '{print $1}')

# Define each array and then add it to the main one
SUB_1=("192.168.1.110" "pi" "/media/pi/07b6b844-0c3a-4e62-bb3d-b51cc61a5c0a")
SUB_2=("192.168.1.190" "laptopsr" "~")
SUB_3=("192.168.1.191" "laptopsr" "~")
SUB_4=("192.168.1.192" "laptopsr" "~")

MAIN_ARRAY=(
  SUB_1[@]
  SUB_2[@]
  SUB_3[@]
  SUB_4[@]
)

#modify,create,delete,move,open
while inotifywait -r -e create,modify,delete,move,moved_from,moved_to $HOME/MOE
do
	# Loop and print it.  Using offset and length to extract values
	COUNT=${#MAIN_ARRAY[@]}
	for ((i=0; i<$COUNT; i++))
	do
		SUB_IP=${!MAIN_ARRAY[i]:0:1}
		SUB_USER=${!MAIN_ARRAY[i]:1:1}
		SUB_DEST=${!MAIN_ARRAY[i]:2:1}
		#echo "${SUB_IP} ${SUB_USER} ${SUB_DEST}"

		if ping -c 1 $SUB_IP > /dev/null 2>&1;
		then
			if [ "$IP" != "$SUB_IP" ]; then
				SENDING="Sending... $SUB_USER@$SUB_IP:$SUB_DEST. My IP ($IP)"
				echo $SENDING
				echo $SENDING > /tmp/rsync.log
				rsync -avu --delete $HOME/MOE $SUB_USER@$SUB_IP:$SUB_DEST --log-file=/tmp/rsync.log
			fi
		else
			echo "ERROR connection $SUB_IP"
		fi
	done
done



