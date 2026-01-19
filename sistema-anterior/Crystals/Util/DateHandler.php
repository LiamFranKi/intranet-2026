<?php
class DateHandler{
    private $months = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre');
    private $days = array('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');

    // $month number -> 1 for january
    function getMonthName($month = null){
        if(isset($month)){
            return $this->months[$month - 1];
        }

        return $this->months;
    }

    function getDayName($day = null){
        if(isset($day)) return $this->days[$day - 1];
        return $this->days;
    }

    function getMonthNamesAsList($id, $value='', $class = '', $style = ''){
        $c = '<select name="'.$id.'" id="'.$id.'" class="'.$class.'" style="'.$style.'">'."\n";
        foreach($this->months as $key => $month){
            $c .= '<option value="'.($key + 1).'" '.($value == ($key + 1) ? 'selected' : '').'>'.mb_strtoupper($month, 'UTF-8').'</option>'."\n";  
        }
        $c .= '</select>';
        return $c;
    }

    function getCurrentMonth(){
        return date('m');
    }

    function getCurrentYear(){
        return date('Y');
    }

    function getCurrentDay(){
        return date('d');
    }

    function getCurrentDate($format = 'Y-m-d'){
        return date($format);
    }

    function getFirstDayFromThisMonth(){
        return date('Y-m-01');
    }

    function getLastDayFromThisMonth(){
        return date('Y-m-t');
    }

    function addDays($date, $days, $format = 'Y-m-d'){
        $timestamp = strtotime($date);
        $new_date = $timestamp + (24*60*60*$days);
        return date($format, $new_date);
    }
    
    function getDate($format, $timestamp = null, $convert_timestamp = false){
        if(!isset($timestamp)) $timestamp = time();
        if($convert_timestamp) $timestamp = strtotime($timestamp);
        return date($format, $timestamp);
    }
}    

?>
