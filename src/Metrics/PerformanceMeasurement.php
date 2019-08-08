<?php declare(strict_types=1);


namespace App\Metrics;

use Cake\Chronos\Chronos;

class PerformanceMeasurement
{
    /**
     * @var Chronos
     */
    private $date;
    /**
     * @var float
     */
    private $bytesPerSecond;

    /**
     * @param Chronos $date
     * @param float $bytesPerSecond
     */
    public function __construct(Chronos $date, float $bytesPerSecond)
    {
        $this->date = $date->setTime(0, 0, 0, 0);
        $this->bytesPerSecond = $bytesPerSecond;
    }

    /**
     * @return Chronos
     */
    public function getDate(): Chronos
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
