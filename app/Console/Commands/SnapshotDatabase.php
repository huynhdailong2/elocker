<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SnapshotDatabase as SnapshotDatabaseJob;

class SnapshotDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Snapshot database';

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
        SnapshotDatabaseJob::dispatch();
    }
}

