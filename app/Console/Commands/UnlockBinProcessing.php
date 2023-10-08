<?php

namespace App\Console\Commands;

use App\Models\Bin;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UnlockBinProcessing extends Command
{
    /** @var int Time expire to unlock */
    public const EXPIRATION_MINUTES = 30;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unlock-bin-processing:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock bin is processing';

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
        $expireTime = Carbon::now()->subMinutes(self::EXPIRATION_MINUTES)->toDateTimeString();

        print('Start unlock bin is processing...' . PHP_EOL);

        Bin::query()->where('is_processing', 1)
            ->where('process_time', '<', $expireTime)
            ->update(
                [
                    'is_processing' => 0,
                    'process_time' => null,
                    'process_by' => null,
                ]
            );

        print('Finish unlock bin is processing...' . PHP_EOL);
    }
}
