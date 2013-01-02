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
     *
     * @var Player
     */
    protected $game_owner;

    /**
     * The player who's turn it is
     *
     * @var Player
     */
    protected $current_player;

    /**
     * Has the game moved past the join stage?
     *
     * @var boolean
     */
    protected $game_has_started = false;

    /**
     * How many times has this game tied?  Zero means this is the first game.
     *
     * @var integer
     */
    protected $tie_counter = 0;

    public function __construct($creating_player_name)
    {
        $this->addPlayer($creating_player_name);

        $this->game_owner = $this->players[0];
    }

    public function getPlayers()
    {
        return $this->players;
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

        // TODO For now, disallow leaving if its your turn.  Eventually, this should be refined.
        if ($this->hasStarted() && $this->players[$index] == $this->getCurrentPlayer()) {
            throw new \Exception("The player who's current turn it is may not leave.");
        }

        unset($this->players[$index]);
    }

    public function playerExists($player_name)
    {
        return ($this->getPlayerIndexByName($player_name) !== false);
    }

    public function shufflePlayers()
    {
        shuffle($this->players);
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

    public function setCurrentPlayerToNextPlayer()
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        if (!$this->current_player) {
            $this->current_player = $this->players[0];

        } else if ($this->currentPlayerIsLastPlayer()) {
            throw new \Exception('The current player is the final player, cannot change to next player.');

        } else {
            $current_index = array_search($this->current_player, $this->players);
            $this->current_player = $this->players[$current_index+1];
        }
    }

    public function currentPlayerIsLastPlayer()
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        return ($this->current_player == $this->players[count($this->players)-1]);
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

        $this->setCurrentPlayerToNextPlayer();
    }

    public function continueTie()
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        if (!$this->isTied()) {
            throw new \Exception("You can't continue a tie if the game isn't tied.");
        }

        $this->clearPlayersForNewGame();

        $this->tie_counter = $this->tie_counter + 1;

        $this->current_player = null;
        $this->setCurrentPlayerToNextPlayer();
    }

    public function getTieCounter()
    {
        return $this->tie_counter;
    }

    protected function clearPlayersForNewGame()
    {
        foreach ($this->players as $player) {
            $player->clearScore();
            $player->clearKept();
            $player->clearRoll();
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

        if ($this->getCurrentPlayer()->turnIsComplete()) {
            throw new \Exception("You've already finished your turn.");
        }

        if ($this->getCurrentPlayer()->alreadyRolled()) {
            throw new \Exception("You've already rolled, select your dice to keep.");
        }

        $kept = $this->getCurrentPlayer()->getKept();
        $dice_count = 6 - count($kept);
        $roll = array();
        for ($i=0; $i<$dice_count; $i++) {
            $roll[] = mt_rand(1, 6);
        }
        $this->getCurrentPlayer()->setRoll($roll);
    }

    public function keep(array $kept)
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        if ($this->getCurrentPlayer()->turnIsComplete()) {
            throw new \Exception("You've already finished your turn.");
        }

        if (!$this->getCurrentPlayer()->alreadyRolled()) {
            throw new \Exception("You haven't rolled yet, roll first.");
        }

        if (count($kept) == 0 ) {
            throw new \Exception("You must keep at least one die from your roll.");
        }

        $roll = $this->getCurrentPlayer()->getRoll();
        $diff = $this->getKeepersNotInRoll($kept, $roll);
        if ( $diff !== array() ) {
            throw new \Exception("The kept choices ".join(', ', $diff)." are invalid.");
        }

        $this->getCurrentPlayer()->addKept($kept);

        $this->getCurrentPlayer()->clearRoll();

        if (count($this->getCurrentPlayer()->getKept()) == 6) {
            $score = $this->getCurrentPlayer()->calculateScoreFromKept();
            $this->getCurrentPlayer()->setScore($score);
        }
    }

    public function getPlayersRankedByScore()
    {
        if (!$this->hasStarted()) {
            throw new \Exception("The game hasn't started yet.");
        }

        foreach ($this->players as $player) {
            if (!$player->turnIsComplete()) {
                throw new \Exception("You can't calculate a winner until all the players finish their turns.");
            }
        }

        $sorted_players = $this->players;
        usort(
            $sorted_players,
            function ($a, $b) {
                return -1 * strnatcasecmp($a->getScore(), $b->getScore());
            }
        );

        return $sorted_players;
    }

    public function isTied()
    {
        $players = $this->getPlayersRankedByScore();
        return (count($players) > 1 && ($players[0]->getScore() == $players[1]->getScore()));
    }

    protected function getKeepersNotInRoll(array $kept, array $roll)
    {
        $diff = array();
        foreach ($kept as $keeper) {
            if (!in_array($keeper, $roll)) {
                $diff[] = $keeper;
            } else {
                $index = array_search($keeper, $roll);
                unset($roll[$index]);
            }
        }

        return $diff;
    }
}
