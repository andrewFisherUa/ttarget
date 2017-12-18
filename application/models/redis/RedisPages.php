<?php

/**
 * Класс для работы с "внешними страницами" для таргетинга
 */
class RedisPages extends RedisAbstract
{
    /**
     * hash
     */
    const KEY_PAGE_URLS = 'ttarget:pages:{domain}';

    /**
     * @param string $class
     *
     * @return RedisPages
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    private function _getPageUrlsKey($domain)
    {
        return str_replace('{domain}', $domain, self::KEY_PAGE_URLS);
    }

    private function _buildPath($parts)
    {
        return (isset($parts['path']) ? ltrim($parts['path'], '/') : '')
            . (isset($parts['query']) ? '?'.$parts['query'] : '')
            . (isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

    private function _prepareParts($url)
    {
        $parts = parse_url($url);
        if(!empty($parts['host'])) {
            $parts['host'] = strtolower($parts['host']);
            if (substr($parts['host'], 0, 4) == 'www.') {
                $parts['host'] = substr($parts['host'], 4);
            }
            return $parts;
        }
        return false;
    }

    public function addPage(Pages $page)
    {
        $parts = $this->_prepareParts($page->url);
        if($parts) {
            $this->redis()->hSet(
                $this->_getPageUrlsKey($parts['host']),
                $page->id,
                $this->_buildPath($parts)
            );
        }
    }

    public function delPage(Pages $page)
    {
        $parts = $this->_prepareParts($page->url);
        if($parts) {
            $this->redis()->hDel($this->_getPageUrlsKey($parts['host']), $page->id);
        }
    }

    public function deleteAll()
    {
        $this->redis()->eval("for _,k in ipairs(redis.call('keys','".$this->_getPageUrlsKey('*')."')) "
            ."do redis.call('del',k) end"
        );
    }
}