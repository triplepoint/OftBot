<?php

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../configuration/configuration.php';

$bot = new Philip($config);

$bot->onChannel('/^!echo (.*)$/', function($event) {
    $matches = $event->getMatches();
    $event->addResponse(Response::msg($event->getRequest()->getSource(), trim($matches[0])));
});

$bot->run();
