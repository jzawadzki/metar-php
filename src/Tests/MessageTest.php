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
        $this->assertEquals(10,$metar->getWindSpeed()->toUnit('kt'));
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
        $this->assertEquals(29.90,$metar->getQNH()->toUnit('inHg'));

    }

    public function testEPMO() {
        $metar = new METAR("EPMO 170930Z 03006KT 1200 R08/1400D -DZ BR BKN003 OVC036 12/12 Q1009");
        $this->assertEquals("EPMO",$metar->getLocation());
        $this->assertEquals(17, $metar->getDayOfMonth());
        $this->assertEquals("0930",$metar->getZuluTime());
        $this->assertEquals("030", $metar->getWindDirection());
        $this->assertEquals(6, $metar->getWindSpeed()->toUnit('kt'));
        $this->assertEquals(12, $metar->getTemperature()->toUnit('C'));
        $this->assertEquals(12, $metar->getDewPoint()->toUnit('C'));
        $this->assertEquals(1200, $metar->getVisibility());
        $this->assertEquals(Array(
                Array('type'=>'BKN','level'=>300,'significant'=>''),
                Array('type'=>'OVC','level'=>3600,'significant'=>'')
            ),$metar->getCloudCover());
        $this->assertEquals(Array(0=>'Light Drizzle',1=>'Mist'),$metar->getWeather());
        $this->assertEquals(1009,$metar->getQNH()->toUnit('hPa'));
    }

    public function testUMMS() {
        $metar = new METAR("UMMS 111900Z 00000MPS 0300 R31/0900 FG FEW001 05/04 Q1032 R31/CLRD// NOSIG RMK QBB050");
        $this->assertEquals("UMMS", $metar->getLocation());
        $this->assertEquals(11, $metar->getDayOfMonth());
        $this->assertEquals("1900",$metar->getZuluTime());
        $this->assertEquals("000", $metar->getWindDirection());
        $this->assertEquals(0, $metar->getWindSpeed()->toUnit('m/s'));
        $this->assertEquals(5, $metar->getTemperature()->toUnit('C'));
        $this->assertEquals(4, $metar->getDewPoint()->toUnit('C'));
        $this->assertEquals(300, $metar->getVisibility());
        $this->assertEquals(Array(
            Array('type'=>'FEW','level'=>100,'significant'=>''),
        ),$metar->getCloudCover());
        $this->assertEquals(Array('exact' => 900, 'unit' => "M"), $metar->getRunwayVisualRange("R31"));
        $this->assertEquals(1032,$metar->getQNH()->toUnit('hPa'));
        $this->assertEquals(Array("Fog"), $metar->getWeather());
    }

}
