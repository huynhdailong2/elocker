<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DangerousTruncate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dangerous-truncate:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all data';

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
        if ($this->confirm('Do you wish to continue?')) {
            print('Truncating clusters, shelfs, bins, spares...'.PHP_EOL);
            DB::table('clusters')->truncate();
            DB::table('shelfs')->truncate();
            DB::table('bins')->truncate();
            DB::table('spares')->truncate();
            DB::table('shelf_spares')->truncate();
            DB::table('bin_configures')->truncate();
            DB::table('user_accessing_spares')->truncate();

            print('Truncating euc...'.PHP_EOL);
            DB::table('euc_lists')->truncate();
            DB::table('euc_boxes')->truncate();
            DB::table('euc_box_spares')->truncate();

            print('Truncating cycle_counts...'.PHP_EOL);
            DB::table('cycle_counts')->truncate();
            DB::table('cycle_count_spares')->truncate();

            print('Truncating Issuing...'.PHP_EOL);
            DB::table('issue_cards')->truncate();

            print('Truncating Replenish...'.PHP_EOL);
            DB::table('replenish_euc_boxes')->truncate();
            DB::table('replenishment_spare_configures')->truncate();
            DB::table('replenishment_spares')->truncate();
            DB::table('replenishments')->truncate();

            print('Truncating Return...'.PHP_EOL);
            DB::table('return_spares')->truncate();

            print('Truncating POL...'.PHP_EOL);
            DB::table('pol_histories')->truncate();
            DB::table('pol_managements')->truncate();

            print('Truncating write_offs...'.PHP_EOL);
            DB::table('write_offs')->truncate();

            print('Truncating taking_transactions...'.PHP_EOL);
            DB::table('taking_transactions')->truncate();
        }
    }
}
