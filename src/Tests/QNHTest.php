<?php

namespace METAR\Tests;

use METAR\Part\QNH;

class QNHTest extends \PHPUnit_Framework_TestCase {

    public function testQNHInHpa() {

        $QNH = new QNH(1013);
        $this->assertEquals(29.91,$QNH->toInHg());
    }

}
