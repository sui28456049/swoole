echo "loading..."
pid=`pidof live_game`
echo $pid
kill -USR1 $pid
echo "loading success"