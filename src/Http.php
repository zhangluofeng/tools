<?php

namespace CommonTools;

class Http
{
    static function get($url, $body = [], $headers = [])
    {
        if (!empty($body)) {
            if (strstr($url, '?')) {
                $url = $url . '&' . http_build_query($body);
            } else {
                $url = $url . '?' . http_build_query($body);
            }
        }
        return self::http($url, 'GET', $headers);
    }

    static function post($url, $body, $headers = [])
    {
        return self::http($url, "POST", $body, $headers);
    }

    static function http($url, $method, $postfields = NULL, $headers = array())
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        //curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);

        $response = curl_exec($ci);
        curl_close($ci);

        return $response;
    }

}

?>