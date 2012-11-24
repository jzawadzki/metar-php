<?php

class METAR {


    private $location,$time,$day;
    private $auto=false;
    private $cloudCover=Array();
    private $runways=Array();
    private $weather=Array();
    private $texts=Array('MI'=>'Shallow','PR'=>'Partial','BC'=>'Low drifting','BL'=>'Blowing',
        'SH'=>'Showers','TS'=>'Thunderstorm','FZ'=>'Freezing','DZ'=>'Drizzle','RA'=>'Rain','SN'=>'Snow',
        'SG'=>'Snow Grains','IC'=>'Ice crystals','PL'=>'Ice pellets','GR'=>'Hail','GS'=>'Small hail',
        'UP'=>'Unknown','BR'=>'Mist','FG'=>'Fog','FU'=>'Smoke','VA'=>'Volcanic ash','DU'=>'Widespread dust',
        'SA'=>'Sand','HZ'=>'Haze','PY'=>'Spray','PO'=>'Well developed dust / sand whirls','SQ'=>'Squalls',
        'FC'=>'Funnel clouds inc tornadoes or waterspouts','SS'=>'Sandstorm','DS'=>'Duststorm');
    
    public function __construct($code='') {
        
        if($code)
            $this->readFromCode($code);    

    }
    private function readFromCode($code) {

        $pieces = explode(' ',$code);
        $pos=0;
        if($pieces[0]=='METAR') 
            $pos++;
        $this->setLocation($pieces[$pos]);
        $pos++;
        $this->setDayOfMonth($pieces[$pos]{0}.$pieces[$pos]{1});
        $this->setZuluTime(substr($pieces[$pos],2,4));
        $c=count($pieces);
        for($pos++;$pos<$c;$pos++)
            $this->checkFormat($pieces[$pos]);
			
    }

