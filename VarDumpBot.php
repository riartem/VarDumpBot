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
    protected static $timeout = 1000;

    protected static $trace;

    public static function log()
    {
        if (func_num_args() === 0)
            return 'nothing to log';

        $variables = func_get_args();
        if(count($variables) == 1){
            $something_here = $variables[0];
        } else {
            $something_here = array();
            foreach ($variables as $variable) {
                $something_here[] = $variable;
            }
        }

        $result = false;
        if(self::$project_key == '' and file_exists(__DIR__.'/secret.txt')){
            self::$project_key = trim(@file_get_contents(__DIR__.'/secret.txt'));
        }
        if(self::$project_key == '') return 'empty project key';

        if(is_object($something_here)){
            $something_here = self::as_array($something_here);
        }


        $content = @json_encode($something_here);
        if($content === false) return 'error encoding content';

        if(function_exists('debug_backtrace')){
            $trace = debug_backtrace();
            $info = "";
            if(isset($trace[0]) and isset($trace[0]['file']) and isset($trace[0]['line'])){
                $tr = $trace[0];
                $info['line'] = $tr['line'];
                $info['file'] = $tr['file'];
            }
            if(isset($trace[1]) and isset($trace[1]['class'])){
                $tr = $trace[1];
                $info['class'] = $tr['class'];
            }
            if(isset($trace[1])and isset($trace[1]['function'])){
                $tr = $trace[1];
                $info['method'] = $tr['function'];
            }
            self::$trace = $info;
        }


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
            'key'   => self::$project_key,
            'data'  => $content,
            'trace' => json_encode(self::$trace)
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$timeout);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_exec($ch);
        curl_close($ch);
        return true;
    }

    /**
     * Returns the values of this object as an array with methods list
     * @return array
     */
    protected static function as_array($input_object)
    {
        if(is_array($input_object)) return $input_object;
        $object = array();


        foreach ($input_object as $column => $value)
        {
            if(is_object($value)){
                $object[$column] = self::as_array($value);
            } else {
                $object[$column] = $value;
            }
        }

        $class_name = get_class($input_object);
        $methods = get_class_methods($class_name);
        foreach ($methods as $method) {
            $object[$method.'()'] = null;
        }

        return $object;
    }
}
