<?php

namespace METAR\Part;

class QNH
{

    protected $value;

    public function __construct($value, $unit = 'hPa')
    {

        if ($unit == 'hPa') {
            $this->value = (int)$value;
        }
        elseif ($unit == 'inHg') {
            $this->value = $this->translateInHgToHPa((float)$value);
        }
        else {
            throw new \Exception('Unknown unit for QNH (only hPa or inHg)');
        }
    }

    protected function translateInHgToHPa($val)
    {
        return round($val * 33.86389,2);
    }

    protected function translateHPaToInHg($val)
    {
        return round($val * 0.029529980164712, 2);
    }

    public function toUnit($unit)
    {
        if ($unit == 'hPa')
            return $this->value;
        if ($unit == 'inHg')
            return $this->translateHPaToInHg($this->value);
        throw new \Exception('Unknown unit for QNH (only hPa or inHg)');
    }

    public function toHPa()
    {
        return $this->toUnit('hPa');
    }

    public function toInHg()
    {
        return $this->toUnit('inHg');
    }

    public function __toString()
    {
        return (string)$this->value;
    }

} 