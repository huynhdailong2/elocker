<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\SettingService;
use App\Http\Services\SpareService;
use App\Consts;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use App\Models\ScheduleWeekly;
use App\Models\ScheduleMonthly;
use Cache;

class PhysicalSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'physical-schedule:run {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Physical Count';

    private $type;
    private $scheduleType;
    private $receivers;
    private $weekly;
    private $monthly;

    const PATTERN = 'Y-m-d H:i';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->type = $this->argument('type');
        logger()->info("==========[{$this->type}] Checking the Schedule.....[has send?]:", [$this->shouldReport()]);

        if (!$this->shouldReport()) {
            return;
        }

        logger()->info('==========Sending.....');

        $spareService = new SpareService;
        switch ($this->type) {
            case Consts::RECEIVER_EMAIL_TYPE_CYCLE_COUNT:
                return $spareService->sendCycleCountReport();
            case Consts::RECEIVER_EMAIL_TYPE_INVENTORY_COUNT:
                return $spareService->sendInventoryCountReport();
            case Consts::RECEIVER_EMAIL_TYPE_ALERT_WEIGHING_SYSTEM:
                return $this->call('weighing-system:notification', [
                    'type' => 'user'
                ]);
            case Consts::RECEIVER_EMAIL_TYPE_MAINTENANCE:
                return $spareService->sendTnxReportNotification();
            default:
                break;
        }
    }

    private function shouldReport()
    {
        $this->fetchSetting();
        if (!$this->scheduleType || empty($this->receivers) || empty($this->weekly) || empty($this->monthly)) {
            return false;
        }

        switch ($this->scheduleType) {
            case Consts::SCHEDULE_REPORT_WEEKLY:
                return $this->shouldWeeklyReport();
            case Consts::SCHEDULE_REPORT_MONTHLY:
                return $this->shouldMonthlyReport();
            default:
                return false;
        }

        return false;
    }

    private function shouldWeeklyReport()
    {
        $now = Carbon::now('UTC');
        foreach ($this->weekly as $item) {
            $date = $this->clientToUtcForWeekly($item->value, $item->time, $item->offset);
            if ($now->format(static::PATTERN) === $date->format(static::PATTERN)) {
                return true;
            }
        }
        return false;
    }

    private function shouldMonthlyReport() {
        $now = now();
        foreach ($this->monthly as $item) {
            $date = $this->clientToUtcForMonthy($item->day, $item->time, $item->offset);
            if ($now->format(static::PATTERN) === $date->format(static::PATTERN)) {
                return true;
            }
        }
        return false;
    }

    private function fetchSetting()
    {
        $setting = (new SettingService())->getScheduleSettingWithCache($this->type);

        $this->scheduleType     = $setting->scheduleType;
        $this->receivers        = $setting->receivers;
        $this->weekly           = $setting->weekly;
        $this->monthly          = $setting->monthly;
    }

    public function clientToUtcForWeekly($dayOfWeek, $time, $timeoffset)
    {
        Carbon::setWeekStartsAt(Carbon::SUNDAY);

        $hours = $timeoffset / 60 * -1;
        $tz = CarbonTimeZone::create($hours);

        return Carbon::now()
            ->setTimezone($tz)
            ->startOfWeek()
            ->addDay($dayOfWeek)
            ->addMinutes($this->time2Minutes($time))
            ->setTimezone(0);
    }

    public function clientToUtcForMonthy($dayOfMonth, $time, $timeoffset)
    {
        $hours = $timeoffset / 60 * -1;
        $tz = CarbonTimeZone::create($hours);

        return Carbon::now()
            ->setTimezone($tz)
            ->startOfMonth()
            ->addDay($dayOfMonth)
            ->addMinutes($this->time2Minutes($time))
            ->setTimezone(0);
    }

    private function time2Minutes($stringTime)
    {
        list($hours, $minutes) = explode(':', $stringTime);

        return $hours * 60 + $minutes;
    }
}

