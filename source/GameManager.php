<?php

namespace OftBot;

use OftBot\Game;
use Philip\IRC\Response;

class GameManager
{
    /**
     * The current game if there is one, or null if there isn't.
     *
     * @var Game
     */
    protected $currentGame;

    /**
     * The Event that led to this call
     *
     * @var \Philip\IRC\Event
     */
    public $event;

    // -- ADMIN TASKS ---

    public function suggest()
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();
            $channel = $this->event->getRequest()->getSource();

            if ($this->currentGame) {
                throw new \Exception('There\'s already a game in progress, wait for it to end before starting a new game.  Or, the player that started the game can kill it with \'@oftbot killgame\'.');
            }

            $this->currentGame = new Game($user);

            $this->event->addResponse(Response::notice($channel, '@' . $this->currentGame->getGameOwner()->getName() . ' suggests a new game of 1, 4, 24!  Who\'s in?  Type \'@oftbot join\' to play!'));

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function killGame()
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();
            $channel = $this->event->getRequest()->getSource();

            if (!$this->currentGame) {
                throw new \Exception('There\'s no game in progress, start a new game with \'@oftbot suggest\'.');
            }

            if ($user != $this->currentGame->getGameOwner()->getName()) {
                throw new \Exception('You can\'t do that because you didn\'t create this game - @' . $this->currentGame->getGameOwner()->getName() . ' did.');
            }

            $this->currentGame = null;

            $this->event->addResponse(Response::notice($channel, "The game has been cancelled by @$user."));

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function kick($kick_user)
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();
            $channel = $this->event->getRequest()->getSource();

            if (!$this->currentGame) {
                throw new \Exception('There\'s no game in progress.');
            }

            if ($user != $this->currentGame->getGameOwner()->getName()) {
                throw new \Exception('You can\'t do that because you didn\'t create this game - @' . $this->currentGame->getGameOwner()->getName() . ' did.');
            }

            $this->currentGame->removePlayer($kick_user);

            $this->event->addResponse(Response::notice($channel, "@$kick_user was kicked from the game by @$user."));

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function start()
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();
            $channel = $this->event->getRequest()->getSource();

            if (!$this->currentGame) {
                throw new \Exception('There\'s no game in progress.');
            }

            if ($user != $this->currentGame->getGameOwner()->getName()) {
                throw new \Exception('You can\'t do that because you didn\'t create this game - @' . $this->currentGame->getGameOwner()->getName() . ' did.');
            }

            $this->currentGame->start();

