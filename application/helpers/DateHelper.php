<?php
class DateHelper
{
    public static function getRusMonth($month, $rp = true)
    {
        if ($month > 12 || $month < 1) {
            return false;
        }
        $aMonthRP = array(
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря'
        );
        $aMonths = array(
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июлй',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь'
        );
        
        return $rp ? $aMonthRP[$month - 1] : $aMonths[$month - 1];
    }

    public static function getRusDate($date)
    {
        $time = strtotime($date);
        return date('d ' . self::getRusMonth(date('m', $time)) . ' Y', $time);
    }

    public static function getGrathDate($time)
    {
        return date('d ' . self::getRusMonth(date('m', $time)), $time);
    }

    public static function parseDate($dateFrom, $dateTo, $minDate = null, $maxDate = null)
    {
        $dateFrom = strtotime($dateFrom);
        $dateTo = strtotime($dateTo);
        if(is_string($minDate)){
            $minDate = strtotime($minDate);
        }
        if(is_string($maxDate)){
            $maxDate = strtotime($maxDate);
        }

        if($minDate && $dateFrom < $minDate){
            $dateFrom = $minDate;
        }elseif($maxDate && $dateFrom > $maxDate){
            $dateFrom = $maxDate;
        }
        if($maxDate && $dateTo > $maxDate){
            $dateTo = $maxDate;
        }
        if($dateTo < $dateFrom){
            $dateTo = $dateFrom;
        }
        return array(date('Y-m-d', $dateFrom), date('Y-m-d', $dateTo));
    }
}