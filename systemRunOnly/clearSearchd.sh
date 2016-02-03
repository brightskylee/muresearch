#!/bin/bash

killall searchd
cd /home/muresearch/sphinxConfigFiles && rm -rf *
cd /home/muresearch/sphinxIndexDataFiles && rm -rf *
cd /home/muresearch/sphinxLoggingFiles && rm -rf *
mysql -u root -D muresearch2 --execute="TRUNCATE sphinxPortManager"
