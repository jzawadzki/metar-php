<?php

namespace METAR\Tests\View;

use METAR\Message;
use METAR\View\Text;

class TextTest extends \PHPUnit_Framework_TestCase {

    public function testFormat() {

        $metar = new Message("EPKK 160030Z 06010KT 8000 BKN060 04/M03 Q1034");
        $view=new Text($metar);
       $template = <<< TEMPLATE
METAR: EPKK 160030Z 06010KT 8000 BKN060 04/M03 Q1034

Location: EPKK
Day of month: 16 Time: 0030Z

Temperature: 4.0C Dew point: -3.0C

QNH: 1034 hPa (30.53 inHg)

Wind:
Direction: 060
Speed: 10kt

Visibility: 8000
Clouds:
- BKN at 6000ft
TEMPLATE;

        $this->assertEquals(str_replace("\r","",trim($template)),str_replace("\r","",trim($view->render())));

    }

}
