<?php
return array(
    'DB_TYPE' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'dingzhi',
    'DB_USER' => 'root',
    'DB_PWD' => '',
    'DB_PORT' => 3306,
    'DB_PREFIX' => 'ecs_',

    'HOST'=>'http://'.$_SERVER['HTTP_HOST'].__ROOT__,//最后没有“/”
);