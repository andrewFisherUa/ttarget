<?php
/**
 * Замечания:
 * Важен порядок вызовов в afterSave в модели. Если вызов parent::afterSave будет раньше,
 * чем обращение к измененным атрибутам, измененные атрибуты будут уже стерты.
 * В определении метода findEntity при получении из кэша нужно не забыть вызвать
 * attachBehaviors() и, возможно, afterFind().
 */
class DirtyObjectBehavior extends CActiveRecordBehavior
{
    protected $cleanAttributes = array();

    public function afterFind($event)
    {
        $this->cleanAttributes = $this->getOwner()->getAttributes();
    }

    public function afterSave($event)
    {
        $this->cleanAttributes = $this->getOwner()->getAttributes();
    }

    public function getDirtyAttributes()
    {

        /** @var ActiveRecord $aro */
        $aro = $this->getOwner();

        $diff = array();
        foreach ($aro->getAttributes() as $key => $val) {
            $type = gettype($this->cleanAttributes[$key]);

            if ($type !== 'NULL') {
                settype($val, $type);
            }

            if ($val !== $this->cleanAttributes[$key]) {
                $diff[$key] = $val;
            }
        }

        return $diff;
    }

    public function isDirty()
    {
        return (count($this->getDirtyAttributes()) > 0);
    }

    public function isAttributeDirty($attributeName)
    {
        $attributes = $this->getDirtyAttributes();
        return (isset($attributes[$attributeName]));
    }

    public function getCleanAttribute($attributeName)
    {
        return (isset($this->cleanAttributes[$attributeName]) ? $this->cleanAttributes[$attributeName] : null);
    }

    public function getCleanAttributes()
    {
        return $this->cleanAttributes;
    }

    /**
     * @param ActiveRecord $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
        $this->cleanAttributes = $owner->attributes;
    }

    public function isAttributeChanged($attributeName)
    {
        return $this->getCleanAttribute($attributeName) != $this->getOwner()->$attributeName;
    }

}
