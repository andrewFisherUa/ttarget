<?php
/**
 * Костыль для работы с лимитами.
 * Кампании, офферы  и тп могут быть уже "отключены до завтра", а переходы (из внешних сетей)
 * или действия (действие можно и через пару дней выполнить) продолжат приходить...
 * Логика лимитов сейчас такая что при привышении лимита мы вызываем задачу синхронизации сейчас и завтра.
 * Если ничего не делать, на завтра может скопиться тонна задач.
 * Да и при каждом переходе/действии дергается вся цепочка синхронизации.
 * Не придумал пока ничего...
 *
 */
class RedisLimit extends RedisAbstract{
    const KEY_LIMIT = 'ttarget:limit';

    /**
     * @param string $class
     *
     * @return RedisLimit
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }


    public function set($obj)
    {
        return $this->redis()->sAdd(
            self::KEY_LIMIT,
            $this->_getName($obj)
        );
    }

    public function del($obj)
    {
        return $this->redis()->sRem(
            self::KEY_LIMIT,
            $this->_getName($obj)
        );
    }

    public function isExists($obj)
    {
        return $this->redis()->sIsMember(
            self::KEY_LIMIT,
            $this->_getName($obj)
        );
    }

    private function _getName($obj)
    {
        return get_class($obj).'_'.$obj->id;
    }
}