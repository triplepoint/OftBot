<?php

namespace OftBot\MessageHandler;

use \Philip\Philip;
use \Philip\IRC\Response;

class MessageHandler extends Philip
{
    /**
     * Override the normal Philip::run() method to add the identify step.
     *
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

    /**
     * Perform the IDENTIFY conversation with NickServ.
     *
     * Note that this behavior has been tuned to wait a few seconds after
     * the IDENTIFY conversation, to give the server a chance to acknowledge it.
     *
     * @return void
     */
    public function identify()
    {
        if (array_key_exists('password', $this->config) && $this->config['password']) {
            $this->send(Response::msg('NickServ', 'IDENTIFY' . ' ' . $this->config['username'] . ' ' . $this->config['password']));
            sleep(20);
        }
    }
}
