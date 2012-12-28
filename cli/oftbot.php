#!/usr/bin/env php
<?php

ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../configuration/configuration.php';

$message_handler = new OftBot\MessageHandler\MessageHandler($config);

$game_manager = new OftBot\GameManager();

$message_handler->onChannel('/^@oftbot suggest$/', function ($event) use ($game_manager) {
    $game_manager->suggest($event);
});

$message_handler->onChannel('/^@oftbot nevermind$/', function ($event) use ($game_manager) {
    $game_manager->nevermind($event);
});

$message_handler->onChannel('/^@oftbot join$/', function ($event) use ($game_manager) {
    $game_manager->join($event);
});

$message_handler->onChannel('/^@oftbot leave$/', function ($event) use ($game_manager) {
    $game_manager->leave($event);
});

$message_handler->onChannel('/^@oftbot kick (.*)$/', function ($event) use ($game_manager) {
    $game_manager->kick($event);
});

$message_handler->onChannel('/^@oftbot start$/', function ($event) use ($game_manager) {
    $game_manager->start($event);
});

$message_handler->onChannel('/^@oftbot roll$/', function ($event) use ($game_manager) {
    $game_manager->roll($event);
});

$message_handler->onChannel('/^@oftbot keep(.*)$/', function ($event) use ($game_manager) {
    $game_manager->keep($event);
});

$message_handler->run();
