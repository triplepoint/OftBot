# Introduction
This bot listens on an IRC channel and will manage a quick game of 1-4-24, based on trigger commands.

# The Game
First, a quick intro to 1-4-24.

To start, a player roles 6 dice.  The player must set aside at least one die from the roll (but more are allowed), and the rest of the dice are gathered up and the player rolls again.  This continues until all the dice have been set aside, at which point the player's score is determined and the next player takes their turn.

The object of the game is to get a 'one' and a 'four', and the highest score with the remaining four dice.  The best possible role (in any order) is {1,4,6,6,6,6}, which would be a score of 24 (the 'one' and 'four' are not counted toward the score total).  If a player does not get both the 'one' and the 'four' during their turn, then they did not qualify and their score is effectively zero.

In the event that the game ends and two or more players are tied for the highest score, another mandatory game is played with players taking their turns in the same order.

Traditionally, each player bets a dollar that they will win, and the winner collects the betting pot.

# Installing the Bot
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

TODO - Explain how to edit the configuration file

# Use
Start the bot:

``` bash
cd /wherever/you/cloned/this/repository/OftBot/cli
php oftbot.php
```

TODO - More explanation about how to interact with the bot
