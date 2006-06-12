#!/bin/sh

# The FULL path to the index.php file for the VegaDNS server
VEGADNS='http://127.0.0.1/vegadns-1.1.6/index.php'

# NOTE: You can get updates from multiple VegaDNS servers if 
# desired. Simply separate them by spaces like so:
# VEGADNS='http://server1/vegadns-x.x/index.php http://server2/vegadns-x.x/index.php'

# Path to the tinydns directory
TINYDNSDIR=/etc/tinydns

CUR="$TINYDNSDIR/root/data"
OLD="$TINYDNSDIR/root/data.old"
NEW="$TINYDNSDIR/root/data.new"


if [ -f "$CUR" ] ; then
    cp $CUR $OLD
fi

if [ -f "$NEW" ] ; then
    rm $NEW
fi

A=$[0]
for VD in $VEGADNS ; do
    A=$[$A+1]
    if wget -q -O "$TINYDNSDIR/root/data.srv-$A" $VD?state=get_data ; then
        if [ -s "$TINYDNSDIR/root/data.srv-$A" ] ; then
            cat "$TINYDNSDIR/root/data.srv-$A" >>$NEW
        else
            echo "ERROR: $TINYDNSDIR/root/data.srv-$A does not have a size greater than zero" 1>&2
            exit 1
        fi
    else
        echo "ERROR: wget did not return 0 when accessing $VD?state=get_data" 1>&2
        exit 1
    fi
    if [ -f "$TINYDNSDIR/root/data.srv-$A" ] ; then
        rm "$TINYDNSDIR/root/data.srv-$A"
    fi
done

# Don't run make if the files havn't changed
OLDSUM=$(sum $OLD | awk '{ print $1 " " $2}')
NEWSUM=$(sum $NEW | awk '{ print $1 " " $2}')

if [ "$OLDSUM" != "$NEWSUM" ]; then
    mv $NEW $CUR
    (cd $TINYDNSDIR/root ; make -s)
else
    rm $NEW
fi
