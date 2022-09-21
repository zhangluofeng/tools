<?php

namespace CommonTools;

class Tools
{

    /**
     * 小时的开始时间戳
     * @param null $time
     * @return false|int
     */
    public static function beginOfHour($time = null)
    {
        if (!$time) {
            $time = time();
        }
        return strtotime(date('Y-m-d H:00:00', $time));
    }

    /**
     * 小时的结束时间戳
     * @param null $time
     * @return false|int
     */
    public static function endOfHour($time = null)
    {
        if (!$time) {
            $time = time();
        }
        return strtotime(date('Y-m-d H:59:59', $time));
    }

    /**
     * 一天的开始时间戳
     * @param null $time
     * @return false|int
     */
    public static function beginOfDay($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        return strtotime(date('Y-m-d', $time));
    }

    /**
     * 一天的结束时间戳
     * @param null $time
     * @return false|int
     */
    public static function endOfDay($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        return strtotime(date('Y-m-d 23:59:59', $time));
    }

    /**
     * 一个月的开始时间戳
     * @param null $time
     * @return false|int
     */
    public static function beginOfMonth($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        return strtotime(date('Y-m-01', $time));
    }

    /**
     * 一个月的结束时间戳
     * @param null $time
     * @return false|int
     */
    public static function endOfMonth($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        $first_day = date('Y-m-01', $time);
        return strtotime("$first_day +1 month -1 second");
    }

    //获取毫秒时间戳
    public static function millisecond()
    {
        return intval(microtime(true) * 1000);
    }

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

    public static function randomString($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'basic':
                return mt_rand();
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'number':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                return false;
        }

        if (!empty($pool)) {
            return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
        }

        return false;
    }


}