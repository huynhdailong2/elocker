<?php

use Illuminate\Database\Seeder;
use App\Consts;
use Illuminate\Support\Str;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->truncate();

        foreach ($this->getAllPermissions() as $key => $value) {
            DB::table('permissions')->insert(
                array_merge($value, [
                    'code'          => Str::slug($value['name'], '_'),
                    'created_at'    => now(),
                    'updated_at'    => now()
                ])
            );
        }
    }

    private function getAllPermissions()
    {
        return [
            [ 'type' => 'User Management', 'name' => 'Creation of Super Admin' ],
            [ 'type' => 'User Management', 'name' => 'Creation of Administrator ' ],
            [ 'type' => 'User Management', 'name' => 'Creation of Supervisor' ],
            [ 'type' => 'User Management', 'name' => 'Creation of Storeman' ],
            [ 'type' => 'User Management', 'name' => 'Creation of Inspector' ],
            [ 'type' => 'User Management', 'name' => 'Creation of Admin Support' ],
            [ 'type' => 'User Management', 'name' => 'Deletion of Super Admin' ],
            [ 'type' => 'User Management', 'name' => 'Deletion of Administrator' ],
            [ 'type' => 'User Management', 'name' => 'Deletion of Supervisor' ],
            [ 'type' => 'User Management', 'name' => 'Deletion of Storeman' ],
            [ 'type' => 'User Management', 'name' => 'Deletion of Inspector' ],
            [ 'type' => 'User Management', 'name' => 'Deletion of Admin Support' ],
            [ 'type' => 'User Management', 'name' => 'Changes on Super Admin' ],
            [ 'type' => 'User Management', 'name' => 'Changes on Administrator' ],
            [ 'type' => 'User Management', 'name' => 'Changes on Supervisor' ],
            [ 'type' => 'User Management', 'name' => 'Changes on Storeman' ],
            [ 'type' => 'User Management', 'name' => 'Changes on Inspector' ],
            [ 'type' => 'User Management', 'name' => 'Changes on Admin Support' ],

            [ 'type' => 'Sparelist', 'name' => 'Creation of Sparelist' ],
            [ 'type' => 'Sparelist', 'name' => 'Deletion of Sparelist' ],
            [ 'type' => 'Sparelist', 'name' => 'Changes on Sparelist' ],
            [ 'type' => 'Sparelist', 'name' => 'View of Sparelist' ],
            
            [ 'type' => 'Spares', 'name' => 'Issuing of Spares' ],
            [ 'type' => 'Spares', 'name' => 'Acceptance of TTC spares to store' ],
            [ 'type' => 'Spares', 'name' => 'Return of TTC spares to Tectstore' ],

            [ 'type' => 'Checklist', 'name' => 'Creation of Checklist' ],
            [ 'type' => 'Checklist', 'name' => 'Deletion of Checklist' ],
            [ 'type' => 'Checklist', 'name' => 'Changes on Checklist' ],
            [ 'type' => 'Checklist', 'name' => 'View of Checklist' ],

            [ 'type' => 'Faultlist', 'name' => 'Creation of Faultlist' ],
            [ 'type' => 'Faultlist', 'name' => 'Deletion of Faultlist' ],
            [ 'type' => 'Faultlist', 'name' => 'Changes on Faultlist' ],
            [ 'type' => 'Faultlist', 'name' => 'View of Faultlist' ],

            [ 'type' => 'RFID', 'name' => 'Creation of RFID masterlist entry (Birth Cert)' ],
            [ 'type' => 'RFID', 'name' => 'Changes of RFID masterlist entry' ],
            [ 'type' => 'RFID', 'name' => 'Deletion of RFID masterlist entry (Death Cert) ' ],
            [ 'type' => 'RFID', 'name' => 'View of RFID masterlist entry' ],
            [ 'type' => 'RFID', 'name' => 'Read / Write of RFID data' ],

            [ 'type' => 'Data Logging', 'name' => 'View of Data logging ' ],
            [ 'type' => 'Data Logging', 'name' => 'Exporting of Data' ],
            [ 'type' => 'Auditing', 'name' => 'Creation of internal auditing' ],

            [ 'type' => 'Measuring Equipment ', 'name' => 'Creation of Measuring Equipment ' ],
            [ 'type' => 'Measuring Equipment ', 'name' => 'Deletion of Measuring Equipment ' ],
            [ 'type' => 'Measuring Equipment ', 'name' => 'Changes on Measuring Equipment ' ],
            [ 'type' => 'Measuring Equipment ', 'name' => 'View of Faultlist' ],

            [ 'type' => 'f Lifting Jig & Appliance', 'name' => 'Creation of f Lifting Jig & Appliance' ],
            [ 'type' => 'f Lifting Jig & Appliance', 'name' => 'Deletion of f Lifting Jig & Appliance' ],
            [ 'type' => 'f Lifting Jig & Appliance', 'name' => 'Changes on f Lifting Jig & Appliance' ],
            [ 'type' => 'f Lifting Jig & Appliance', 'name' => 'View of Faultlist' ],

            [ 'type' => 'Asset', 'name' => 'Creation of Asset' ],
            [ 'type' => 'Asset', 'name' => 'Deletion of Asset' ],
            [ 'type' => 'Asset', 'name' => 'Changes on Asset' ],
            [ 'type' => 'Asset', 'name' => 'View of Asset' ],
            [ 'type' => 'Asset', 'name' => 'Issuing of Asset' ],

            [ 'type' => 'Task', 'name' => 'Issuing of Task' ],
            [ 'type' => 'Task', 'name' => 'Acceptance of task' ],
            [ 'type' => 'Task', 'name' => 'View of Dashboard' ],

            [ 'type' => 'POL management', 'name' => 'Creation of POL management' ],
            [ 'type' => 'POL management', 'name' => 'Deletion of POL management' ],
            [ 'type' => 'POL management', 'name' => 'Changes on POL management' ],
            [ 'type' => 'POL management', 'name' => 'View of POL management' ],
            [ 'type' => 'POL management', 'name' => 'Issuing of POL' ],
            [ 'type' => 'POL management', 'name' => 'Receiving of POL' ],
        ];
    }
}
