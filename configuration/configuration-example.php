<?php
return array(
    "hostname"   => "irc.freenode.net",
    "servername" => "example.com",
    "port"       => 6667,
    "username"   => "examplebot",
    "realname"   => "example IRC Bot",
    "nick"       => "examplebot",
    "password"   => "some_password",        // This one is optional and can be removed to skip the 'identify' step after logging in

    "channels"   => '#example-channel',
    "channel_passwords" => 'example-password',  // This one is also optional and can be skipped if the channel isn't private

    "admins"     => array( 'example' ),

    "debug"      => true,
    "log"        => __DIR__ . '/../logs/bot.log',
);

