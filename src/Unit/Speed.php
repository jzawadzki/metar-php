<?php

namespace METAR\Unit;

class Speed
{

    /**
     * @var meters per second
     */
    protected $mps;


    public function __construct($value, $unit = 'kt')
    {
        if ($unit == 'm/s') {
            $this->value = (float)$value;
        } elseif ($unit == 'km/s') {
            $this->value = ((float)$value)*1000;
        } elseif ($unit == 'kt') {
            $this->value = ((float)$value)*0.51444444444;
        } else {
            throw new \Exception("Unit must be one of m/s,km/s,kt");
        }

    }

    public function toUnit($unit)
    {
        if ($unit == 'm/s') {
            return $this->value;
        }
        if ($unit == 'km/s') {
            return $this->value/1000;
        }
        if ($unit == 'kt') {
            return round($this->value*1.9438444924574);
        }

        throw new \Exception("Unsupported unit");
    }
} 