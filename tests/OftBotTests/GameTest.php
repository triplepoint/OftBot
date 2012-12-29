<?php

namespace OftBotTests;

use OftBot\Game;
use OftBot\Player;

class GameTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPlayersInitialPlayer()
    {
        $game = new Game('creating_player_name');

        $players = $game->getPlayers();

        $test_player = new Player('creating_player_name');

        $this->assertEquals(array($test_player), $players);

        $this->assertEquals($test_player, $game->getGameOwner());
    }

    public function testAddPlayers()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $test_players = array(
            new Player('creating_player_name'),
            new Player('new_player_one'),
            new Player('new_player_two')
        );

        $players = $game->getPlayers();

        $this->assertEquals($test_players, $players);
    }

    public function testPlayerExistsPositive()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $player_exists = $game->playerExists('new_player_one');

        $this->assertTrue($player_exists);
    }

    public function testPlayerExistsNegative()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $player_exists = $game->playerExists('nonexistent_player');

        $this->assertFalse($player_exists);
    }

    /**
     * @expectedException \Exception
     */
    public function testAddPlayersWontAddAPlayerTwice()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');

        $game->addPlayer('new_player_one');
    }

    /**
     * @expectedException \Exception
     */
    public function testAddPlayersWontAddPlayerAfterGameStarts()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');

        $game->start();

        $game->addPlayer('new_player_two');
    }

    public function testShufflePlayers()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->shufflePlayers();

        $test_players = array(
            new Player('creating_player_name'),
            new Player('new_player_one'),
            new Player('new_player_two')
        );

        $players = $game->getPlayers();

        foreach ($test_players as $player) {
            $this->assertContains($player, $players, '', false, false);
        }
    }

    public function testRemovePlayer()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->RemovePlayer('new_player_two');

        $test_players = array(
            new Player('creating_player_name'),
            new Player('new_player_one')
        );

        $players = $game->getPlayers();

        $this->assertEquals($test_players, $players);
    }

    /**
     * @expectedException \Exception
     */
    public function testRemovePlayerThatDoesntExist()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->RemovePlayer('nonexistent_player');
    }

    /**
     * @expectedException \Exception
     */
    public function testRemovePlayerWontRemoveGameOwner()
    {
        $game = new Game('creating_player_name');

        $game->RemovePlayer('creating_player_name');
    }

    public function testGetCurrentPlayer()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->start();

        $current_player = $game->getCurrentPlayer();

        $this->assertContains($current_player->getName(), array('creating_player_name', 'new_player_one', 'new_player_two'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetCurrentPlayerFailsIfGameHasntStarted()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->getCurrentPlayer();
    }

    public function testSetCurrentPlayerToNextPlayer()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->start();

        $game->setCurrentPlayerToNextPlayer();
        $game->setCurrentPlayerToNextPlayer();

        $players = $game->getPlayers();
        $last_player = array_pop($players);  // Because start() randomizes the players, this is the only way to know what the last player is

        $current_player = $game->getCurrentPlayer();

        $this->assertSame($last_player->getName(), $current_player->getName());
    }

    public function testCurrentPlayerIsLastPlayer()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->start();

        $this->assertFalse($game->currentPlayerIsLastPlayer());

        $game->setCurrentPlayerToNextPlayer();

        $this->assertFalse($game->currentPlayerIsLastPlayer());

        $game->setCurrentPlayerToNextPlayer();

        $this->assertTrue($game->currentPlayerIsLastPlayer());
    }

    public function testStart()
    {
        $game = new Game('creating_player_name');

        $this->assertFalse($game->hasStarted());

        $game->start();

        $this->assertTrue($game->hasStarted());
        $this->assertNotEmpty($game->getCurrentPlayer());
    }

    /**
     * @expectedException \Exception
     */
    public function testCantRollIfGameHasntStarted()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->roll();
    }

    public function testFirstRoll()
    {
        $game = new Game('creating_player_name');

        $game->start();

        $game->roll();

        $owner = $game->getGameOwner();

        $this->assertCount(6, $owner->getRoll());
    }

    /**
     * @expectedException \Exception
     */
    public function testCantKeepIfGameHasntStarted()
    {
        $game = new Game('creating_player_name');

        $game->addPlayer('new_player_one');
        $game->addPlayer('new_player_two');

        $game->keep();
    }

    /**
     * @expectedException \Exception
     */
    public function testCantKeepIfPlayerHasntRolled()
    {
        $game = new Game('creating_player_name');

        $game->start();

        $game->keep();
    }

    /**
     * @expectedException \Exception
     * @dataProvider invalidKept
     */
    public function testCantKeepInvalidDice($kept)
    {
        $game = new Game('creating_player_name');

        $game->start();

        $game->roll();

        $roll = $game->getCurrentPlayer()->setRoll(array(1,1,2,3,4,5));

        $game->keep($kept);
    }

    public function invalidKept()
    {
        return array(
            array(array(1,1,1)),
            array(array(6))
        );
    }

    public function testCanKeepValidDice()
    {
        $game = new Game('creating_player_name');

        $game->start();

        $game->roll();

        $roll = $game->getCurrentPlayer()->setRoll(array(1,1,2,3,4,5));

        $game->keep(array(1,1,2,3,4,5));
    }

    /**
     * @covers \OftBot\Game::roll
     * @covers \OftBot\Game::keep
     */
    public function testFullTurn()
    {
        $game = new Game('creating_player_name');

        $game->start();

        $player = $game->getCurrentPlayer();

        // First roll
        $game->roll();
        $roll = $player->getRoll();

        $this->assertCount(6, $roll);

        $game->keep(array($roll[0], $roll[1])); // Keep the first two die rolled

        $kept = $player->getKept();

        $this->assertCount(2, $kept);

        $this->assertFalse($player->turnIsComplete());

        $this->assertSame(null, $player->getScore());

        // Second roll
        $game->roll();
        $roll = $player->getRoll();

        $this->assertCount(4, $roll);

        $game->keep(array($roll[0])); // Keep the first die rolled

        $kept = $player->getKept();

        $this->assertCount(3, $kept);

        $this->assertFalse($player->turnIsComplete());

        $this->assertSame(null, $player->getScore());

        // third roll
        $game->roll();
        $roll = $player->getRoll();

        $this->assertCount(3, $roll);

        $game->keep(array($roll[0], $roll[1], $roll[2])); // Keep all 3 die rolled

        $kept = $player->getKept();

        $this->assertCount(6, $kept);

        $this->assertTrue($player->turnIsComplete());

        $this->assertNotSame(null, $player->getScore());
    }

}
