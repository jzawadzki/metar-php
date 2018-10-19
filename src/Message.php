<?php

namespace METAR;

use METAR\Part\QNH;
use METAR\Part\Temperature;
use METAR\Part\Wind;
use METAR\Unit\Speed;

/**
 * METAR - class for parsing METAR messages
 *
 * Licence: MIT
 * Author: Jerzy Zawadzki
 **/
class Message
{

    protected $code, $location, $time, $day;
    protected $auto = false;
    protected $cloudCover = Array();
    protected $runways = Array();
    protected $weather = Array();
    protected $QNH, $dewPoint, $temperature, $regExpWeather, $windSpeedDirectionVariable, $windDirectionVariable, $visibility;
    /**
     * @var Wind
     */
    protected $wind;
    protected $texts = Array(
        'MI' => 'Shallow',
        'PR' => 'Partial',
        'BC' => 'Low drifting',
        'BL' => 'Blowing',
        'SH' => 'Showers',
        'TS' => 'Thunderstorm',
        'FZ' => 'Freezing',
        'DZ' => 'Drizzle',
        'RA' => 'Rain',
        'SN' => 'Snow',
        'SG' => 'Snow Grains',
        'IC' => 'Ice crystals',
        'PL' => 'Ice pellets',
        'GR' => 'Hail',
        'GS' => 'Small hail',
        'UP' => 'Unknown',
        'BR' => 'Mist',
        'FG' => 'Fog',
        'FU' => 'Smoke',
        'VA' => 'Volcanic ash',
        'DU' => 'Widespread dust',
        'SA' => 'Sand',
        'HZ' => 'Haze',
        'PY' => 'Spray',
        'PO' => 'Well developed dust / sand whirls',
        'SQ' => 'Squalls',
        'FC' => 'Funnel clouds inc tornadoes or waterspouts',
        'SS' => 'Sandstorm',
        'DS' => 'Duststorm'
    );

    public function __construct($code)
    {
        $this->code  = $code;
        $this->readFromCode($code);
    }

    public function getAsText()
    {
        return $this->code;
    }

    protected function readFromCode($code)
    {
        $codes               = implode('|', array_keys($this->texts));
        $this->regExpWeather = '#^(\+|\-|VC)?(' . $codes . ')(' . $codes . ')?$#';

        $pieces = explode(' ', $code);
        $pos    = 0;
        if ($pieces[0] == 'METAR') {
            $pos++;
        }
        $this->wind = new Wind();
        if (strlen($pieces[$pos]) != 4) {
            $pos++;
        } // skip COR and similar
        $this->setLocation($pieces[$pos]);
        $pos++;
        $this->setDayOfMonth($pieces[$pos]{0} . $pieces[$pos]{1});
        $this->setZuluTime(substr($pieces[$pos], 2, 4));
        $c = count($pieces);
        for ($pos++; $pos < $c; $pos++) {
            $piece = $pieces[$pos];
            if ($piece == "RMK") { // we are not interested in remarks
                break;
            }
            $this->checkFormat($piece);
        }
    }

    protected function checkForWindSpeed($code)
    {
        //WEATHER dddssKT or dddssGggKT
        if (!preg_match('#^([0-9]{3})([0-9]{2})(G([0-9]{2}))?(KT|MPS)$#', $code, $matches)) {
            return false;
        }
        $this->wind->setDirection($matches[1]);
        $this->wind->setSpeed($matches[2], $matches[5]);
        if ($matches[3]) {
            $this->wind->setGusts(new Speed($matches[4], $matches[5] == 'KT' ? 'kt' : 'm/s'));
        }
        return true;
    }

    protected function checkForTemperature($code)
    {
        if (!preg_match('#^(M?[0-9]{2,})/(M?[0-9]{2,})$#', $code, $matches)) { //TEMP/DEW TT/DD negative M03
            return false;
        }
        $temp = (float)$matches[1];
        if ($matches[1]{0} == 'M') {
            $temp = ((float)substr($matches[1], 1)) * -1;
        }

        $this->temperature = new Temperature($temp);

        $dew = (float)$matches[2];
        if ($matches[2]{0} == 'M') {
            $dew = ((float)substr($matches[2], 1)) * -1;
        }
        $this->dewPoint = new Temperature($dew);

        return true;
    }

    protected function checkForQNH($code)
    {
        if (!preg_match('#^(A|Q)([0-9]{4})$#', $code, $matches)) { //QNH
            return false;
        }
        $this->QNH = new QNH($matches[1] == 'Q' ? $matches[2] : ($matches[2] / 100), $matches[1] == 'Q' ? 'hPa' : 'inHg');
        return true;
    }

    protected function checkForWindDirection($code)
    {
        if (!preg_match('#^([0-9]{3})V([0-9]{3})$#', $code, $matches)) {
            return false;
        }
        $this->setWindDirectionVariable(Array($matches[1], $matches[2]));
        return true;
    }

    protected function checkForWindSpeedVariable($code)
    {
        if (!preg_match('#^VRB([0-9]{2})KT$#', $code, $matches)) {
            return false;
        }
        $this->setWindSpeedVariable($matches[1]);
        return true;
    }

