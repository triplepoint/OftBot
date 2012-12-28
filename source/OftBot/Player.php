<?php

namespace OftBot;

class Player
{
    protected $name;

    protected $roll;

    protected $keep;

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

    public function getKeep()
    {
        return $this->keep;
    }

    public function setKeep(array $keep)
    {
        $this->keep = $keep;
    }

}