            $this->event->addResponse(Response::notice($channel, 'Joining has ended, time to play the game.'));
            $this->event->addResponse(Response::notice($channel, 'Play order: ' . join(', ', $this->getCurrentGamePlayerNames())));
            $this->event->addResponse(Response::notice($channel, '@' . $this->currentGame->getCurrentPlayer()->getName() . ', you\'re up first.  Type \'@oftbot roll\' to take your first roll.'));

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }


    // -- GENERAL TASKS --

    public function join()
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();
            $channel = $this->event->getRequest()->getSource();

            if (!$this->currentGame) {
                throw new \Exception('There\'s no game in progress.');
            }

            $this->currentGame->addPlayer($user);

            $this->event->addResponse(Response::notice($channel, "@$user has joined the game."));

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function leave()
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();
            $channel = $this->event->getRequest()->getSource();

            if (!$this->currentGame) {
                throw new \Exception('There\'s no game in progress.');
            }

            $this->currentGame->removePlayer($user);

            $this->event->addResponse(Response::notice($channel, "@$user left the game."));

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function roll()
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();

            if (!$this->currentGame) {
                throw new \Exception('There\'s no game in progress.');
            }

            if ($this->currentGame->getCurrentPlayer()->getName() != $user) {
                throw new \Exception('It\'s not your turn to play - it\'s @' . $this->currentGame->getCurrentPlayer()->getName() . '\'s turn.');
            }

            $this->doRoll();

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    public function keep($kept)
    {
        try {
            $user    = $this->event->getRequest()->getSendingUser();

            if (!$this->currentGame) {
                throw new \Exception('There\'s no game in progress.');
            }

            if ($this->currentGame->getCurrentPlayer()->getName() != $user) {
                throw new \Exception('It\'s not your turn to play - it\'s @' . $this->currentGame->getCurrentPlayer()->getName() . '\'s turn.');
            }

            $kept = preg_replace('/[^0-9]/', '', $kept);
            $kept = str_split($kept, 1);

            $this->doKeep($kept);

        } catch (\Exception $e) {
            $this->event->addResponse(Response::msg($user, $e->getMessage()));
        }
    }

    protected function doRoll()
    {
        $channel = $this->event->getRequest()->getSource();

        $this->currentGame->roll();

        $roll = $this->currentGame->getCurrentPlayer()->getRoll();

        $this->event->addResponse(Response::notice($channel, '@' . $this->currentGame->getCurrentPlayer()->getName() . ' rolled: ' . join(', ', $roll) . '.'));

        if (count($roll) === 1) {
            $this->event->addResponse(Response::notice($channel, 'There\'s only one die; keeping it.'));
            $this->doKeep([$roll[0]]);

        } else {
            $message = '@' . $this->currentGame->getCurrentPlayer()->getName() . ', What would you like to keep?  ';
            if (count($this->currentGame->getCurrentPlayer()->getKept()) === 0) {
                $message .= 'For example, type \'@oftbot keep 666614\'.';

            } else {
                $message .= 'You\'ve already kept ' . join(', ', $this->currentGame->getCurrentPlayer()->getKept()) . ', ';
                if ($this->currentGame->getCurrentPlayer()->calculateScoreFromKept() > 0) {
                    $message .= 'and your score is ' . $this->currentGame->getCurrentPlayer()->calculateScoreFromKept() . '.';
                } else {
                    $message .= 'and you have not yet qualified.';
                }

            }
            $this->event->addResponse(Response::notice($channel, $message));

        }
    }

    protected function doKeep(array $kept)
    {
        $channel = $this->event->getRequest()->getSource();

        $this->currentGame->keep($kept);

        if (!$this->currentGame->getCurrentPlayer()->turnIsComplete()) {
            $this->doRoll();
            return;
        }

        $message = '@' . $this->currentGame->getCurrentPlayer()->getName() . ', that was your last roll.  ';
        if ($this->currentGame->getCurrentPlayer()->getScore() > 0) {
            $message .= 'Your final score was ' . $this->currentGame->getCurrentPlayer()->getScore() . '.';
        } else {
            $message .= 'You did not qualify.';
        }
        $this->event->addResponse(Response::notice($channel, $message));

        if (!$this->currentGame->currentPlayerIsLastPlayer()) {
            $this->currentGame->setCurrentPlayerToNextPlayer();
            $this->event->addResponse(Response::notice($channel, '@' . $this->currentGame->getCurrentPlayer()->getName() . ', it\'s your turn next.  Type \'@oftbot roll\' to take your first roll.'));

        } else {
            $this->finishGame();
        }
    }

    protected function finishGame()
    {
        $channel = $this->event->getRequest()->getSource();

        $players = $this->currentGame->getPlayersRankedByScore();

        $this->event->addResponse(Response::notice($channel, 'And with that, the game is over.'));

        $scores = [];
        foreach ($players as $player) {
             $scores[] = $player->getName() . ' - ' . $player->getScore();
        }
        $message = 'Final Score: ' . join(' ,  ', $scores);
        $this->event->addResponse(Response::notice($channel, $message));

        if (!$this->currentGame->isTied()) {
            $this->event->addResponse(Response::notice($channel, $players[0]->getName() . ' wins.'));
            if ($this->currentGame->getTieCounter() > 0) {
                $this->event->addResponse(Response::notice($channel, 'That was the end of a ' . $this->currentGame->getTieCounter()+1 . ' game tied-game series.'));
            }
            $this->currentGame = null;

        } else {
            $this->event->addResponse(Response::notice($channel, 'It\'s a tied game!'));

            $this->currentGame->continueTie();

            $this->event->addResponse(Response::notice($channel, 'Same play order as before: ' . join(', ', $this->getCurrentGamePlayerNames())));

            $this->event->addResponse(Response::notice($channel, '@' . $this->currentGame->getCurrentPlayer()->getName() . ', you\'re up first.  Type \'@oftbot roll\' to take your first roll.'));
        }
    }

    // -- MISCELLANEOUS --

    public function status()
    {
        $channel = $this->event->getRequest()->getSource();

        if (!$this->currentGame) {
            $this->event->addResponse(Response::notice($channel, 'There is no game active.'));
            return;
        }

        $this->event->addResponse(Response::notice($channel, 'There is a game active, started by ' . $this->currentGame->getGameOwner()->getName() . '.'));

        $player_names = $this->getCurrentGamePlayerNames();

        if (!$this->currentGame->hasStarted()) {
            $this->event->addResponse(Response::notice($channel, 'The game hasn\'t started yet, and we\'re still in the joining phase.'));
            $this->event->addResponse(Response::notice($channel, 'So far, ' . join(', ', $player_names) . ' have joined the game.'));
            return;
        }

        $player = $this->currentGame->getCurrentPlayer();
        $this->event->addResponse(Response::notice($channel, 'The game has started, and we\'re waiting on  ' . $player->getName() . ' to ' . ($player->alreadyRolled() ? 'select dice to keep' : 'roll') . '.'));

        if ($this->currentGame->getTieCounter() > 1) {
            $message = 'This is a continuation of a tied game.  So, far there\'ve been ' . $this->currentGame->getTieCounter() . ' tied games before this one.';
            $this->event->addResponse(Response::notice($channel, $message));

        } else if ($this->currentGame->getTieCounter() > 0) {
            $message = 'This is a continuation of a tied game.  So, far there\'s been ' . $this->currentGame->getTieCounter() . ' tied game before this one.';
            $this->event->addResponse(Response::notice($channel, $message));
        }
    }

    public function help()
    {
        $channel = $this->event->getRequest()->getSource();

        $lines = [
            'This is OftBot - a 1, 4, 24 IRC bot.',
            '  OftBot receives commands of the form \'@oftbot <command>\'.',
            '  OftBot accepts these commands: suggest, killgame, join, leave, kick, start, roll, keep (also available as k), help, and status.',
            '  For more information, see https://github.com/triplepoint/OftBot .'
        ];

        foreach ($lines as $line) {
            $this->event->addResponse(Response::notice($channel, $line));
        }
    }

    protected function getCurrentGamePlayerNames()
    {
        $players = $this->currentGame->getPlayers();
        $names = array_map(
            function ($player) {
                return $player->getName();
            },
            $players
        );

        return $names;
    }
}
