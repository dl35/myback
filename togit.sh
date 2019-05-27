#!/bin/bash

read -p "message to commit ? " message
if [ ${#message} == 0 ] ; then
message="commit"
fi

echo "mess -> " $message 
git add .
git commit --m "$message"
git push 
