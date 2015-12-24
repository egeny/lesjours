#!/bin/bash

# Change for main directory
cd $(dirname $0)/../dist
clear

PORT=8000
while lsof -i :$PORT > /dev/null; do
	((PORT++))
done

open "http://localhost:$PORT"
python -m SimpleHTTPServer $PORT