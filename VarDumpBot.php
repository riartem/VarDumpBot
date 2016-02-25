<?php

class VarDumpBot
{
    protected static $project_key = '';
    protected static $service_url = 'https://debug.tbot.me/ping';

    public static function log($something_here)
    {
        $result = false;

        $content = @json_encode($something_here);
        if($content === false) return $result;

        if(self::curl($content) !== false){
            $result = true;
        }

        return $result;
    }

    protected static function curl($content)
    {
        if(! function_exists('curl_init')){
            return false;
        }

        $ch = @curl_init(self::$service_url);
        if($ch === false) return false;

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'key'  => self::$project_key,
            'data' => $content
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        if($response === false) return false;
        return true;
    }
}


class VDB {
    public static function log($s_h)
    {
        return VarDumpBot::log($s_h);
    }
}