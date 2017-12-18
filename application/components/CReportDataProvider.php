<?php

class CReportDataProvider extends CArrayDataProvider{
    private $_total = array();

    public function __construct($rawData, $defaultOrder = false)
    {
        if(isset($rawData['total']) && isset($rawData['rows'])) {
            $this->_total = $rawData['total'];
            $rawData = $rawData['rows'];
        }

        parent::__construct($rawData, array(
            'sort' => array(
                'attributes' => isset($rawData[0]) ? array_keys($rawData[0]) : array(),
                'defaultOrder' => $defaultOrder,
            ),
            'pagination' => false,
            'keyField' => false,
        ));
    }

    public function total($key)
    {
        return $this->itemCount > 0 && isset($this->_total[$key]) ? $this->_total[$key] : null;
    }
}