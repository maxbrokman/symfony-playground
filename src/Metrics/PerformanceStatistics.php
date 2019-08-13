<?php declare(strict_types=1);


namespace App\Metrics;

use Cake\Chronos\Chronos;

/**
 * Holds a set of performance measurements and calculates statistics about them.
 */
class PerformanceStatistics
{
    /**
     * @var MeasurementRange
     */
    private $range;

    /**
     * @var float
     */
    private $max;

    /**
     * @var float
     */
    private $min;

    /**
     * @var float
     */
    private $average;

    /**
     * @var float
     */
    private $median;

    /**
     * @var array
     */
    private $lowOutlierRanges;

    public function __construct(MeasurementRange $range, float $max, float $min, float $average, float $median, array $lowOutlierRanges)
    {
        $this->range = $range;
        $this->max = $max;
        $this->min = $min;
        $this->average = $average;
        $this->median = $median;
        $this->lowOutlierRanges = $lowOutlierRanges;
    }

    public function getDateRangeStart(): Chronos
    {
        return $this->range->getStart();
    }

    public function getDateRangeEnd(): Chronos
    {
        return $this->range->getEnd();
    }

    /**
     * Get the average (i.e mean) performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getAverage(): float
    {
        return $this->average;
    }

    /**
     * Get the minimum performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMinimum(): float
    {
        return $this->min;
    }

    /**
     * Get the maximum performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMaximum()
    {
        return $this->max;
    }

    /**
     * Get the median performance measurement from the set
     *
     * @return float performance in bytes per second
     */
    public function getMedian()
    {
        return $this->median;
    }

    /**
     * Arrange low outliers into sets over continuous periods for presentation in results
     *
     * @return MeasurementRange[]
     */
    public function getLowOutlierSets(): array
    {
        return $this->lowOutlierRanges;
    }
}
