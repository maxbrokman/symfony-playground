<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\AverageCalculator;
use App\Metrics\MeasurementRange;
use App\Metrics\PerformanceMeasurement;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AverageCalculatorTest extends TestCase
{
    public function testCalculate()
    {
        $calculator = new AverageCalculator();
        $range = new MeasurementRange([
            new PerformanceMeasurement(Chronos::now(), 50.0),
            new PerformanceMeasurement(Chronos::now(), 100.0),
            new PerformanceMeasurement(Chronos::now(), 150.0),
        ]);

        $this->assertSame(100.0, $calculator->calculate($range));
    }
}
