#!/bin/bash
stty -F /dev/ttyUSB0 19200 cs8 -cstopb -parenb ixon

NEW_DATA=""
COUNTER=0

echo "date:$(date -u)";
while read -r f1 line; do
	if [[ "$f1" == *"Checksum"* ]]; then
		continue
	fi
	if [[ "$line" ]]; then
		if [[ "$f1" == *"PID"* ]]; then
			NEW_DATA+="\"${f1}\":\"${line//[[:space:]]}\","
			COUNTER=$((COUNTER+1))
		else
			if [[ "$COUNTER" > 1 ]]; then
				NEW_DATA+="\"${f1}\":\"${line//[[:space:]]}\""
				break
			else
				NEW_DATA+="\"${f1}\":\"${line//[[:space:]]}\","
			fi
		fi
	fi
done < /dev/ttyUSB0

if [[ "$NEW_DATA" ]]; then
	echo "{$NEW_DATA}" > /var/www/html/steca/direct_data_ttyUSB0.txt
	echo "Valmis"
fi

