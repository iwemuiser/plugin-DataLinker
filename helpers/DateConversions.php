<?php

/**
 * Does some magic with dates and date ranges
 * 
 * @package Omeka\View\Helper
 */
class DateFormatRange{
    
    public $free_text_date;
    public $date_span;
    public $date_start;
    public $date_end;
    public $valid = true;
    public $fixed = false;
    
    function __construct($free_text_date){
        $this->free_text_date = $free_text_date;
        $this->validate();
    }
    
    function validate(){
        $this->valid = true;
    }
}

/**
 * Does some magic with dates and date ranges
 * 
 * @package Omeka\View\Helper
 */
class DateFormatHuman{
    
    public $date_span;
    public $date_start;
    public $date_end;
    public $valid = true;
    public $fixed = false;
    
    //EEUW: eindwaarden
    public $century_quarters = array(
        25 =>   "Eerste kwart",             //25 jaar
        50 =>   "Tweede kwart",
        75 =>   "Derde kwart",
        00 =>   "Vierde kwart");

    public $century_positions = array(
        20 =>   "begin",                    //20 jaar
        60 =>   "midden",
        00 =>   "eind");
        
    public $century_approx    =  50;                              //50 jaar
    
    public $year_quarters = array(
        "1 3" =>    "Eerste kwartaal",
        "4 6" =>    "Tweede kwartaal",
        "8 9" =>    "Derde kwartaal",
        "10 12" =>  "Vierde kwartaal",
        "1 2" =>    "Begin",
        "5 6" =>    "Midden",
        "11 12" =>  "Eind"
        );
    
    /**
     * constructs the class
     */
    function __construct($date_span){
        $this->date_span = $date_span;
        $this->validate();
        if ($this->valid){
            $date_span = explode(' ', $this->date_span, 2);
            $this->date_start = new DateTime($date_span[0]);
            $this->date_end = new DateTime($date_span[1]);
        }
        else{
#            trigger_error("Date span $this->date_span not valid. Trying recovery...", E_USER_NOTICE);
            $this->date_start = $this->recoverDate($date_span) ? new DateTime($this->recoverDate($date_span)) : "Invalid date (range)";
        }
    }
    
    function recoverDate($date_span){
        if (preg_match('/^\d{4}.\\d{2}.\\d{2}/', $date_span, $matches)){
            $this->fixed = true;
            return $matches[0];
        }
        else{
            $this->fixed = false;
            return false;
        }
    }
    
    
    /**
     * returns a human readable formatted date (range)
     * @return string
     */
    function formatHuman(){
        if ($this->fixed) return $this->nlDate(strftime("%A %d %B %Y", strtotime($this->date_start->format('Ymd')))) . " (F)";
        if (!$this->valid) return $this->date_span . " (foutieve datum)";
        else if ($this->is_identical($this->date_start, $this->date_end)){ //only one date
            return $this->nlDate(strftime("%A %d %B %Y", strtotime($this->date_start->format('Ymd'))));
        }
        else if ($this->is_century($this->date_start, $this->date_end)){ //full century
            return ($this->date_end->format('Y')/100)+1 . "e eeuw";
        }
        else if ($this->is_quarterof_century($this->date_start, $this->date_end)){ //quarter century
            $century = floor($this->date_start->format('Y')/100);
            return $this->century_quarters[$this->date_end->format('Y') - $century * 100] . " " . ($century+1) . "e eeuw";
        }
        else if ($this->is_positionin_century($this->date_start, $this->date_end)){ //quarter century
            $century = floor($this->date_start->format('Y')/100);
            return $this->century_positions[$this->date_end->format('Y') - $century * 100] . " " . ($century+1) . "e eeuw";
        }
        else if ($this->is_year($this->date_start, $this->date_end)){ //a year
            return $this->date_end->format('Y');
        }
        else if ($this->is_partof_year_dyn($this->date_start, $this->date_end)){ //part of a year
            return $this->year_quarters[$this->date_start->format('n') . " " . $this->date_end->format('n')] . " " . $this->date_start->format('Y');
        }
        else{
            return "Van " . $this->nlDate(strftime("%A %d %B %Y", strtotime($this->date_start->format('Ymd')))) . " t/m " . $this->nlDate(strftime("%A %d %B %Y", strtotime($this->date_start->format('Ymd'))));
        }
    }
    
    
    /**
     * checks if date_start : [(100(C-1))+1]-01-01 AND date_start : [100C]-12-DD 
     * @return string
     */
    function is_partof_year($date_start, $date_end){
#        print $date_start->format('m-d') . " " . $date_end->format('m-d');
        if (array_key_exists($date_start->format('m-d') . " " . $date_end->format('m-d'), $this->year_quarters)){
            return true;
        }
        else return false;
    }

