<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\PerformanceMeasurement;
use App\Metrics\MeasurementRange;
use Cake\Chronos\Chronos;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class MeasurementRangeTest extends TestCase
{
    public function testRefuseEmpty()
    {
        $this->expectException(InvalidArgumentException::class);
        new MeasurementRange([]);
    }

    public function testDateRangeStart()
    {
        $range = new MeasurementRange([
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 3), 50.0),
        ]);

        $this->assertEquals(Chronos::createFromDate(2019, 1, 1)->setTime(0, 0, 0), $range->getStart());
    }

    public function testDateRangeEnd()
    {
        $range = new MeasurementRange([
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 50.0),
            new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 3), 50.0),
        ]);

        $this->assertEquals(Chronos::createFromDate(2019, 1, 3)->setTime(0, 0, 0), $range->getEnd());
    }
}
