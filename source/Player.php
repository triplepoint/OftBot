<?php

namespace OftBot;

class Player
{
    /**
     * The player's score if they have not qualified
     */
    const SCORE_DNQ = 0;

    /**
     * The player's name
     *
     * @var string
     */
    protected $name;

    /**
     * The player's current roll
     *
     * @var null|array
     */
    protected $roll;

    /**
     * The player's kept dice so far this game
     *
     * @var null|array
     */
    protected $kept;

    /**
     * The player's computer score once they're
     * done rolling.
     *
     * @var null|string
     */
    protected $score;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRoll()
    {
        return $this->roll;
    }

    public function setRoll(array $roll)
    {
        $this->roll = $roll;
    }

    public function clearRoll()
    {
        $this->roll = null;
    }

    public function getKept()
    {
        return $this->kept ?: [];
    }

    public function addKept(array $kept)
    {
        if (!is_array($this->kept)) {
            $this->kept = [];
        }
        $this->kept = array_merge($this->kept, $kept);
    }

    public function clearKept()
    {
        $this->kept = null;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function setScore($score)
    {
        $this->score = $score;
    }

    public function clearScore()
    {
        $this->score = null;
    }

    /**
     * Has the player already rolled?
     *
     * If so, their next move should be to store some more kept dice.
     *
     * @return boolean whether or not the player has already rolled and needs to keep some dice
     */
    public function alreadyRolled()
    {
        return (boolean) $this->getRoll();
    }

    /**
     *  Has the player rolled all their dice and received a score?
     *
     * @return boolean
     */
    public function turnIsComplete()
    {
        return ! is_null($this->score);
    }

    /**
     * What would the player's score be with their currently kept dice?
     *
     * @return integer the player's score.  Zero denotes DNQ.
     */
    public function calculateScoreFromKept()
    {
        // If the player didn't get both a 1 and a 4, their score is zero
        if (!is_array($this->kept) || array_search(1, $this->kept) === false || array_search(4, $this->kept) === false) {
            $score = self::SCORE_DNQ;
        } else {
            $score = array_sum($this->kept) - 1 - 4;
        }

        return $score;
    }
}