    protected function checkForVisibility($code)
    {
        if (preg_match('#^([0-9]{4})|(([0-9]{1,4})SM)$#', $code, $matches)) {
            if (isset($matches[3]) && strlen($matches[3]) > 0) {

                $this->setVisibility((float)$matches[3] * 1609.34);
            } else {
                if ($matches[1] == '9999') {
                    $this->setVisibility('> 10000');
                } else {
                    $this->setVisibility($matches[1]);
                }
            }
            return true;
        }

        if (preg_match('#^CAVOK$#', $code, $matches)) {
            $this->setVisibility('> 10000');
            $this->addWeather("CAVOK");
            return true;
        }
        return false;
    }

    protected function checkForCloudCoverage($code)
    {
        if (!preg_match('#^(SKC|CLR|FEW|SCT|BKN|OVC|VV)([0-9]{3})(CB|TCU|CU|CI)?$#', $code, $matches)) {
            return false;
        }
        $this->addCloudCover($matches[1], ((float)$matches[2]) * 100, isset($matches[3]) ? $matches[3] : '');
        return true;

    }

    protected function checkForRVR($code)
    {
        if (!preg_match('#^(R[A-Z0-9]{2,3})/([0-9]{4})(V([0-9]{4}))?(FT)?$#', $code, $matches)) {
            return false;
        }

        $range = array('exact' => (float)$matches[2], 'unit' => isset($matches[5]) ? 'FT' : 'M');
        if (isset($matches[3])) {
            $range = Array(
                'from' => (float)$matches[2],
                'to'   => (float)$matches[4],
                'unit' => isset($matches[5]) ? 'FT' : 'M'
            );
        }
        $this->addRunwayVisualRange($matches[1], $range);
        return true;
    }

    protected function checkForWeather($code)
    {
        if (!preg_match($this->regExpWeather, $code, $matches)) {
            return false;
        }
        $text = Array();
        switch ($matches[1]) {
            case '+':
                $text[] = 'Heavy';
                break;
            case '-':
                $text[] = 'Light';
                break;
            case 'VC':
                $text[] = 'Vicinity';
                break;
            default:
                break;
        }
        if (isset($matches[2])) {
            $text[] = $this->texts[$matches[2]];
        }
        if (isset($matches[3])) {
            $text[] = $this->texts[$matches[3]];
        }
        $this->addWeather(implode(' ', $text));
        return true;
    }

    protected function checkForAutoRemark($code)
    {
        if ($code == 'AUTO') {
            $this->setIsAuto(true);
            return true;
        }
        return false;
    }

    protected function checkFormat($code)
    {

        if ($this->checkForAutoRemark($code)) {
            return;
        }
        if ($this->checkForWindSpeed($code)) {
            return;
        }
        if ($this->checkForTemperature($code)) {
            return;
        }
        if ($this->checkForQNH($code)) {
            return;
        }
        if ($this->checkForWindDirection($code)) {
            return;
        }
        if ($this->checkForWindSpeedVariable($code)) {
            return;
        }
        if ($this->checkForVisibility($code)) {
            return;
        }
        if ($this->checkForCloudCoverage($code)) {
            return;
        }
        if ($this->checkForRVR($code)) {
            return;
        }
        if ($this->checkForWeather($code)) {
            return;
        }
    }

    protected function addWeather($weather)
    {
        $this->weather[] = $weather;
    }

    public function getWeather()
    {
        return $this->weather;
    }

    protected function addRunwayVisualRange($runway, $range)
    {
        $this->runways[$runway] = $range;
    }

    public function getRunwayVisualRange($runway)
    {
        return isset($this->runways[$runway]) ? $this->runways[$runway] : null;
    }

    protected function addCloudCover($type, $level, $significant)
    {
        $this->cloudCover[] = Array('type' => $type, 'level' => $level, 'significant' => $significant);
    }

    public function getCloudCover()
    {
        return $this->cloudCover;
    }

    protected function setVisibility($val)
    {
        $this->visibility = $val;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    protected function setWindSpeedVariable($val)
    {
        $this->windSpeedDirectionVariable = (float)$val;
    }

    public function getWindSpeedVariable()
    {
        return $this->windSpeedDirectionVariable;
    }

    public function getWindDirectionVariable()
    {
        return $this->windDirectionVariable;
    }

    public function setWindDirectionVariable($variable)
    {
        $this->windDirectionVariable = $variable;
    }

    /**
     * @return QNH
     */
    public function getQNH()
    {
        return $this->QNH;
    }

    /**
     * @return Temperature
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * @return Temperature
     */
    public function getDewPoint()
    {
        return $this->dewPoint;
    }

    public function getWindGusts()
    {
        return $this->wind->getGusts();
    }

    public function getWindDirection()
    {
        return $this->wind->getDirection();
    }

    public function getWindSpeed()
    {
        return $this->wind->getSpeed();
    }

    protected function setIsAuto($auto)
    {
        $this->auto = $auto;
    }

    public function isAuto()
    {
        return $this->auto;
    }

    public function getLocation()
    {
        return $this->location;
    }

    protected function setLocation($loc)
    {
        if (strlen($loc) != 4) {
            throw new \Exception('Invalid location');
        }
        $this->location = $loc;
    }

    public function getDayOfMonth()
    {
        return $this->day;
    }

    protected function setDayOfMonth($day)
    {
        if ($day < 1 || $day > 31) {
            throw new \Exception('Invalid day of month');
        }
        $this->day = $day;
    }

    public function getZuluTime()
    {
        return $this->time;
    }

    protected function setZuluTime($time)
    {
        $this->time = $time;
    }

}
