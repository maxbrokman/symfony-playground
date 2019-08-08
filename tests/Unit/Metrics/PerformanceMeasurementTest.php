<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\PerformanceMeasurement;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PerformanceMeasurementTest extends TestCase
{
    public function testBreaksDateReference()
    {
        $date = Chronos::now();
        $expected = clone $date;

        $measurement = new PerformanceMeasurement($date, 0.0);
        $date->addDay();

        $this->assertEquals($expected->format('Y-m-d'), $measurement->getDate()->format('Y-m-d'));
    }

    public function testIgnoresTimeInformation()
    {
        // Must set time juuust in case our test runs at midnight
        $date = Chronos::now()->setTime(1, 1, 1);
        $expected = (clone $date)->setTime(0, 0, 0, 0);

        $measurement = new PerformanceMeasurement($date, 0.0);

        $this->assertEquals($expected, $measurement->getDate());
    }
}
