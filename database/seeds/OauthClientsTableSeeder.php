<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OauthClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('oauth_clients')->truncate();
        DB::table('oauth_clients')->insert([
            'name'                      => 'Basic Drk Inventory Password Grant Client',
            'secret'                    => env('CLIENT_SECRET'),
            'redirect'                  => 'http://localhost',
            'personal_access_client'    => 0,
            'password_client'           => 1,
            'revoked'                   => 0,
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now()
        ]);
    }
}

