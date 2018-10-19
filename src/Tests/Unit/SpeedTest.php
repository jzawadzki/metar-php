<?php

namespace METAR\Tests\Part;

use METAR\Unit\Speed;
use PHPUnit\Framework\TestCase;

class SpeedTest extends TestCase {

    public function testConvert() {

        $speed = new Speed(1234,'m/s');
        $this->assertEquals(1.234,$speed->toUnit('km/s'));
    }

}
