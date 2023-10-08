<?php

namespace App\Http\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\EmailReceiver;
use App\Models\ScheduleWeekly;
use App\Models\ScheduleMonthly;
use App\Consts;
use Cache;
use Artisan;

class SettingService
{
    const SENDER_CACHE_KEY      = 'sender-email-config';
    const SCHEDULE_CACHE_KEY    = 'schedule-config';
    const RANGE_GET_REPORT_TNX  = 'range_get_report_tnx';

    public function getSenderEmail()
    {
        $key = static::SENDER_CACHE_KEY;
        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $keys = [
            Consts::SENDER_EMAIL_KEY,
            Consts::SENDER_PASSWORD_KEY
        ];
        $config = Setting::whereIn('key', $keys)
            ->get()
            ->mapWithKeys(function ($record) {
                return [ $record->key => $record->value ];
            })
            ->toArray();

        Cache::forever($key, $config);

        return $config;
    }

    public function getScheduleSettingWithCache($type)
    {
        $key = static::SCHEDULE_CACHE_KEY;
        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $key = null;
        switch ($type) {
            case Consts::RECEIVER_EMAIL_TYPE_CYCLE_COUNT:
                $key = Consts::SCHEDULE_TYPE_CYCLE_COUNT_KEY;
                break;
            case Consts::RECEIVER_EMAIL_TYPE_INVENTORY_COUNT:
                $key = Consts::SCHEDULE_TYPE_INVENTORY_COUNT_KEY;
                break;
            case Consts::RECEIVER_EMAIL_TYPE_ALERT_WEIGHING_SYSTEM:
                $key = Consts::SCHEDULE_TYPE_ALERT_WEIGHING_SYSTEM_KEY;
                break;
            case Consts::RECEIVER_EMAIL_TYPE_MAINTENANCE:
                $key = Consts::SCHEDULE_TYPE_INVENTORY_COUNT_KEY;
                break;
        }
        $setting = Setting::where('key', $key)->first();
        $scheduleType = $setting ? $setting->value : null;

        $receivers = $this->getEmailReceivers($type);

        $weekly = ScheduleWeekly::where('type', $type)->get();
        $monthly = ScheduleMonthly::where('type', $type)->get();

        $config = (object) [
            'scheduleType'  => $scheduleType,
            'receivers'     => $receivers,
            'weekly'        => $weekly,
            'monthly'       => $monthly
        ];

        Cache::forever($key, $config);

        return $config;
    }

    public function getScheduleSettings($params)
    {
        $type = array_get($params, 'type');

        $keys = [
            Consts::SENDER_EMAIL_KEY,
            Consts::SENDER_PASSWORD_KEY,
            Consts::SCHEDULE_TYPE_CYCLE_COUNT_KEY,
            Consts::SCHEDULE_TYPE_INVENTORY_COUNT_KEY,
            Consts::SCHEDULE_TYPE_ALERT_WEIGHING_SYSTEM_KEY,
            Consts::SCHEDULE_TYPE_MAINTENANCE_KEY,
        ];
        $settings = Setting::whereIn('key', $keys)
            ->get()
            ->mapWithKeys(function ($record) {
                return [ $record->key => $record->value ];
            })
            ->toArray();

        $receiverEmail = $this->getEmailReceivers($type)
            ->join(Consts::CHAR_COMMA);

        $scheduleWeekly = ScheduleWeekly::where('type', $type)->get();
        $scheduleMonthly = ScheduleMonthly::where('type', $type)->get();

        return array_merge($settings, [
            'receiver_email'    => $receiverEmail,
            'weekly'            => $scheduleWeekly,
            'monthly'           => $scheduleMonthly
        ]);
    }

    public function getEmailReceivers($type)
    {
        return EmailReceiver::where('type', $type)
            ->get()
            ->pluck('email');
    }

    public function saveSenderEmail($params)
    {
        $this->saveDataSetting(Consts::SENDER_EMAIL_KEY, array_get($params, 'sender_email'));
        $this->saveDataSetting(Consts::SENDER_PASSWORD_KEY, array_get($params, 'sender_password'));

        $this->restartQueue();

        return true;
    }

