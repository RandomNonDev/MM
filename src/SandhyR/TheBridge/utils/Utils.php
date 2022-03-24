<?php

namespace SandhyR\TheBridge\utils;

use pocketmine\math\Vector3;
use pocketmine\color\Color;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class Utils{

    /**
     * @param Vector3 $vector
     * @return string
     */
    public static function vectorToString(Vector3 $vector): string{
        return $vector->getX() . ":" . $vector->getY() . ":" . $vector->getZ();
    }

    /**
     * @param string $delimeter
     * @param string $string
     * @return Vector3
     */
    public static function stringToVector(string $delimeter, ?string $string): ?Vector3
    {
        if($string !== null) {
            $split = explode($delimeter, $string);
            return new Vector3($split[0], $split[1], $split[2]);
        }
        return null;
    }

    /**
     * @param int $int
     * @return string
     */
    public static function intToString(int $int) : string
    {
        $mins = floor($int / 60);
        $seconds = floor($int % 60);
        return (($mins < 10 ? "0" : "") . $mins . ":" . ($seconds < 10 ? "0" : "") . $seconds);
    }
    

    /**
     * @param Position $position
     * @return string
     */
    public static function PositionToString(Position $position): string{
        return $position->getX() . ":" . $position->getY() . ":" . $position->getZ() . ":" . $position->getWorld()->getDisplayName();
    }

    /**
     * @param string|null $string
     * @return Position|null
     */
    public static function stringToPosition(?string $string): ?Position{
        if($string !== null) {
            $split = explode(":", $string);
            return new Position($split[0], $split[1], $split[2], Server::getInstance()->getWorldManager()->getWorldByName($split[3]));
        }
        return null;
    }
}
