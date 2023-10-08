<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateCardIdUsersDeletedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-card-id-users-deleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update card id of users deleted';

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
        print('Start update data...'.PHP_EOL);

        $usersDeleted = User::onlyTrashed()->get();
        foreach ($usersDeleted as $userDeleted) {
            $prefixDelete = '_deleted_' . $userDeleted->deleted_at;
            if(strpos($userDeleted->card_id, '_deleted_') === false) {
                $userDeleted->card_id = $userDeleted->card_id . $prefixDelete;
            }
//            if(strpos($userDeleted->name, '_deleted_') === false) {
//                $userDeleted->name = $userDeleted->name . $prefixDelete;
//            }
            if(strpos($userDeleted->login_name, '_deleted_') === false) {
                $userDeleted->login_name = $userDeleted->login_name . $prefixDelete;
            }

            $userDeleted->save();
        }

        print('Finished update data...'.PHP_EOL);
    }
}

