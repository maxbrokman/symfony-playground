<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\MeasurementRange;
use App\Metrics\MedianCalculator;
use App\Metrics\PerformanceMeasurement;
use App\Metrics\PerformanceStatistics;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class MedianCalculatorTest extends TestCase
{
    public function testCalculate()
    {
        $calculator = new MedianCalculator();

        // Odd set
        $range = new MeasurementRange([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(100.0, $calculator->calculate($range));

        // Even set
        $range = new MeasurementRange([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
        ]);

        $this->assertSame(125.0, $calculator->calculate($range));

        // Unsorted even set
        $range = new MeasurementRange([
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
        ]);

        $this->assertSame(125.0, $calculator->calculate($range));
    }
}
