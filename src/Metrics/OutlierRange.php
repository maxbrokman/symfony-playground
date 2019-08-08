<?php declare(strict_types=1);


namespace App\Metrics;


use Cake\Chronos\Chronos;

class OutlierRange
{
    /**
     * @var Chronos
     */
    private $start;
    /**
     * @var Chronos
     */
    private $end;
    /**
     * @var array
     */
    private $measurements;

    /**
     * OutlierRange constructor.
     * @param Chronos $start
     * @param Chronos $end
     * @param PerformanceMeasurement[] $measurements
     */
    public function __construct(Chronos $start, Chronos $end, array $measurements)
    {
        $this->start = $start;
        $this->end = $end;
        $this->measurements = $measurements;
    }

    /**
     * @return Chronos
     */
    public function getStart(): Chronos
    {
        return $this->start;
    }

    /**
     * @return Chronos
     */
    public function getEnd(): Chronos
    {
        return $this->end;
    }

    /**
     * @return array
     */
    public function getMeasurements(): array
    {
        return $this->measurements;
    }
}
