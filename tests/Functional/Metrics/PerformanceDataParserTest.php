<?php declare(strict_types=1);


namespace App\Tests\Functional\Metrics;

use App\Metrics\AverageCalculator;
use App\Metrics\LowOutlierIdentifier;
use App\Metrics\MaximumCalculator;
use App\Metrics\MedianCalculator;
use App\Metrics\MinimumCalculator;
use App\Util\Megabits;
use App\Metrics\PerformanceDataParser;
use App\Metrics\PerformanceStatistics;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PerformanceDataParserTest extends TestCase
{
    /**
     * @var PerformanceDataParser
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new PerformanceDataParser(
            new MaximumCalculator(),
            new MinimumCalculator(),
            new AverageCalculator(),
            new MedianCalculator(),
            new LowOutlierIdentifier()
        );
    }

    public function testParse()
    {
        $filename = \realpath(__DIR__ . "/../../../resources/fixtures/1.json");
        $set = $this->parser->parse($filename);

        $this->assertInstanceOf(PerformanceStatistics::class, $set);
        $this->assertEquals(
            Chronos::createFromDate(2018, 1, 29)->format("Y-m-d"),
            $set->getDateRangeStart()->format("Y-m-d")
        );
        $this->assertEquals(
            Chronos::createFromDate(2018, 2, 27)->format("Y-m-d"),
            $set->getDateRangeEnd()->format("Y-m-d")
        );

        // This tests PerformanceSet more than anything for current implementation
        $this->assertEquals(102.7, round(Megabits::fromBytes($set->getAverage()), 1));
        $this->assertEquals(101.25, round(Megabits::fromBytes($set->getMinimum()), 2));
        $this->assertEquals(104.08, round(Megabits::fromBytes($set->getMaximum()), 2));
        $this->assertEquals(102.93, round(Megabits::fromBytes($set->getMedian()), 2));
    }

    public function testParseWithOutliers()
    {
        $filename = \realpath(__DIR__ . "/../../../resources/fixtures/2.json");
        $set = $this->parser->parse($filename);

        $this->assertInstanceOf(PerformanceStatistics::class, $set);
        $this->assertEquals(
            Chronos::createFromDate(2018, 1, 29)->format("Y-m-d"),
            $set->getDateRangeStart()->format("Y-m-d")
        );
        $this->assertEquals(
            Chronos::createFromDate(2018, 2, 27)->format("Y-m-d"),
            $set->getDateRangeEnd()->format("Y-m-d")
        );

        $this->assertEquals(95.5, round(Megabits::fromBytes($set->getAverage()), 1));
        $this->assertEquals(27.63, round(Megabits::fromBytes($set->getMinimum()), 2));
        $this->assertEquals(104.08, round(Megabits::fromBytes($set->getMaximum()), 2));
        $this->assertEquals(102.91, round(Megabits::fromBytes($set->getMedian()), 2));

        $this->assertCount(1, $set->getLowOutlierSets());
        $this->assertCount(3, $set->getLowOutlierSets()[0]->getMeasurements());
        $this->assertEquals("2018-02-05", $set->getLowOutlierSets()[0]->getStart()->format("Y-m-d"));
        $this->assertEquals("2018-02-07", $set->getLowOutlierSets()[0]->getEnd()->format("Y-m-d"));
    }
}
