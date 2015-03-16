# metar-php

Lightweight library for parsing METAR message

```
METAR is a format for reporting weather information. A METAR weather report is predominantly used by pilots in fulfillment of a part of a pre-flight weather briefing, and by meteorologists, who use aggregated METAR information to assist in weather forecasting.

From Wikipedia, the free encyclopedia
```

## Installation

```sh
$ composer require "jzawadzki/metar-php"```
## Usage

```php

$metar = new \METAR\Message("EPKK 160030Z 06010KT 8000 BKN060 04/03 Q1034");

echo $metar->getLocation(); //EPKK
echo $metar->getVisibility(); //8000
echo $metar->getQHN()->toHPa(); //1034
echo $metar->getQHN()->toInHg(); //29.91

```

## License
MIT

## Author

Jerzy Zawadzki 
https://github.com/jzawadzki
