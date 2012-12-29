#!/usr/bin/env php
<?php
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../configuration/configuration.php';

$message_handler = new OftBot\MessageHandler\MessageHandler($config);

$game_manager = new OftBot\GameManager();

$message_handler->onChannel('/^@oftbot suggest$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->suggest();
});

$message_handler->onChannel('/^@oftbot killgame$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->killGame();
});

$message_handler->onChannel('/^@oftbot join$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->join();
});

$message_handler->onChannel('/^@oftbot leave$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->leave();
});

$message_handler->onChannel('/^@oftbot kick (.*)$/', function ($event) use ($game_manager) {
    $kick = $event->getMatches();
    $kick = $kick[0];
    $game_manager->event = $event;
    $game_manager->kick($kick);
});

$message_handler->onChannel('/^@oftbot start$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->start();
});

$message_handler->onChannel('/^@oftbot roll$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->roll();
});

$message_handler->onChannel('/^@oftbot (k|keep) (.*)$/', function ($event) use ($game_manager) {
    $kept = $event->getMatches();
    $kept = $kept[1];
    $game_manager->event = $event;
    $game_manager->keep($kept);
});

$message_handler->onChannel('/^@oftbot help$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->help();
});

$message_handler->onChannel('/^@oftbot status$/', function ($event) use ($game_manager) {
    $game_manager->event = $event;
    $game_manager->status();
});

$message_handler->run();
