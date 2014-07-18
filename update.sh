#!/bin/sh

WWW=/webs/sks-keyservers.net
S="${WWW}/status"
M="${WWW}/map"

php $S/sks-update.php

$WWW/updatezone.sh
