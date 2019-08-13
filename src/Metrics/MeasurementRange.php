<?php declare(strict_types=1);


namespace App\Metrics;

use Cake\Chronos\Chronos;
use InvalidArgumentException;

class MeasurementRange
{
    use WorksWithPerformanceMeasurements;

    /**
     * @var Chronos
     */
    private $start;
    /**
     * @var Chronos
     */
    private $end;

    /**
     * OutlierRange constructor.
     * @param PerformanceMeasurement[] $measurements
     */
    public function __construct(array $measurements)
    {
        if (count($measurements) < 1) {
            throw new InvalidArgumentException("Must provide at least one performance measurement to " . __CLASS__);
        }

        $this->measurements = $measurements;
        $this->init();
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

    /**
     * Initialize class members
     *
     * Calculates the range represented by the performance measurements provided at construction time
     */
    private function init(): void
    {
        $this->start = array_reduce($this->getDimensionsOnly(), function (?Chronos $memo, Chronos $dimension) {
            if (is_null($memo)) {
                return $dimension;
            }

            return $dimension->min($memo);
        }, null);

        $this->end = array_reduce($this->getDimensionsOnly(), function (?Chronos $memo, Chronos $dimension) {
            if (is_null($memo)) {
                return $dimension;
            }

            return $dimension->max($memo);
        }, null);
    }
}
