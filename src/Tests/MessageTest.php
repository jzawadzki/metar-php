<?php

namespace METAR\Tests;

use METAR\Message as METAR;

class MessageTest extends \PHPUnit_Framework_TestCase {

    public function testEmptyCloudCoverSignificant() {
        $metar=new METAR("KMIA 251453Z 04004KT 10SM FEW035 BKN250 32/22 A3014 RMK AO2 SLP205 TCU DSNT SE T03170222 50006");
        $cc=$metar->getCloudCover();
        $this->assertEquals(Array(
            Array('type'=>'FEW','level'=>3500,'significant'=>''),
            Array('type'=>'BKN','level'=>25000,'significant'=>'')),
         $cc);
    }

}