    public function checkFormat($code) {
        $matches=Array();
        if ($code == 'AUTO')
            $this->setIsAuto(true);
        if (preg_match('#^([0-9]{3})([0-9]{2})(G([0-9]{2}))?(KT|MPS)$#', $code, $matches)) { //WEATHER dddssKT or dddssGggKT
            $this->setWindDirection($matches[1]);
            $this->setWindSpeed($matches[2], $matches[5]);
            if ($matches[3])
                $this->setWindGusts($matches[4]);
            return;
        }
        if (preg_match('#^(M?[0-9]{2,})/(M?[0-9]{2,})$#', $code, $matches)) { //TEMP/DEW TT/DD negative M03
             $temp = (int) $matches[1];
            if ($matches[1]{0} == 'M')
                $temp = ((int) substr($matches[1], 1)) * -1;
              
            $this->setTemperature($temp);
            
            $dew = (int) $matches[2];
            if ($matches[2]{0} == 'M')
                $dew = ((int) substr($matches[2], 1)) * -1;
                
            $this->setDewPoint($dew);
            return;
        }
        if (preg_match('#^(A|Q)([0-9]{4})$#', $code, $matches)) { //QNH
            $this->setQNH((int) $matches[2], $matches[1] == 'Q' ? 'hPa' : 'mMHg');
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
        if (preg_match('#^([0-9]{4})$#', $code, $matches)) {
            if ($matches[1] == '9999')
                $this->setVisibility('MAX');
            else
                $this->setVisibility((int) $matches[1]);
            return;
        }
        if (preg_match('#^(SKC|CLR|FEW|SCT|BKN|OVC|VV)([0-9]{3})$#', $code, $matches)) {
            $this->addCloudCover($matches[1], ((int) $matches[2]) * 100);
            return ;
        }
        if (preg_match('#^(R[A-Z0-9]{2,3})/([0-9]{4})(V([0-9]{4}))?(FT)?$#',$code,$matches)) {
            
            $range=array('exact'=>(int)$matches[2],'unit'=>$matches[5]?'FT':'M');
            if(isset($matches[3]))
                $range=Array('from'=>(int)$matches[2],'to'=>(int)$matches[4],'unit'=>$matches[5]?'FT':'M');
            $this->addRunwayVisualRange($matches[1],$range);
            return ;
        }
     
        if(preg_match('#^(\+|\-|VC)?('.implode('|',array_keys($this->texts)).')('.implode('|',array_keys($this->texts)).')?$#',$code,$matches))
        {
            $text=Array();
            switch($matches[1]) {
                case '+':
                    $text[]='Heavy';
                    break;
                case '-':
                    $text[]='Light';
                    break;
                case 'VC':
                    $text[]='Vicinity';
                    break;
                default:
                    
                    break;
                            
            }
            
            if($matches[2])
                $text[]=$this->texts[$matches[2]];
            if($matches[3])
                $text[]=$this->texts[$matches[3]];
            $this->addWeather(implode(' ',$text));
            return;
        }
    }
    public function addWeather($weather) {
        
        $this->weather[]=$weather;
    }
    public function getWeather() {
        return $this->weather;
    }
    public function addRunwayVisualRange($runway, $range) {
        $this->runways[$runway]=$range;
    }
    public function getRunwayVisualRange($runway) {
        return isset($this->runways[$runway])?$this->runways[$runway]:null;
    
    }
    public function addCloudCover($type,$level) {
        
        $this->cloudCover[]=Array('type'=>$type,'level'=>$level);
    }
    public function getCloudCover() {
        return $this->cloudCover;
    
    }
    public function setVisibility($val) {
        
        $this->visibility=$val;
    }
    public function getVisibility() {
        return $this->visibility;
    }
    
    public function setWindSpeedVariable($val) {
        $this->windSpeedDirectionVariable=$val;
        
    }
    public function getWindSpeedVariable() {
        
        return $this->windSpeedDirectionVariable;
    }
    public function setWindDirectionVariable($val) {
        $this->windDirectionVariable=$val;
    
    }
    public function getWindDirectionVariable() {
        return $this->windDirectionVariable;
    }
    public function setQNH($val,$unit) {
        if($unit!='hPa'&&$unit!='inHg')
            throw new Exception('Unknown unit for QNH (only hPa or inHg)');
        if($unit=='inHg')
            $this->QNH=$this->translateInHgToHPa($val);
        $this->QNH=$val;
    }
    private function translateInHgToHPa($val) {
        
        return round($val/100*33.86389);
    }
    public function getQNH() {
        return $this->QNH;
    }
    public function setTemperature($val) {
        $this->temperature=$val;
    }
    public function getTemperature() {
        return $this->temperature;
    }
    public function setDewPoint($val) {
        $this->dewPoint=$val;
    }
    public function getDewPoint() {
        $this->dewPoint;
    }
    public function setWindGusts($val) {
        $this->windGusts=(int)$val;
    }
    public function getWindGusts() {
        return $this->windGusts?$this->windGusts:0;
    }
    public function setWindDirection($val) {
            $this->windDirection=(int)$val;
    }
    public function getWindDirection() {
        return $this->windDirection();
    }
    public function setWindSpeed($speed,$unit) 
    {
        $speedKT=(int)$speed;
        if($unit=='MPS')
            $speedKT= 0.00031965819613457*$speedKT;
        $this->windSpeedKT=$speedKT;
    }
    public function getWindSpeed() {
        return $this->windSpeedKT;
    }

    public function setIsAuto($auto) {
        $this->auto=$auto;
    }
    public function isAuto() {
        return $this->auto;
    }
    public function getLocation() {
        return $this->location;
    }
    public function setLocation($loc) {
        if(strlen($loc)!=4)
            throw new Exception('Invalid location');
        $this->location=$loc;
    }
    public function getDayOfMonth() {
        return $this->day;
    }
    public function setDayOfMonth($day) {
        if($day<1||$day>31)
            throw new Exception('Invalid day of month');
        $this->day=$day;
    }
    public function getZuluTime() {
        return $this->time;
    }
    public function setZuluTime($time) {
        $this->time=$time;
    }

}
