<?php


namespace App\Imports;


use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use App\Consts;
use Exception;


class UsersImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, ShouldQueue {

    public function model(array $row)
    {
        $roleName = str_replace(' ', '_', trim(strtoupper($row['role'])));
        $roleId = null;
        $password = $row['password'] ?: '123123';
        $userName = $row['username'];

        $roles = [
            'SUPER_ADMIN' => Consts::USER_ROLE_SUPER_ADMIN,
            'ADMINISTRATOR' => Consts::USER_ROLE_ADMINISTRATOR,
            'ADMIN_SUPPORT' => Consts::USER_ROLE_ADMIN_SUPPORT,
            'STOREMAN' => Consts::USER_ROLE_STOREMAN,
            'INSPECTOR' => Consts::USER_ROLE_INSPECTOR,
        ];
        if(array_key_exists($roleName, $roles)) {
            $roleId = $roles[$roleName];
        }

        if (! $roleId) {
            $this->logRowInvalid($row);
            return;
        }

        try {
            $user = User::query()->where('login_name', $userName)->first() ?: new User();
            $row['role'] = $roleId;
            $row['login_name'] = $userName;
            $row['name'] = $userName;
            $row['password'] = $password;

            $this->saveData($user, $row);
        } catch (Exception $ex) {
            $this->logRowInvalid($row);
            logger()->error($ex);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }

    private function saveData($instance, $data)
    {
        foreach ($instance->getFillable() as $key => $field) {
            if (array_key_exists($field, $data)) {
                $instance->{$field} = $data[$field];
            }
        }
        if ($instance->isDirty()) {
            $instance->save();
        }
        return $instance;
    }

    private function logRowInvalid($row)
    {
        logger()->error('===============UsersImport::row_invalid = ', [$row]);
    }
}
