#!/bin/bash
stty -F /dev/ttyUSB0 19200 cs8 -cstopb -parenb ixon
echo "" > /var/www/html/steca/direct_data.txt
COUNTER=0

echo "date:$(date -u)";
while read line; do
	if [[ "$line" == *"PID"* ]]; then
		echo "$line" >> /var/www/html/steca/direct_data.txt
		COUNTER=$((COUNTER+1))
	else
		echo "$line" >> /var/www/html/steca/direct_data.txt

		if [[ "$COUNTER" > 1 ]]; then
			break
		fi
	fi
done < /dev/ttyUSB0