    private function saveDataSetting($key, $value)
    {
        $setting = Setting::where('key', $key)->first() ?: new Setting;

        $setting->key = $key;
        $setting->value = $value;
        $setting->save();

        Cache::forget(static::SENDER_CACHE_KEY);

        return $setting;
    }

    public function saveReceiverEmail($params)
    {
//        EmailReceiver::truncate();

        $type       = array_get($params, 'type');
        $strEmail   = array_get($params, 'value');

        EmailReceiver::query()
            ->where('type', $type)
            ->delete();

        collect(explode(Consts::CHAR_COMMA, $strEmail))
            ->filter(function ($email) {
                $validator = Validator::make([ 'email' => trim($email) ], [ 'email' => 'required|email' ]);
                return !$validator->fails();
            })
            ->each(function ($email) use ($type) {
                EmailReceiver::updateOrCreate(
                    [
                        'type'  => $type,
                        'email' => $email,
                    ],
                    [
                        'type'  => $type,
                        'email' => $email,
                    ]
                );
            });

        Cache::forget(static::SCHEDULE_CACHE_KEY);

        return true;
    }

    public function saveSchedule($params)
    {
        $type           = array_get($params, 'type');
        $reportType     = array_get($params, 'report_type');
        $schedule       = array_get($params, 'schedule');

        $this->saveDataSetting($type, $reportType);

        switch ($reportType) {
            case Consts::SCHEDULE_REPORT_WEEKLY:
                $this->saveWeekly($schedule);
                Cache::forget(Consts::WEEKLY_KEY);
                break;
            case Consts::SCHEDULE_REPORT_MONTHLY:
                $this->saveMonthly($schedule);
                Cache::forget(Consts::MONTHLY_KEY);
                break;
        }

        Cache::forget(static::SCHEDULE_CACHE_KEY);
        $this->restartQueue();

        return true;
    }

    private function restartQueue()
    {
        logger()->info('====restartQueue');
        Artisan::call('queue:restart');
    }

    private function saveWeekly($schedule)
    {
        $head = collect($schedule)->first();
        ScheduleWeekly::where('type', $head['type'])->delete();

        foreach ($schedule as $key => $value) {
            $data = [
                'type'      => $value['type'],
                'name'      => $value['name'],
                'value'     => Consts::WEEKLY[$value['name']],
                'time'      => $value['time'],
                'offset'    => $value['offset']
            ];
            ScheduleWeekly::create($data);
        }
    }

    private function saveMonthly($schedule)
    {
        $head = collect($schedule)->first();
        ScheduleMonthly::where('type', $head['type'])->delete();

        foreach ($schedule as $key => $value) {
            $data = [
                'type'      => $value['type'],
                'day'       => $value['day'],
                'time'      => $value['time'],
                'offset'    => $value['offset']
            ];
            ScheduleMonthly::create($data);
        }
    }

    public function getRangeGetReportTnx()
    {
        $setting = Setting::where('key', self::RANGE_GET_REPORT_TNX)->first();
        $now = Carbon::now();
        $start = $now->startOfWeek()->format('Y-m-d 00:00:00');
        $end = $now->endOfWeek()->format('Y-m-d 23:59:59');
        if($setting) {
            if($setting->value == 'week') {
                $start = $now->startOfWeek()->format('Y-m-d 00:00:00');
                $end = $now->endOfWeek()->format('Y-m-d 23:59:59');
            } else {
                $start = $now->startOfMonth()->format('Y-m-d 00:00:00');
                $end = $now->startOfMonth()->format('Y-m-d 23:59:59');
            }
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    public function saveByKey($key, $value)
    {
        $setting = Setting::query()->where('key', $key)->first();
        if(!$setting) {
            $setting = new Setting();
        }

        $setting->fill(
            [
                'key' => $key,
                'value' => $value,
            ]
        )->save();

        return $setting;
    }

    public function getByKey($key)
    {
        return Setting::query()->where('key', $key)->first();
    }
}
