<?php

namespace OftBot\MessageHandler;

use \Philip\Philip;
use \Philip\IRC\Response;

class MessageHandler extends Philip
{
    /**
     * Override the normal Philip::run() method to add the identify step.
     * @return void
     */
    public function run()
    {
        if ($this->connect()) {
            $this->login();
            $this->identify();
            $this->join();
            $this->listen();
        }
    }

    public function identify()
    {
        if (array_key_exists('password', $this->config) && $this->config['password']) {
            $this->send(Response::msg('NickServ', 'IDENTIFY'.' '. $this->config['username'].' '. $this->config['password']));
            sleep(20);
        }
    }
}
