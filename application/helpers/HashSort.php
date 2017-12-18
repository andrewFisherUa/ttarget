<?php
Class HashSort
{
    public static function sortBy($sort, &$data, $sub = null){
        $direction = SORT_ASC;
        if(($pos=strpos($sort, '.'))!==false){
            if(substr($sort, $pos + 1)=='desc'){
                $direction = SORT_DESC;
            }
            $sort = substr($sort,0, $pos);
        }
        self::_sort($sort, $direction, $data, $sub);
    }

    private static function _sort($sort, $direction, &$data, $sub = null)
    {
        $field = array();
        foreach($data as $k => $c){
            if(!isset($c[$sort])) return;
            if($sub){
                self::_sort($sort, $direction, $data[$k][$sub]);
            }
            $field[$k] = $c[$sort];
        }
        array_multisort($field, $direction, $data);
    }
}