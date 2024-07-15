#!/bin/bash

#CONNECTED_RESULT='supported=1 detected=1'
CONNECTED_RESULT='supported=1 detected=1, libcamera interfaces=0'
RESULT=$(vcgencmd get_camera)

case $CONNECTED_RESULT in

    $RESULT)
        touch $BM_CONTROL_CAM_CONNECTED_FILE
    ;;

    *)
        rm -f $BM_CONTROL_CAM_CONNECTED_FILE
    ;;
esac
