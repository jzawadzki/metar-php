<?php

namespace METAR\Tests;

use METAR\Message as METAR;

class MessageTest extends \PHPUnit_Framework_TestCase {

    public function testEmptyCloudCoverSignificant() {
        $metar=new METAR("KMIA 251453Z 04004KT 10SM FEW035 BKN250 32/22 A3014 RMK AO2 SLP205 TCU DSNT SE T03170222 50006");
        $this->assertEquals("KMIA 251453Z 04004KT 10SM FEW035 BKN250 32/22 A3014 RMK AO2 SLP205 TCU DSNT SE T03170222 50006",$metar->getAsText());
        $cc=$metar->getCloudCover();
        $this->assertEquals(Array(
            Array('type'=>'FEW','level'=>3500,'significant'=>''),
            Array('type'=>'BKN','level'=>25000,'significant'=>'')),
         $cc);
    }

    public function testEPKK() {
        $metar = new METAR("EPKK 160030Z 06010KT 8000 BKN060 04/03 Q1034");
        $this->assertEquals('EPKK',$metar->getLocation());
        $this->assertEquals(16,$metar->getDayOfMonth());
        $this->assertEquals('0030',$metar->getZuluTime());
        $this->assertEquals(60,$metar->getWindDirection());
        $this->assertEquals(10,$metar->getWindSpeed());
        $this->assertEquals(Array(
            Array('type'=>'BKN','level'=>6000,'significant'=>'')
        ),$metar->getCloudCover());
        $this->assertEquals(4,$metar->getTemperature()->toUnit('C'));
        $this->assertEquals(3,$metar->getDewPoint()->toUnit('C'));
        $this->assertEquals(8000,$metar->getVisibility());
        $this->assertEquals("1034",(string)$metar->getQNH());
        $this->assertEquals("1034",(string)$metar->getQNH()->toHPa());
    }

    public function testKJFK() {
        $metar = new METAR("KJFK 152351Z 33016G33KT 10SM OVC044 05/M04 A2990 RMK AO2 PK WND 30034/2256 SLP125 T00501044 10072 20050 51038");
        $this->assertEquals('KJFK',$metar->getLocation());
        $this->assertEquals(15, $metar->getDayOfMonth());
        $this->assertEquals(2351,$metar->getZuluTime());
        $this->assertEquals(330, $metar->getWindDirection());
        $this->assertEquals(5, $metar->getTemperature()->toUnit('C'));
        $this->assertEquals(-4, $metar->getDewPoint()->toUnit('C'));
    }

}
