<?php

namespace OftBot;

use \OftBot\Player;

class Game
{
    /**
     * The players in this game
     *
     * @var Player[]
     */
    protected $players = array();

    /**
     * The player that created this game
     * @var Player
     */
    protected $game_owner;

    /**
     * The player who's turn it is
     * @var Player
     */
    protected $current_player;

    /**
     * Has the game moved past the join stage?
     * @var boolean
     */
    protected $game_has_started = false;

    public function __construct($creating_player_name)
    {
        $this->addPlayer($creating_player_name);

        $index = $this->getPlayerIndexByName($creating_player_name);
        $this->game_owner = $this->players[$index];
    }

    public function getPlayers()
    {
        return $this->players;
    }

    public function shufflePlayers()
    {
        shuffle($this->players);
    }

    public function addPlayer($player_name)
    {
        if ($this->playerExists($player_name)) {
            throw new \Exception("You've already joined this game.");
        }

        if ($this->hasStarted()) {
            throw new \Exception("You can't join the game, it's already started.  Wait for the next one.");
        }

        $this->players[] = new Player($player_name);
    }

    public function removePlayer($player_name)
    {
        if (!$this->playerExists($player_name)) {
            throw new \Exception("That player isn't in the game.");
        }

        if ($player_name == $this->getGameOwner()->getName()) {
            throw new \Exception("The player that started the game can't leave.");
        }

        $index = $this->getPlayerIndexByName($player_name);

        unset($this->players[$index]);
    }

    public function playerExists($player_name)
    {
        return ($this->getPlayerIndexByName($player_name) !== false);
    }

    protected function getPlayerIndexByName($player_name)
    {
        foreach ($this->players as $index => $player) {
            if ($player->getName() == $player_name) {
                return $index;
            }
        }
        return false;
    }

    public function getCurrentPlayer()
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        return $this->current_player;
    }

    public function getGameOwner()
    {
        return $this->game_owner;
    }



    public function start()
    {
        if ($this->hasStarted()) {
            throw new \Exception("The game has already started.");
        }

        $this->game_has_started = true;

        $this->shufflePlayers();

        $this->current_player = $this->players[0];
    }

    public function cancelGame()
    {
        if ($this->hasStarted()) {
            throw new \Exception("The game's already started.  Play it through or kick all the players.");
        }
    }

    public function hasStarted()
    {
        return $this->game_has_started;
    }

    public function roll()
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        if ($this->getCurrentPlayer()->getRoll()) {
            throw new \Exception("You've already rolled, select your die to keep.");
        }

        // Do the roll
        $kept = $this->getCurrentPlayer()->getKept() ?: array();
        $die_count = 6 - count($kept);
        $roll = array();
        for ($i=0; $i<$die_count; $i++) {
            $roll[] = mt_rand(1, 6);
        }
        $this->getCurrentPlayer()->setRoll($roll);
    }

    public function keep($kept)
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        if (!$this->getCurrentPlayer()->getRoll()) {
            throw new \Exception("You haven't rolled yet, roll first.");
        }

        // Verify that the kept are actually in the user's roll

        // Add the kept to the user's kept set

        // Clear the user's roll

        // If the user has 6 kept die, they're done.  Mark them complete,
        //  Tally their score, and set the next current player.

    }

}
