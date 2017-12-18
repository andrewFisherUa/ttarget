<?php


class IdsBehavior extends CActiveRecordBehavior
{
    public $attributes;
    private $_data = array();
    private $_clean = array();

    private function _setIds($attr, $value){
        if(empty($value)){
            $value = array();
        }elseif( !is_array($value) ){
            $value = explode(',', $value);
        }

        if(!isset($this->_clean[$attr])){
            $this->_clean[$attr] = $this->_getIds($attr);
        }

        $this->_data[$attr] = $value;
    }

    private function _getIds($attr)
    {
        $ownerAttr = $this->attributes[$attr];

        if(!isset($this->_data[$attr])){
            $this->_data[$attr] = array();
            if(!empty($this->getOwner()->$ownerAttr)){
                foreach ($this->getOwner()->$ownerAttr as $model) {
                    array_push($this->_data[$attr], $model->id);
                }
            }
        }
        return $this->_data[$attr];
    }

    public function __isset($name)
    {
        if(isset($this->attributes[$name])){
            return true;
        }
        return false;
    }

    public function __get($name)
    {
        if(isset($this->attributes[$name])){
            return $this->_getIds($name);
        }
    }

    public function __set($name, $value)
    {
        if(isset($this->attributes[$name])) {
            $this->_setIds($name, $value);
        }
    }

    /**
     * @param ActiveRecord $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $newAttrs = array();
        foreach($this->attributes as $attr){
            $newAttrs[$attr . 'Ids'] = $attr;
        }
        $this->attributes = $newAttrs;
    }

    public function beforeSave($event)
    {
        foreach($this->attributes as $attr => $ownerAttr) {
            if($this->isAttributeDirty($attr)) {
                $this->getOwner()->$ownerAttr = $this->_data[$attr];
            }
        }

        return parent::beforeSave($event);
    }

    public function isAttributeDirty($attr)
    {
        if(isset($this->_clean[$attr]) && $this->_clean[$attr] != $this->_data[$attr]){
            return true;
        }
        return false;
    }
}