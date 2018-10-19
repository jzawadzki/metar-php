<?php

namespace METAR\Tests\Part;

use METAR\Part\QNH;
use PHPUnit\Framework\TestCase;

class QNHTest extends TestCase {

    public function testQNHInHpa() {

        $QNH = new QNH(1013);
        $this->assertEquals(29.91,$QNH->toInHg());
    }

}
