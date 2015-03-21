<?php

namespace METAR\Tests\Part;

use METAR\Part\QNH;
use METAR\Unit\Speed;

class SpeedTest extends \PHPUnit_Framework_TestCase {

    public function testConvert() {

        $speed = new Speed(1234,'m/s');
        $this->assertEquals(1.234,$speed->toUnit('km/s'));
    }

}
