<?php


class Crypt {

    /**
     * Возвращает шифрованную ссылку по переданной строке
     *
     * @param $value
     * @param null|string $secret
     *
     * @return bool|string
     */
    public static function encryptUrlComponent($value, $secret = null)
    {
        if(!$value){
            return false;
        }
        if(null === $secret){
            $secret = Yii::app()->params['linkSecret'];
        }

        $iv_size    = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv         = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $cryptText  = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secret, $value, MCRYPT_MODE_ECB, $iv);

        return trim(self::safe_b64encode($cryptText));
    }

    /**
     * Возвращает значение зашифрованной ссылки
     *
     * @param $value
     * @param null|string $secret
     *
     * @return bool|string
     */
    public static function decryptUrlComponent($value, $secret = null)
    {
        if(!$value){
            return false;
        }
        if(null === $secret){
            $secret = Yii::app()->params['linkSecret'];
        }

        $cryptText      = self::safe_b64decode($value);
        $iv_size        = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv             = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decryptText    = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secret, $cryptText, MCRYPT_MODE_ECB, $iv);

        return trim($decryptText);
    }

    public static function safe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    public static function safe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
} 