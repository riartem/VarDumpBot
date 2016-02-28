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
    protected static $timeout = 2000;
    protected static $debug = true;

    protected static $trace;
    protected static $debug_file_name = 'VarDumpBot.txt';

    /**
     * Log to Telegram any number of variables
     *
     * @param   mixed   $var,...    variable to debug
     * @return bool
     */
    public static function log()
    {
        self::init();

        if (func_num_args() === 0){
            return self::self_error('nothing to log');
        }

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

        if(is_object($something_here)){
            $something_here = self::as_array($something_here);
        }


        $content = @json_encode($something_here);
        if($content === false) return self::self_error('empty token');

        if(function_exists('debug_backtrace')){
            $trace = debug_backtrace();
            $info = array();
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
        } else {
            self::self_error('backtrace unavailable');
        }


        if(self::curl($content) !== false){
            $result = true;
        }


        return $result;
    }

    /**
     * Error handler for VarDumpBot
     * set it with: set_error_handler(array('VarDumpBot', 'error_handler'), E_ALL);
     */
    public static function error_handler()
    {
        self::init();

        if (func_num_args() === 0)
            return;

        $variables = func_get_args();


        $info = array();
        $info['line'] = $variables[3];
        $info['file'] = $variables[2];
        $info['type'] = 'error';

        $content = @json_encode(strip_tags($variables[1]));

        self::$trace = $info;

        self::curl($content);
    }

    /**
     * Handle an Exception and send it to Telegram
     * set_exception_handler(array('VarDumpBot', 'exception_handler'));
     * or put in any try/catch block:
     * try { your_code_here} catch(Exception $e){ VarDumpBot::exception_handler($e) }
     * @param Exception $e
     */
    public static function exception_handler(Exception $e)
    {
        self::init();

        $info = array();
        $info['line'] = $e->getLine();
        $info['file'] = $e->getFile();
        $info['type'] = 'exception';

        $content = @json_encode($e->getMessage());

        self::curl($content);
    }


    /**
     * Send info to a server with curl
     * @param $content — JSON
     * @return bool
     */
    protected static function curl($content)
    {
        if(! function_exists('curl_init')){
            return self::self_error('curl_init not found');
        }
        $ch = @curl_init(self::$service_url);
        if($ch === false) return self::self_error('curl init error');
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
     * @param $input_object
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

    /**
     * prepare debug and token
     */
    protected static function init()
    {
        if(self::$debug){
            $log_file = __DIR__.'/'.self::$debug_file_name;
            if(! file_exists($log_file)){
                $result_of_write = @file_put_contents($log_file, " ");
                if($result_of_write === false){
                    @chmod($log_file, 0777);
                }

                $result_of_write = @file_put_contents($log_file, " ");
                if($result_of_write === false){
                    self::$debug = false;
                }
            }
        }


        if(self::$project_key == '' and file_exists(__DIR__.'/secret.txt')){
            self::$project_key = trim(@file_get_contents(__DIR__.'/secret.txt'));
        }
        if(self::$project_key == '') self::self_error('empty token');
    }

    /**
     * Put the VarDumpBot's error into debug file (if debug enabled) and return false
     * @param $message
     * @return bool
     */
    protected static function self_error($message)
    {
        if(self::$debug){
            file_put_contents(__DIR__.'/'.self::$debug_file_name, $message."\n", FILE_APPEND);
        }
        return false;
    }
}
