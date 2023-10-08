<?php


namespace App\Console\Commands;


use App\Consts;
use App\Http\Services\SpareService;
use App\Http\Services\WeightSystemService;
use App\Mails\WeighingScaleNotificationMail;
use App\Models\EmailReceiver;
use App\Models\ReturnSpare;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;


class NotificationWeighingSystemCommand extends Command
{
    /** @var string Status low */
    private const STATUS_LOW = 'low';

    /** @var string Status expired */
    private const STATUS_EXPIRED = 'expired';

    /** @var string Status good */
    private const STATUS_GOOD = 'good';

    /** @var string Status unassigned */
    private const STATUS_UNASSIGNED = 'unassigned';

    private const POINT_EXPIRED_DATE = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weighing-system:notification {type=admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification for weighing system';

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
        $type = $this->argument('type');

        print('Start notification weighing system is processing...' . PHP_EOL);

        $messageAlertWeighingSystem = $this->getAlertWeighingSystem();
        if($messageAlertWeighingSystem) {
            array_unshift($messageAlertWeighingSystem, '[low/critical]');
        }

        $messageItemsExpired = $this->getAlertItemsExpired();
        if($messageItemsExpired) {
            array_unshift($messageItemsExpired, '[item expired]');
        }

        $messageItemsReturn = $this->getAlertItemsReturn();
        if($messageItemsReturn) {
            array_unshift($messageItemsReturn, '[item return with condition]');
        }

        $messageItemsOverdue = $this->getAlertItemsOverdue();
        if($messageItemsOverdue) {
            array_unshift($messageItemsOverdue, '[item onload overdue]');
        }

        $listMessageNotification = array_merge($messageAlertWeighingSystem, $messageItemsExpired, $messageItemsReturn, $messageItemsOverdue);

        $receivers = [];
        // Get list email from setting
        if ($type != 'admin') {
            $receivers = EmailReceiver::query()
                ->where('type', Consts::RECEIVER_EMAIL_TYPE_ALERT_WEIGHING_SYSTEM)
                ->get()
                ->pluck('email')
                ->all();
        }
        if (count($listMessageNotification)) {
            Mail::send(new WeighingScaleNotificationMail($listMessageNotification, $receivers));
        }

        print('Finish notification weighing system ...' . PHP_EOL);
    }

    private function getAlertItemsOverdue(): array
    {
        $listMessageNotification = [];
        /** @var SpareService $spareService */
        $spareService = app(SpareService::class);
        $start = Carbon::now()->subDays(30)->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();
        $issuedDate = ['start' => $start, 'end' => $end];
        $itemsOverdue = $spareService->getReportByLoan(
            [
                'no_pagination' => true,
                'issued_date' => json_encode($issuedDate)
            ]
        );

        foreach ($itemsOverdue as $item) {
            if (!$item->fully_returned && $this->isExpiredReturnTime($item)) {
                $issuedDateItem = Carbon::createFromFormat('Y-m-d H:i:s', $item->issued_date)->format('Y-m-d');
//                $listMessageNotification[] = "Item [$item->name] at the location [$item->location] on loan from[$item->issued_date] has been overdue.";
                $listMessageNotification[] = "Item [$item->part_no] on loan from [$issuedDateItem] has been overdue.";
            }
        }

        return $listMessageNotification;
    }

    private function isExpiredReturnTime($item): bool
    {
        $limitHours = 16;
        $issuedDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $item->issued_date);
        $limitDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $item->issued_date)->startOfDay()->addHours(
            $limitHours
        );
        $now = Carbon::now();
        $isSameDay = $now->diffInDays($limitDatetime) == 0;

        if ($issuedDatetime->gt($limitDatetime) && $isSameDay) {
            return false;
        }

        return $now->gt($limitDatetime);
    }

    private function getAlertItemsExpired(): array
    {
        $listMessageNotification = [];
        /** @var SpareService $spareService */
        $spareService = app(SpareService::class);
        $itemsExpired = $spareService->getSparesExpiring(
            [
                'no_pagination' => true,
            ]
        );

        foreach ($itemsExpired as $item) {
            $pointAllDay = $item->point_all_date;
            if ($pointAllDay == self::POINT_EXPIRED_DATE) {
                $listMessageNotification[] = "Item [$item->name] at the location [$item->location] expired in [$item->expiry_date].";
            }
        }

        return $listMessageNotification;
    }

    private function getAlertItemsReturn(): array
    {
        $listMessageNotification = [];
        $start = Carbon::now()->subDays(30)->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();
        $returnData = ['start' => $start, 'end' => $end];
        /** @var SpareService $spareService */
        $spareService = app(SpareService::class);
        $itemsReturn = $spareService->getReportForReturns(
            [
                'no_pagination' => true,
                'returned_date' => json_encode($returnData),
                'send_mail_alert' => 0
            ]
        );

        foreach ($itemsReturn as $item) {
            if ($item->not_use) {
                $returnState = strtoupper($item->return_state);
                $listMessageNotification[] = "Item [$item->spare_name] at the location [$item->location] returned with the condition [$returnState].";
            }
        }

        // Update return_spares is send email alert
        $this->updateSentEmailReturnSpares();

        return $listMessageNotification;
    }

    private function updateSentEmailReturnSpares()
    {
        ReturnSpare::query()
            ->where('send_mail_alert', 0)
            ->update(
                [
                    'send_mail_alert' => 1
                ]
            );
    }

    private function getAlertWeighingSystem(): array
    {
        /** @var WeightSystemService $weightSystemService */
        $weightSystemService = app(WeightSystemService::class);
        $listSites = $weightSystemService->getListSites();
        $listMessageNotification = [];
        $site = $listSites['sites'][0];
        $room = $site['rooms'][0];
        foreach ($room['shelves'] as $shelf) {
            $siteId = Arr::get($room, 'siteId');
            $roomId = Arr::get($room, 'id');
            $shelfId = $shelf['id'];

            $listBins = $weightSystemService->getBinsOfShelf($shelfId);
            foreach ($listBins as $bin) {
                $status = $this->getBinStatus($bin);
                if (in_array($status, [self::STATUS_LOW, self::STATUS_EXPIRED])) {
                    $messageStatus = $status === self::STATUS_LOW ? 'low threshold' : 'critical threshold';
                    $low = (float)Arr::get($bin, 'quantityMinThreshold');
                    $crit = (float)Arr::get($bin, 'quantityCritThreshold');
                    $quantityStatus = $status === self::STATUS_LOW ? $low : $crit;

                    $deviceId = Arr::get($bin, 'deviceId');
                    $location = implode('-', [$siteId, $roomId, $shelfId, $deviceId]);
                    $currentQuantity = Arr::get($bin, 'quantity');
                    $listMessageNotification[] = "Item [$deviceId] at the location [$location] has on-hand [$currentQuantity] which is lower than the $messageStatus [$quantityStatus].";
                }
            }
        }

        return $listMessageNotification;
    }

    /**
     * Get status of bin
     *
     * @param $bin
     * @return string
     */
    private function getBinStatus($bin): string
    {
        $currentQuantity = (float)Arr::get($bin, 'quantity');
        $low = (float)Arr::get($bin, 'quantityMinThreshold');
        $crit = (float)Arr::get($bin, 'quantityCritThreshold');

        if ($currentQuantity <= $low && $currentQuantity > $crit) {
            return self::STATUS_LOW;
        }

        if ($currentQuantity <= $crit) {
            return self::STATUS_EXPIRED;
        }

        if ($currentQuantity > $low) {
            return self::STATUS_GOOD;
        }

        return self::STATUS_UNASSIGNED;
    }
}
