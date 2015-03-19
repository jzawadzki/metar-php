<?php

namespace METAR\part;

class Temperature
{
    protected $celcius;

    public function __construct($celcius, $unit = 'C')
    {
        if ($unit == 'F') {
            $this->value = ($celcius - 32) * 5 / 9;
        } elseif ($unit == 'K') {
            $this->value = $celcius - 273.15;
        } elseif ($unit == 'C') {
            $this->value = (float)$celcius;
        } else {
            throw \Exception("Unit must be one of C,F or K");
        }

    }

    public function toUnit($unit)
    {
        if ($unit == 'F') {
            return ($this->value * 9 / 5) + 32;
        }
        if ($unit == 'K') {
            return $this->value + 273.15;
        }
        if ($unit == 'C') {
            return (float)$this->value;
        }

        throw new \Exception("Unsupported unit");
    }
}