    /**
     * checks if date_start : [(100(C-1))+1]-01-01 AND date_start : [100C]-12-DD 
     * @return string
     */
    function is_partof_year_dyn($date_start, $date_end){
        foreach($this->year_quarters as $months => $value){
            $values = explode(" ", $months);
            if ((date("Y-m-d", mktime(0, 0, 0, $values[0], 1, $date_start->format('Y'))) == $date_start->format('Y-m-d')) AND
                (date("Y-m-d", mktime(0, 0, 0, $values[1]+1, 0, $date_end->format('Y'))) == $date_end->format('Y-m-d'))){
                return true;
            }
        }
        return false;
    }

    /**
     * checks if date_start : YYYY-01-01 AND date_start : YYYY-12-31 
     * @return string
     */
    function is_year($date_start, $date_end){
        if ((date("Y-m-d", mktime(0, 0, 0, 13, 0, $date_end->format('Y'))) == $date_end->format('Y-m-d')) AND
            (date("Y-m-d", mktime(0, 0, 0, 1, 1, $date_start->format('Y'))) == $date_start->format('Y-m-d'))){
            return true;
        }
        else return false;
    }

    /**
     * checks if date_start : [(100(C-1))+1]-01-01 AND date_start : [100C]-12-DD 
     * @return string
     */
    function is_positionin_century($date_start, $date_end){
        if ($date_end->format('Y') - $date_start->format('Y') == 19){
            return true;
        }
        else return false;
    }

    /**
     * checks if date_start : [(100(C-1))+1]-01-01 AND date_start : [100C]-12-DD 
     * @return string
     */
    function is_quarterof_century($date_start, $date_end){
        if ($date_end->format('Y') - $date_start->format('Y') == 24){
            return true;
        }
        else return false;
    }
    
    /**
     * checks if date_start : [(100(C-1))+1]-01-01 AND date_start : [100C]-12-DD 
     * @return string
     */
    function is_century($date_start, $date_end){
        if ($date_end->format('Y') - $date_start->format('Y') == 99){
            return true;
        }
        else return false;
    }
    
    /**
     * compares the years and returns a verdict
     * @return string
     */
    function is_identical($date_start, $date_end){
        $interval = $date_start->diff($date_end);
        return $interval->format("%a") == 0 ? true : false;
    }
    
    function validate(){
        if (preg_match('/^\d{4}.\\d{2}.\\d{2}\s\d{4}.\\d{2}.\\d{2}/', $this->date_span)){
            $date_span = explode(' ', $this->date_span, 2);
            try{
                new DateTime($date_span[0]);
                new DateTime($date_span[1]);
            }
            catch (Exception $e){
                $this->valid = false;
            }
        }
        else{
            $this->valid = false;
        }
    }
    
