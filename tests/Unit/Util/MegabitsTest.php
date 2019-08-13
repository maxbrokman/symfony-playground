<?php declare(strict_types=1);


namespace App\Tests\Unit\Util;

use App\Util\Megabits;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class MegabitsTest extends TestCase
{
    public function testFromBytes()
    {
        $this->assertEquals(0.000008, Megabits::fromBytes(1.0));
    }
}
