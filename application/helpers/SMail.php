<?php
class SMail {
    static public function sendMail($to,$subject,$view, $data, $attachments = array())
    {
        set_error_handler(array(__CLASS__, '_errorHandler'), E_ERROR);
        try {
            $message = new YiiMailMessage;
            $message->setSubject($subject);
            $message->view = $view;
            $message->setBody($data, 'text/html');
            $message->addTo($to);
            $message->from = Yii::app()->params['adminEmail'];
            foreach($attachments as $attachment){
                $message->attach($attachment);
            }
            Yii::app()->mail->send($message);
        }catch (Exception $e){
            Yii::log('Cant send email. Subject: '.$subject.'. Error: '.$e->__toString(), CLogger::LEVEL_ERROR);
        }
        restore_error_handler();
    }

    static private function _errorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new Exception('Error: '.$errstr);
        return true;
    }
} 