# var_dump bot connector
Simple connector for Telegram debug bot: [@var_dump_bot](https://telegram.me/var_dump_bot)


Bot supports all types of PHP variables, which can be serialized to JSON. See more information on the [official webpage](https://debug.tbot.me/).

---
Currently we have only PHP library.

NodeJS connector in roadmap (*if you want to help with development, create pull request*).

### Getting started with PHP library
- Download the [latest version](https://github.com/riartem/var_dump_bot/releases/latest)
- Unpack and place to your project with other classes
- Place secret.txt into folder where library located
- (optional) if your project don't have autoload, don't forget to require. Example: `require_once "./lib/VarDumpBot.php";`

### Error and exception handlers
You can use that bot library as handler for errors and exceptions. To enable that feature, include library in your project as described above and register handlers (or one):

- set_error_handler(array('VarDumpBot', 'error_handler'), E_ALL);
- set_exception_handler(array('VarDumpBot', 'exception_handler'));

Also you can put an exception handler to try/catch blocks. Example:
```
try {
    // your_code_here
} catch(Exception $e){
    VarDumpBot::exception_handler($e);
}
```

### Sending info directly
Of course, you may not use that library for sending commands to bot. 
You may send directly POST requests to our API endpoint: `https://debug.tbot.me/ping` with fields:

- `key` — secret phrase of your project
- `data` — json serialized variable
- `trace` *(not necessarily)* — json serialized array of call stack info ['file', 'line', 'class', 'method']
 
(see [connector PHP class](https://github.com/riartem/var_dump_bot/blob/master/VarDumpBot.php) source code for get an example)
