<?php

class Dice {
    /**
     * @var int $topSideVal
     */
    private $topSideVal;

    /**
     * @return int
     */
    public function getTopSideVal()
    {
        return $this->topSideVal;
    }

    /**
     * @return int
     */
    public function roll()
    {
        $this->topSideVal =  rand(1,6);
        return $this;
    }

    /**
     * @param int $topSideVal
     * @return Dice
     */
    public function setTopSideVal($topSideVal)
    {
        $this->topSideVal = $topSideVal;
        return $this;
    }
}

class Player
{

    /** 
     * @var array $diceInCup
     */
    private $diceInCup = [];

    /** 
     * @var string $name
     */
    private $name;

    /**
     * @var int $position
     */
    private $position;

    /**
     * @var int $point
     */
    private $point;

    /**
     * @return array
     */
    public function getDiceInCup()
    {
        return $this->diceInCup;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Player constructor.
     * @param int $numberOfDice
     */
    public function __construct($numberOfDice, $position, $name = '')
    {
        /* Set point to 0 */
        $this->point = 0;

        /* position 0 is the left most */
        $this->position = $position;

        /* Optional name, example Player A */
        $this->name = $name;

        /* Initialize array of dice */
        for ($i = 0; $i < $numberOfDice; $i++) {
            array_push($this->diceInCup, new Dice());
        }
    }
    
    /**
     * Add point
     * 
     * @var int $point
     */
    public function addPoint($point)
    {
        $this->point += $point;
    }

    /**
     * Get point
     * 
     * @return int
     */
    public function getPoint()
    {
        return $this->point;
    }

    public function play()
    {
        foreach($this->diceInCup as $dice){
            $dice->roll();
        }
    }

    /**
     * @param int $key
     */
    public function removeDice($key)
    {
        unset($this->diceInCup[$key]);
    }

    /**
     * @param Dice $dice
     */
    public function insertDice($dice)
    {
        array_push($this->diceInCup, $dice);
    }
}

class Game
{
    /**
     * @var array $players = []
     */
    private $players = [];

    /**
     * @var int $round
     */
    private $round;

    /**
     * @var int $numberOfPlayer
     */
    private $numberOfPlayer;
    
    /**
     * @var int $numberOfDicePerPlayer
     */
    private $numberOfDicePerPlayer;

    const REMOVED_WHEN_DICE_TOP = 6;
    const MOVE_WHEN_DICE_TOP = 1;

    /**
     * Game constructor.
     */
    public function __construct($numberOfPlayer, $numberOfDicePerPlayer)
    {
        $this->round = 0;
        $this->numberOfPlayer = $numberOfPlayer;
        $this->numberOfDicePerPlayer = $numberOfDicePerPlayer;

        /* The game contains players and each player have dices */
        for ($i = 0; $i < $this->numberOfPlayer; $i++) {
            $this->players[$i] = new Player($this->numberOfDicePerPlayer, $i, chr(65 + $i));
        }
    }

    /**
     * Display round.
     * 
     * @return $this
     */
    private function displayRound()
    {
        echo "<strong>Giliran {$this->round}</strong><br/>\r\n";
        return $this;
    }

    /**
     * Show top side dice
     *
     * @param string $title
     * @return $this
     */
    private function displayTopSideDice($title = 'Lempar Dadu')
    {
        echo "<span>{$title}:</span><br/>";
        foreach ($this->players as $player) {
            echo "Pemain #{$player->getName()}: ";
            $diceTopSide = '';

            foreach ($player->getDiceInCup() as $dice) {
                $diceTopSide .= $dice->getTopSideVal() . ", ";
            }

            // Remove last comma and echo
            echo rtrim($diceTopSide, ',') . "<br/>\r\n";
        }

        echo "<br/>\r\n";
        return $this;
    }

    /**
     * @param Player $player
     * @return $this
     */
    public function displayWinner($player)
    {
        echo "<h1>Pemenang</h1>\r\n";
        echo "Pemain {$player->getName()}<br>\r\n";
        return $this;
    }

    /**
     * Start the game
     */
    public function start()
    {
        echo "Pemain = {$this->numberOfPlayer}, Dadu = {$this->numberOfDicePerPlayer}<br/><br/>\r\n";
        // Loop until found the winner
        while (true) {
            $this->round++;
            $diceCarryForward = [];

            foreach ($this->players as $player) {
                $player->play();
            }

            /* Display before moved/removed */
            $this->displayRound()->displayTopSideDice();

            /* Check player the top side */
            foreach ($this->players as $index => $player) {
                $tempDiceArray = [];

                foreach ($player->getDiceInCup() as $diceIndex => $dice) {
                    /* Check for any occurrence of 6 */
                    if ($dice->getTopSideVal() == self::REMOVED_WHEN_DICE_TOP) {
                        $player->addPoint(1);
                        $player->removeDice($diceIndex);
                    }

                    /* Check for occurrence of 1 */
                    if ($dice->getTopSideVal() == self::MOVE_WHEN_DICE_TOP) {
                        /**
                         * Determine player position
                         * Max player is right most side.
                         * So move the dice to left most side.
                         */
                        if ($player->getPosition() == ($this->numberOfPlayer - 1)) {
                            $this->players[0]->insertDice($dice);
                            $player->removeDice($diceIndex);
                        } else {
                            array_push($tempDiceArray, $dice);
                            $player->removeDice($diceIndex);
                        }
                    }
                }

                $diceCarryForward[$index + 1] = $tempDiceArray;

                if (array_key_exists($index, $diceCarryForward) && count($diceCarryForward[$index]) > 0) {
                    // Insert the dice
                    foreach ($diceCarryForward[$index] as $dice) {
                        $player->insertDice($dice);
                    }

                    // Reset
                    $diceCarryForward = [];
                }
            }

            /* Display after moved/removed */
            $this->displayTopSideDice("Setelah Evaluasi");

            /* Set number player who have dice. */
            $playerHasDice = $this->numberOfPlayer;

            foreach ($this->players as $player) {
                if (count($player->getDiceInCup()) <= 0) {
                    $playerHasDice--;
                }
            }

            /* Check if player has dice only one */
            if ($playerHasDice == 1) {
                $this->displayWinner($this->getWinner());
                /* Exit the loop */
                break;
            }
        }
    }

    /**
     * Get winner
     *
     * @return Player
     */
    private function getWinner()
    {
        $winner = null;
        $highscore = 0;
        foreach ($this->players as $player) {
            if ($player->getPoint() > $highscore) {
                $highscore = $player->getPoint();
                $winner = $player;
            }
        }

        return $winner;
    }
}

$game = new Game(3, 4);

$game->start();
