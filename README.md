# Introduction
This bot listens on an IRC channel and will manage a quick game of 1-4-24, based on trigger commands.

# The Game
First, a quick intro to 1-4-24.

To start, a player roles 6 dice.  The player must set aside at least one die from the roll (but more are allowed), and the rest of the dice are gathered up and the player rolls again.  This continues until all the dice have been set aside, at which point the player's score is determined and the next player takes their turn.

The object of the game is to get a 'one' and a 'four', and the highest score with the remaining four dice.  The best possible role (in any order) is {1,4,6,6,6,6}, which would be a score of 24 (the 'one' and 'four' are not counted toward the score total).  If a player does not get both the 'one' and the 'four' during their turn, then they did not qualify and their score is effectively zero.

In the event that the game ends and two or more players are tied for the highest score, another mandatory game is played with players taking their turns in the same order.

Traditionally, each player bets a dollar that they will win, and the winner collects the betting pot.
