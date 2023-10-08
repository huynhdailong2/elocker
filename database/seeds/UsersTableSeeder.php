<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Consts;
use App\Utils;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();

        $this->createUser(1, 'Super Admin', 'super_admin', Consts::USER_ROLE_SUPER_ADMIN);
        $this->createUser(2, 'Storeman', 'storeman', Consts::USER_ROLE_STOREMAN);
        $this->createUser(3, 'Inspector', 'inspector', Consts::USER_ROLE_INSPECTOR);
        $this->createUser(4, 'Admin', 'admin', Consts::USER_ROLE_ADMINISTRATOR);
        $this->createUser(5, 'Admin Support', 'admin_support', Consts::USER_ROLE_ADMIN_SUPPORT);
    }

    private function createUser($id, $name, $loginId, $role, $password = null)
    {
        $password = bcrypt($password ?? '123123');

        DB::table('users')->insert([
            'id'                    => $id,
            'name'                  => $name,
            'login_name'            => $loginId,
            'card_id'               => Utils::generateRandomString(6, '0123456789'),
            'password'              => $password,
            'role'                  => $role,
            'remember_token'        => str_random(10),
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now()
        ]);
    }
}
