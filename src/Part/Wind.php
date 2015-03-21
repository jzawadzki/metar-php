<?php

namespace METAR\Part;

use METAR\Unit\Speed;

class Wind
{

    /**
     * @var integer Direction of the wind in degrees
     */
    protected $direction = 0;

    /**
     * @var Speed wind speed
     */
    protected $speed;

    /**
     * @var Speed|null wind gusts speed
     */
    protected $gusts;

    public function __construct() {
        $this->gusts = new Speed(0);
        $this->speed = new Speed(0);
    }
    public function setDirection($direction)
    {
        if ($direction < 0 || $direction > 360) {
            throw new \Exception("Invalid wind direction");
        }
        $this->direction = $direction;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setSpeed($speed, $unit)
    {
        $this->speed = new Speed((float)$speed, $unit == 'MPS' ? 'm/s' : 'kt');
    }

    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * @param \METAR\Unit\Speed|null $gusts
     */
    public function setGusts($gusts)
    {
        $this->gusts = $gusts;
    }

    /**
     * @return \METAR\Unit\Speed|null
     */
    public function getGusts()
    {
        return $this->gusts;
    }

} 