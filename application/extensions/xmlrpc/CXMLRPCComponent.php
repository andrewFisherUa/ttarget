<?php

include(__DIR__ . '/xmlrpc.inc');


/**
 * Description of CImageComponent
 *
 * @author Administrator
 */
class CXMLRPCComponent extends CApplicationComponent
{
    public function init()
    {
        parent::init();
    }

    public function load($url)
    {
        return new xmlrpc_client($url);
    }
}
?>
