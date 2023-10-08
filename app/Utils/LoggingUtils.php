<?php

namespace App\Utils;

use Illuminate\Support\Facades\Schema;
use App\Models\SystemLog;
use App\Jobs\SaveLogDatabase;
use App\Consts;
use DB;

class LoggingUtils
{

    public static function save($exception = null)
    {
        $logs = DB::getQueryLog();
        foreach ($logs as $log) {
            $log = (object) $log;
            if (static::shouldLog($log)) {
                static::createNewLog($log);
            }
        }

        if ($exception) {
            SystemLog::create([
            'type'      => Consts::SYSTEM_TYPE_EXCEPTION,
            'data'    => $exception
        ]);
        }
    }

    private static function createNewLog($log)
    {
        $sql = str_replace(Consts::CHAR_QUESTION_MARK, "'%s'", $log->query);
        $sql = vsprintf($sql, $log->bindings);

        $action = explode(Consts::CHAR_SPAPCE, $sql)[0];

        // Schema::hasTable(SystemLog::getTableName())
        SystemLog::create([
            'type'      => Consts::SYSTEM_TYPE_LOG,
            'action'    => $action,
            'query'     => $sql
        ]);
    }

    private static function shouldLog($log)
    {
        $ignoreKeys = [
            'insert into `jobs`',
            'select * from `jobs`',
            'select * from information_schema.tables',
            'insert into `system_logs`',
            'select * from `system_logs`',
            'insert into `oauth_refresh_tokens',
            'insert into `oauth_access_tokens`',
            'select * from `oauth_clients`',
            'select * from `oauth_access_tokens`',
        ];

        foreach ($ignoreKeys as $key) {
           if (substr($log->query, 0, strlen($key)) === $key || strpos($log->query, 'select') !== false) {
               return false;
           }
        }

        return true;
    }

}
