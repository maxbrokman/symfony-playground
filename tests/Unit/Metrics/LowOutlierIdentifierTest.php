<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\LowOutlierIdentifier;
use App\Metrics\MeasurementRange;
use App\Metrics\PerformanceMeasurement;
use App\Metrics\PerformanceStatistics;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class LowOutlierIdentifierTest extends TestCase
{
    public function testIdentifyLowOutliers()
    {
        $identifier = new LowOutlierIdentifier();
        $range = new MeasurementRange([
            $outlier = new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 1.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
            new PerformanceMeasurement(Chronos::now(), 1000.0),
        ]);

        $outliers = $identifier->identifyLowOutliers($range);
        $this->assertCount(1, $outliers);
        $this->assertInstanceOf(MeasurementRange::class, $outliers[0]);
        $this->assertEquals("2019-01-01", $outliers[0]->getStart()->format("Y-m-d"));
        $this->assertEquals("2019-01-01", $outliers[0]->getEnd()->format("Y-m-d"));
        $this->assertCount(1, $outliers[0]->getMeasurements());
        $this->assertContains($outlier, $outliers[0]->getMeasurements());
    }

    public function testMultipleOutlierRanges()
    {
        $identifier = new LowOutlierIdentifier();
        $range = new MeasurementRange([
            $outlier = new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 1), 1.0),
            $outlier2 = new PerformanceMeasurement(Chronos::createFromDate(2019, 1, 2), 1.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            new PerformanceMeasurement(Chronos::now(), 200.0),
            $outlier3 = new PerformanceMeasurement(Chronos::createFromDate(2025, 1, 1), 1.0),
        ]);

        $outliers = $identifier->identifyLowOutliers($range);
        $this->assertCount(2, $outliers);

        $this->assertInstanceOf(MeasurementRange::class, $outliers[0]);
        $this->assertEquals("2019-01-01", $outliers[0]->getStart()->format("Y-m-d"));
        $this->assertEquals("2019-01-02", $outliers[0]->getEnd()->format("Y-m-d"));
        $this->assertCount(2, $outliers[0]->getMeasurements());
        $this->assertContains($outlier, $outliers[0]->getMeasurements());
        $this->assertContains($outlier2, $outliers[0]->getMeasurements());

        $this->assertInstanceOf(MeasurementRange::class, $outliers[1]);
        $this->assertEquals("2025-01-01", $outliers[1]->getStart()->format("Y-m-d"));
        $this->assertEquals("2025-01-01", $outliers[1]->getEnd()->format("Y-m-d"));
        $this->assertCount(1, $outliers[1]->getMeasurements());
        $this->assertContains($outlier3, $outliers[1]->getMeasurements());
    }
}
