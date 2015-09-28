<?php
return [
    // IRC Connection configuration
    'hostname'   => 'chat.freenode.net',
    'servername' => 'example.com',
    'port'       => 6667,
    'username'   => 'examplebot',
    'realname'   => 'example IRC Bot',
    'nick'       => 'examplebot',
    'password'   => 'some_password',        // This one is optional and can be removed to skip the 'IDENTIFY' process after logging in

    // Channel configuration
    'channels'   => '#example-channel',     // For password protected channels, this could be an array where the key is the channel name, and the value is the password

    // You can safely ignore this
    'admins'     => [],
    'debug'      => false,
    'log'        => __DIR__ . '/../logs/bot.log',
];
