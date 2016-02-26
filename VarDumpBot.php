<?php
/**
 * Telegram @var_dump_bot connector
 * https://github.com/riartem/var_dump_bot
 */
class VarDumpBot
{
    protected static $project_key = '';
    protected static $service_url = 'https://debug.tbot.me/ping';
    // Decrease $timeout for speed up sending info. But if messages not delivered means value is too low.
    protected static $timeout = 200;
    public static function log($something_here)
    {
        $result = false;
        if(self::$project_key == '' and file_exists(__DIR__.'/secret.txt')){
            self::$project_key = trim(@file_get_contents(__DIR__.'/secret.txt'));
        }
        if(self::$project_key == '') return 'empty project key';

        $content = @json_encode($something_here);
        if($content === false) return 'error encoding content';
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

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$timeout);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_exec($ch);
        curl_close($ch);
        return true;
    }
}
