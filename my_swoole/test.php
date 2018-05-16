<?php

function timer () {
    echo "hello world";
}
Swoole\Timer::tick(2000, 'timer');