    function nlDate($parameters){

        $datum = $parameters;
        
        // Vervang de maand, klein
        $datum = str_replace("january", "januari", $datum);
        $datum = str_replace("february", "februari", $datum);
        $datum = str_replace("march", "maart", $datum);
        $datum = str_replace("april", "april", $datum);
        $datum = str_replace("may", "mei", $datum);
        $datum = str_replace("june", "juni", $datum);
        $datum = str_replace("july", "juli", $datum);
        $datum = str_replace("august", "augustus", $datum);
        $datum = str_replace("september", "september", $datum);
        $datum = str_replace("october", "oktober", $datum);
        $datum = str_replace("november", "november", $datum);
        $datum = str_replace("december", "december", $datum);

        // Vervang de maand, hoofdletters
        $datum = str_replace("January", "Januari", $datum);
        $datum = str_replace("February", "Februari", $datum);
        $datum = str_replace("March", "Maart", $datum);
        $datum = str_replace("April", "April", $datum);
        $datum = str_replace("May", "Mei", $datum);
        $datum = str_replace("June", "Juni", $datum);
        $datum = str_replace("July", "Juli", $datum);
        $datum = str_replace("August", "Augustus", $datum);
        $datum = str_replace("September", "September", $datum);
        $datum = str_replace("October", "Oktober", $datum);
        $datum = str_replace("November", "November", $datum);
        $datum = str_replace("December", "December", $datum);

        // Vervang de maand, kort
        $datum = str_replace("Jan", "Jan", $datum);
        $datum = str_replace("Feb", "Feb", $datum);
        $datum = str_replace("Mar", "Maa", $datum);
        $datum = str_replace("Apr", "Apr", $datum);
        $datum = str_replace("May", "Mei", $datum);
        $datum = str_replace("Jun", "Jun", $datum);
        $datum = str_replace("Jul", "Jul", $datum);
        $datum = str_replace("Aug", "Aug", $datum);
        $datum = str_replace("Sep", "Sep", $datum);
        $datum = str_replace("Oct", "Ok", $datum);
        $datum = str_replace("Nov", "Nov", $datum);
        $datum = str_replace("Dec", "Dec", $datum);

        // Vervang de dag, klein
        $datum = str_replace("monday", "maandag", $datum);
        $datum = str_replace("tuesday", "dinsdag", $datum);
        $datum = str_replace("wednesday", "woensdag", $datum);
        $datum = str_replace("thursday", "donderdag", $datum);
        $datum = str_replace("friday", "vrijdag", $datum);
        $datum = str_replace("saturday", "zaterdag", $datum);
        $datum = str_replace("sunday", "zondag", $datum);

        // Vervang de dag, hoofdletters
        $datum = str_replace("Monday", "Maandag", $datum);
        $datum = str_replace("Tuesday", "Dinsdag", $datum);
        $datum = str_replace("Wednesday", "Woensdag", $datum);
        $datum = str_replace("Thursday", "Donderdag", $datum);
        $datum = str_replace("Friday", "Vrijdag", $datum);
        $datum = str_replace("Saturday", "Zaterdag", $datum);
        $datum = str_replace("Sunday", "Zondag", $datum);

        // Vervang de verkorting van de dag, hoofdletters
        $datum = str_replace("Mon",	 "Maa", $datum);
        $datum = str_replace("Tue", "Din", $datum);
        $datum = str_replace("Wed", "Woe", $datum);
        $datum = str_replace("Thu", "Don", $datum);
        $datum = str_replace("Fri", "Vri", $datum);
        $datum = str_replace("Sat", "Zat", $datum);
        $datum = str_replace("Sun", "Zon", $datum);

        return $datum;
    }
    
}

function subtest($date_span){
    $printable = new DateFormatHuman($date_span);
    print $date_span;
    print "<br>_______________________";
    print $printable->formatHuman();
    print "<br>";
}

function test(){    
    subtest("1982-05-11 1982-05-13");
    subtest("1982-05-11 1982-05-11");
    subtest("1982-05-11 1982-0");
    subtest("1982-05-");
    subtest("1901-01-01 2000-12-31");
    subtest("1901-01-01 1925-12-31");
    subtest("1251-01-01 1275-12-31");
    subtest("1901-01-01 1901-12-31");
    subtest("1901-01-01 1901-03-31");
    subtest("1901-01-01 1920-03-31");
    subtest("1941-01-01 1960-03-31");
    subtest("1901-01-01 1901-02-28");
    subtest("1901-01-01 1901-02-31");
    subtest("blablabla");
    subtest("2001-01-01 20  01-01-01");
}

//test();

?>