<?php

namespace METAR\Part;

class Wind
{

    /**
     * @var integer Direction of the wind in degrees
     */
    protected $direction;

    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
    public function getDirection() {
        return $this->direction;
    }
} 