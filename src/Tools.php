<?php


namespace Zlf;


class Tools
{
    public static function uuid($prefix = '', $split = '')
    {
        $chars = md5(uniqid(mt_rand(), true));

        $uuid = substr($chars, 0, 8) . $split;
        $uuid .= substr($chars, 8, 4) . $split;
        $uuid .= substr($chars, 12, 4) . $split;
        $uuid .= substr($chars, 16, 4) . $split;
        $uuid .= substr($chars, 20, 12);

        return $prefix . $uuid;
    }

}