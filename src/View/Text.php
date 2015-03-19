<?php

namespace METAR\View;

use METAR\Message;

class Text
{

    /**
     * @var Message
     */
    protected $message;

    public function __construct(Message $message)
    {

        $this->message = $message;

    }

    public function render()
    {
        $template = <<< TEMPLATE
METAR: %s

Location: %s
Day of month: %s Time: %sZ

Temperature: %.1fC Dew point: %.1fC
QNH: %d hPa (%.2f inHg)
TEMPLATE;
        return sprintf(
            $template,
            $this->message->getAsText(),
            $this->message->getLocation(),
            $this->message->getDayOfMonth(),
            $this->message->getZuluTime(),
            $this->message->getTemperature()->toUnit('C'),
            $this->message->getDewPoint()->toUnit('C'),
            (int)$this->message->getQNH()->toUnit('hPa'),
            $this->message->getQNH()->toUnit('inHg')
        );

    }
}