#!/bin/bash

# change to dir
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd $DIR

#PHP
PHP=php

$PHP ExportMonth.php "$@"
