<?php declare(strict_types=1);


namespace App\Metrics;

use Carbon\Carbon;

class PerformanceMeasurement
{
    /**
     * @var Carbon
     */
    private $date;
    /**
     * @var float
     */
    private $bytesPerSecond;

    /**
     * @param Carbon $date
     * @param float $bytesPerSecond
     */
    public function __construct(Carbon $date, float $bytesPerSecond)
    {
        $this->date = (clone $date)->setTime(0, 0, 0, 0);
        $this->bytesPerSecond = $bytesPerSecond;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getBytesPerSecond(): float
    {
        return $this->bytesPerSecond;
    }
}
