<?php declare(strict_types=1);


namespace App\Tests\Unit\Metrics;

use App\Metrics\Megabits;
use PHPUnit\Framework\TestCase;

class MegabitsTest extends TestCase
{
    public function testFromBytes()
    {
        $this->assertEquals(0.000008, Megabits::fromBytes(1.0));
    }
}
