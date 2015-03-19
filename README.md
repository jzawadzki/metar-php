# metar-php

Lightweight library for parsing METAR message

```
METAR is a format for reporting weather information. A METAR weather report is predominantly used by pilots in fulfillment of a part of a pre-flight weather briefing, and by meteorologists, who use aggregated METAR information to assist in weather forecasting.

From Wikipedia, the free encyclopedia
```

## Installation

```sh
$ composer require "jzawadzki/metar-php"
```
## Usage

```php

$metar = new \METAR\Message("EPKK 160030Z 06010KT 8000 BKN060 04/M03 Q1034");

echo $metar->getLocation(); //EPKK
echo $metar->getVisibility(); //8000
echo $metar->getQNH()->toHPa(); //1034
echo $metar->getQNH()->toInHg(); //30.53
echo $metar->getTemperature()->toUnit('C'); //4
echo $metar->getDewPoint()->toUnit('F'); //26.6

$view = new \METAR\View\Text($metar);
echo $view->render();
/*
METAR: EPKK 160030Z 06010KT 8000 BKN060 04/M03 Q1034

Location: EPKK
Day of month: 16 Hour: 0030Z

Temperature: 4.0C Dew point: -3.0C
QNH: 1034 hPa (30.53 inHg)
*/

```

## License
MIT

## Author

Jerzy Zawadzki 
https://github.com/jzawadzki
