<?php

namespace METAR;

use METAR\Part\QNH;
use METAR\part\Temperature;
use METAR\Part\Wind;

/**
 * METAR - class for parsing METAR messages
 *
 * Licence: MIT
 * Author: Jerzy Zawadzki
 **/
class Message
{

    protected $code,$location, $time, $day;
    protected $auto = false;
    protected $cloudCover = Array();
    protected $runways = Array();
    protected $weather = Array();
    protected $QNH;
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
        $this->code = $code;
        $this->readFromCode($code);
    }

    public function getAsText()
    {
        return $this->code;
    }

    protected function readFromCode($code)
    {

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
            if ($piece == "RMK") {
                break;
            } // we are not interested in remarks
            $this->checkFormat($piece);
        }

    }

    public function checkFormat($code)
    {
        $matches = Array();
        if ($code == 'AUTO') {
            $this->setIsAuto(true);
        }
        if (preg_match(
            '#^([0-9]{3})([0-9]{2})(G([0-9]{2}))?(KT|MPS)$#',
            $code,
            $matches
        )
        ) { //WEATHER dddssKT or dddssGggKT
            $this->wind->setDirection($matches[1]);
            $this->wind->setSpeed($matches[2], $matches[5]);
            if ($matches[3]) {
                $this->setWindGusts($matches[4]);
            }
            return;
        }

        if (preg_match('#^(M?[0-9]{2,})/(M?[0-9]{2,})$#', $code, $matches)) { //TEMP/DEW TT/DD negative M03
            $temp = (float)$matches[1];
            if ($matches[1]{0} == 'M') {
                $temp = ((float)substr($matches[1], 1)) * -1;
            }

            $this->setTemperature(new Temperature($temp));

            $dew = (float)$matches[2];
            if ($matches[2]{0} == 'M') {
                $dew = ((float)substr($matches[2], 1)) * -1;
            }
            $this->setDewPoint(new Temperature($dew));
            return;
        }

        if (preg_match('#^(A|Q)([0-9]{4})$#', $code, $matches)) { //QNH
            $this->QNH = new QNH($matches[2], $matches[1] == 'Q' ? 'hPa' : 'inHg');
            return;
        }

        if (preg_match('#^([0-9]{3})V([0-9]{3})$#', $code, $matches)) {
            $this->setWindDirectionVariable(Array($matches[1], $matches[2]));
            return;
        }
        if (preg_match('#^VRB([0-9]{2})KT$#', $code, $matches)) {

            $this->setWindSpeedVariable($matches[1]);
            return;
        }
        if (preg_match('#^([0-9]{4})|(([0-9]{1,4})SM)$#', $code, $matches)) {
            if (isset($matces[3]) && strlen($matches[3]) > 0) {

                $this->setVisibility((float)$matches[3] * 1609.34);
            } else {
                if ($matches[1] == '9999') {
                    $this->setVisibility('> 10000');
                } else {
                    $this->setVisibility($matches[1]);
                }
            }
            return;
        } else {
            if (preg_match('#^CAVOK$#', $code, $matches)) {
                $this->setVisibility('> 10000');
                $this->addWeather("CAVOK");
            }
        }

        if (preg_match('#^(SKC|CLR|FEW|SCT|BKN|OVC|VV)([0-9]{3})(CB|TCU|CU|CI)?$#', $code, $matches)) {
            $this->addCloudCover($matches[1], ((float)$matches[2]) * 100, isset($matches[3]) ? $matches[3] : '');
            return;
        }
        if (preg_match('#^(R[A-Z0-9]{2,3})/([0-9]{4})(V([0-9]{4}))?(FT)?$#', $code, $matches)) {

            $range = array('exact' => (float)$matches[2], 'unit' => $matches[5] ? 'FT' : 'M');
            if (isset($matches[3])) {
                $range = Array(
                    'from' => (float)$matches[2],
                    'to'   => (float)$matches[4],
                    'unit' => $matches[5] ? 'FT' : 'M'
                );
            }
            $this->addRunwayVisualRange($matches[1], $range);
            return;
        }

        if (preg_match(
            '#^(\+|\-|VC)?(' . implode('|', array_keys($this->texts)) . ')(' . implode(
                '|',
                array_keys($this->texts)
            ) . ')?$#',
            $code,
            $matches
        )
        ) {
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

            if ($matches[2]) {
                $text[] = $this->texts[$matches[2]];
            }
            if ($matches[3]) {
                $text[] = $this->texts[$matches[3]];
            }
            $this->addWeather(implode(' ', $text));
            return;
        }
    }

    protected function addWeather($weather)
    {

        $this->weather[] = $weather;
    }

    public function getWeather()
    {
        return $this->weather ? $this->weather : array("CLEAR");
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

    protected function setQNH($val, $unit)
    {
        $this->QNH = new QNH($val, $unit);
    }

    /**
     * @return QNH
     */
    public function getQNH()
    {
        return $this->QNH;
    }

    protected function setTemperature($val)
    {
        $this->temperature = $val;
    }

    /**
     * @return Temperature
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    protected function setDewPoint($val)
    {
        $this->dewPoint = $val;
    }

    /**
     * @return Temperature
     */
    public function getDewPoint()
    {
        return $this->dewPoint;
    }

    protected function setWindGusts($val)
    {
        $this->windGusts = (float)$val;
    }

    public function getWindGusts()
    {
        return $this->windGusts ? $this->windGusts : 0;
    }

    protected function setWindDirection($val)
    {
        $this->windDirection = (float)$val;
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
            throw new Exception('Invalid location');
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
            throw new Exception('Invalid day of month');
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
