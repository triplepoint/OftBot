# Introduction
OftBot listens on an IRC channel and will manage a quick game of 1-4-24, based on trigger commands.

# The Game
First, a quick intro to 1-4-24 (sometimes called Midnight).

To start, a player roles 6 dice.  The player must set aside at least one die from the roll (but more are allowed), and the rest of the dice are gathered up and the player rolls again.  This continues until all the dice have been set aside, at which point the player's score is determined and the next player takes their turn.

The object of the game is to get a 'one' and a 'four', and the highest score with the remaining four dice.  The best possible role (in any order) is {1,4,6,6,6,6}, which would be a score of 24 (the 'one' and 'four' are not counted toward the score total).  If a player does not get both the 'one' and the 'four' during their turn, then they did not qualify and their score is effectively zero.

In the event that the game ends and two or more players are tied for the highest score, another mandatory game is played with players taking their turns in the same order.

Traditionally, each player bets a dollar that they will win, and the winner collects the betting pot.

# Installing OftBot
Clone a copy of this repository to some place convenient that would have client access to the IRC channel where the game will be hosted.

Install the project's Composer dependencies:

``` bash
cd /wherever/you/cloned/this/repository/OftBot
wget https://getcomposer.org/installer -O composer.phar
./composer.phar install --verbose --dev --prefer-dist -o
```

# Configuration
Copy the example configuration file to a real configuration file:

``` bash
cd /wherever/you/cloned/this/repository/OftBot/configuration
cp configuration-example.php configuration.php
```

The configuration array is more or less self explanatory.

# Use
## Start OftBot

``` bash
cd /wherever/you/cloned/this/repository/OftBot/cli
./oftbot.php
```

## Setting up a New Game
OftBot listens for public messages that start with `@oftbot`.

So for instance, to call the `help` command, you could say in IRC:
`@oftbot help`

## Begin a Game
Once OftBot is started and has successfully logged into your channel, it's listening and waiting for game commands.  The first step is for someone to suggest a game by saying in IRC:
`@oftbot suggest`

OftBot will begin a game and ask for players to join the game.  Players can join the game during this phase by saying in IRC:
`@oftbot join`

Once all the players that are going to join have joined, the player that originally suggested the game can start the game with:
`@oftbot start`

At this point the game is begun and @oftbot will ignore join requests until the game is ended.  Games in progress can be killed by the player that started the game with:
`@oftbot killgame`

Also the player that started the game can can also kick other players from the game (unless it's current their turn)with:
`@oftbot kick <username>`

Players can leave the game at any time except when it's their turn with:
`@oftbot leave`

## Playing the Game
OftBot will randomly decide the play order of the players and will announce in IRC who's turn is next.  The player who's turn it is can roll their die with:
`@oftbot roll`

OftBot will generated random rolls and announce the results.  The player then says which die he would like to keep.  For instance, if the player rolled and OftBot says that the results are 1, 4, 2, 5, 5, 6, then the player could keep the 1, 4 and 6 with
`@oftbot keep 1 4 6` or
`@oftbot keep 146` or
`@oftbot keep 1, 4,6`

OftBot will then roll the remaining die automatically.  This will continue until all the die are kept, at which point OftBot will anounce the player's score and notify the next player that their turn is beginning.

Once all the players have taken their turn, OftBot will announce the results.  At this point, unless the game is a tie and starts again, the game is over and a new game can be suggested.

## Miscellaneous Commands
There're a few more commands OftBot is listening for:
- help   - Prints a short message listing the commands available and a link to further documentation
- status - Will explain what the current state of the game is, if any.
