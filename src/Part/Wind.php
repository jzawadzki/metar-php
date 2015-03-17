<?php

namespace METAR\Part;

class Wind
{

    /**
     * @var integer Direction of the wind in degrees
     */
    protected $direction=0;
    protected $windSpeedKT=0;

    public function setDirection($direction)
    {
        if ($direction < 0 || $direction > 360) {
            throw \Exception("Invalid wind direction");
        }
        $this->direction = $direction;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setSpeed($speed, $unit)
    {
        $speedKT = (float)$speed;
        if ($unit == 'MPS') {
            $speedKT = 0.00031965819613457 * $speedKT;
        }
        $this->windSpeedKT = $speedKT;
    }

    public function getSpeed()
    {
        return $this->windSpeedKT;
    }
} 