<?php

return array(
    'modules' => array(
        'Application',
        'Core',
        'ZealMessages',
    ),
    'db' => array(
        'driver' => 'PDO',
        'dsn' => 'pgsql:host=localhost;dbname=database_test',
        'username' => '',
        'password' => '',
    )
);
