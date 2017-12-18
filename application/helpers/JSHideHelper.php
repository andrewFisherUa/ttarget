<?php
/*******************************************************************
*   file: JSHideHelper.php
*   freated: 28 апр. 2015 г. - 16:12:51
*
*   @author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


class JSHideHelper
{
    const TRUE_SYMBOL = "\t";
    const FALSE_SYMBOL = " ";
    const DELIMITTER = ".";
    
    /**
    *   Encode text
    *   @param string $js
    *   @param string $true 'TRUE' symbol
    *   @param string $false 'FALSE' symbol
    *   @param string $delim Delimitter
    *   @return string Encoded text
    **/
    public static function encode($js, $true = self::TRUE_SYMBOL, $false = self::FALSE_SYMBOL, $delim = self::DELIMITTER)
    {
        $_encoded = null;
        $_code = preg_split("/\\n/",$js);
        
        foreach( $_code as $_k => $_s){
            for($_i = 0; $_i < strlen($_s); $_i++){
                $_c = $_s[$_i];
                $_d = (string)base_convert(self::utf8CharCodeAt($_s, $_i),10,2);
                for($_j = 0; $_j < strlen($_d); $_j++){
                    $_encoded .= (int)$_d[$_j] ? $true : $false;
                }
                $_encoded .= $delim;
            }
        }
        return $_encoded;
    }
    
    /**
    *   Decode text, encoded by JSHideHelper::encode()
    *   @param string $encoded Encoded text
    *   @param string $true 'TRUE' symbol
    *   @param string $delim Delimitter
    *   @return string Decoded text
    **/
    public static function decode( $encoded, $true = self::TRUE_SYMBOL, $delim = self::DELIMITTER )
    {
        $_encoded = explode($delim,$encoded);
        $_decoded = '';
        foreach($_encoded as $i => $_string){
            $_d = '';
            for($_j=0; $_j < strlen($_string); $_j++){
                $_d .= $_string[$_j] == $true ? '1' : '0';
            }
            
            $_chr = base_convert($_d, 2, 10);
            $_decoded .= chr($_chr);
        }
        return $_decoded;
    }
    
    public static function utf8CharCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');
    
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        } else {
            return null;
        }
    }

}



/*******************************************************************
*   encoding: UTF-8
*   tab size: 4
*   end oof file: JSHide.php
**/