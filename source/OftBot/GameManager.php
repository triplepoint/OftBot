<?php

namespace OftBot;

use \OftBot\Game;
use \Philip\IRC\Response;

class GameManager
{
    /**
     * The current game if there is one, or null if there isn't.
     *
     * @var Game
     */
    protected $current_game;

    // -- ADMIN TASKS ---

    public function suggest($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            if ($this->current_game) {
                throw new \Exception("There's already a game in progress.");
            }

            $this->current_game = new Game($user);

            $event->addResponse(Response::notice($channel, "$user suggests a game!"));

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function nevermind($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            if (!$this->current_game) {
                throw new \Exception("There's no game in progress.");
            }

            if ($user != $this->current_game->getGameOwner()->getName()) {
                throw new \Exception("You didn't create this game, ".$this->current_game->getGameOwner()->getName()." did.");
            }

            $this->current_game->cancelGame();
            $this->current_game = null;

            $event->addResponse(Response::notice($channel, 'The game has been cancelled.'));

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function start($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            if (!$this->current_game) {
                throw new \Exception("There's no game in progress.");
            }

            if ($user != $this->current_game->getGameOwner()->getName()) {
                throw new \Exception("You didn't create this game, ".$this->current_game->getGameOwner()->getName()." did.");
            }

            $this->current_game->start();

            $event->addResponse(Response::notice($channel, "Joining has ended, time to play the game."));

            $players = $this->current_game->getPlayers();
            $names = array_map(
                function ($player) {
                    return $player->getName();
                },
                $players
            );

            $event->addResponse(Response::notice($channel, "Play order: ".join(', ',$names)));

            $current_player = $this->current_game->getCurrentPlayer();

            $event->addResponse(Response::notice($channel, $current_player->getName().", it's your turn to roll"));

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function kick($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            $kick_user = $event->getMatches();
            $kick_user = $kick_user[0];

            if (!$this->current_game) {
                throw new \Exception("There's no game in progress.");
            }

            if ($user != $this->current_game->getGameOwner()->getName()) {
                throw new \Exception("You didn't create this game, ".$this->current_game->getGameOwner()->getName()." did.");
            }

            $this->current_game->removePlayer($kick_user);

            $event->addResponse(Response::notice($channel, "$kick_user was kicked from the game."));

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    // -- GENERAL TASKS --

    public function join($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            if (!$this->current_game) {
                throw new \Exception("There's no game in progress.");
            }

            $this->current_game->addPlayer($user);

            $event->addResponse(Response::notice($channel, "$user joined the game."));

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function leave($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            if (!$this->current_game) {
                throw new \Exception("There's no game in progress.");
            }

            $this->current_game->removePlayer($user);

            $event->addResponse(Response::notice($channel, "$user left the game."));

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function roll($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            if (!$this->current_game) {
                throw new \Exception("There's no game in progress.");
            }

            // Its not your turn
            if (!$this->current_game->getCurrentPlayer()->getName() == $user) {
                throw new \Exception("It's not your turn to play.");
            }

            $this->do_roll($event);

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function keep($event)
    {
        try {
            $user    = $event->getRequest()->getSendingUser();
            $channel = $event->getRequest()->getSource();

            $kept = $event->getMatches();
            $kept = str_replace(',', '', $kept[0]);
            $kept = explode(' ', trim($kept));

            if (!$this->current_game) {
                throw new \Exception("There's no game in progress.");
            }

            // Its not your turn
            if (!$this->current_game->getCurrentPlayer()->getName() == $user) {
                throw new \Exception("It's not your turn to play.");
            }

            $this->current_game->keep($kept);

            // TODO show some message for the kept die?

            // TODO Check to see if its still this user's turn. If not, show hteir final score,
            // if it is, do another roll event

            $this->do_roll($event);

        } catch (\Exception $e) {
            $event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    protected function do_roll($event)
    {
        $user    = $event->getRequest()->getSendingUser();
        $channel = $event->getRequest()->getSource();

        $this->current_game->roll();

        $event->addResponse(Response::notice($channel, "$user rolled: ".join(', ', $this->current_game->getCurrentPlayer()->getRoll())."."));
        $event->addResponse(Response::notice($channel, "What would you like to keep?"));
    }

    // -- MISCELLANEOUS --

    public function help($event)
    {

    }

    public function status($event)
    {

    }

    public function stats($event)
    {

    }
}
