<?php
class Arr {
    /**
     * Array dereferencing
     *
     * @param $array
     * @param $index
     * @return mixed
     */
    public static function ad($array, $index, $default = null)
    {
        if(!isset($array[$index])){
            if(func_num_args() > 2){
                return $default;
            }
        }
        return $array[$index];
    }
} 