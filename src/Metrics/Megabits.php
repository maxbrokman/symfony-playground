<?php declare(strict_types=1);


namespace App\Metrics;

class Megabits
{
    /**
     * @param float $bytes
     * @return float value converted to megabits assuming 1000 bytes per megabyte etc
     */
    public static function fromBytes(float $bytes): float
    {
        return (8 * $bytes) / 1000000;
    }
}
