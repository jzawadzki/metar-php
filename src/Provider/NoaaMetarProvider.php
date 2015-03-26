<?php

namespace METAR\Provider;

class NoaaMetarProvider {

    public function get($icao) {
        //@TODO: move to guzzle or at least curl
        $f=@file_get_contents(sprintf("http://weather.noaa.gov/pub/data/observations/metar/stations/%s.TXT",$icao));
        if(!$f)
            return false;
        $lines=explode("\n",$f);
        return isset($lines[1])?trim($lines[1]):false;
    }
} 