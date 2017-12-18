<?php


class IDN
{
    public static function decodeUrl($url)
    {
        if(empty($url)) { return $url; }
        $parts = parse_url($url);
        if($parts !== false && isset($parts['host'])) {
            $host = self::decodeHost($parts['host']);
            if ($host !== $parts['host']) {
                $parts['host'] = $host;
                $url = self::buildUrl($parts);
            }
        }
        return rawurldecode($url);
    }

    public static function encodeUrl($url)
    {
        if(empty($url)) { return $url; }
        $parts = parse_url($url);
        if($parts !== false && isset($parts['host'])) {
            $host = self::encodeHost($parts['host']);
            if ($host != $parts['host']) {
                $parts['host'] = $host;
                $url = self::buildURL($parts);
            }
        }
        return self::encodeURI($url);
    }

    public static function decodeHost($host)
    {
        return idn_to_utf8($host);
    }

    public static function encodeHost($host)
    {
        return idn_to_ascii($host);
    }
    
    private static function buildUrl($parts)
    {
        return $parts['scheme'].'://'
            . (isset($parts['user']) ? ($parts['user'] . (isset($parts['pass']) ? ':'.$parts['pass'] : '') . '@') : '')
            . (isset($parts['host']) ? $parts['host'] : '')
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . (isset($parts['path']) ? $parts['path'] : '')
            . (isset($parts['query']) ? '?'.$parts['query'] : '')
            . (isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

    private static function encodeURI($url)
    {
        $unescaped = array(
            '%2D'=>'-','%5F'=>'_','%2E'=>'.','%21'=>'!', '%7E'=>'~',
            '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')',
            '%25'=>'%'
        );
        $reserved = array(
            '%3B'=>';','%2C'=>',','%2F'=>'/','%3F'=>'?','%3A'=>':',
            '%40'=>'@','%26'=>'&','%3D'=>'=','%2B'=>'+','%24'=>'$'
        );
        $score = array(
            '%23'=>'#'
        );
        return strtr(rawurlencode($url), array_merge($reserved,$unescaped,$score));
    }
} 