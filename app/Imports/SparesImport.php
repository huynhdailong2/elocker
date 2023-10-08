<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use App\Models\Spare;
use App\Consts;
use Exception;

class SparesImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, ShouldQueue {

    public function model(array $row)
    {
        $type = $row['type'];
        $types = [
            Consts::SPARE_TYPE_CONSUMABLE,
            Consts::SPARE_TYPE_DURABLE,
            Consts::SPARE_TYPE_DURABLE_2,
            Consts::SPARE_TYPE_PERISHABLE,
            Consts::SPARE_TYPE_AFES,
            Consts::SPARE_TYPE_EUC,
            Consts::SPARE_TYPE_TORQUE_WRENCH,
            Consts::SPARE_TYPE_OTHERS,
            Consts::SPARE_TYPE_LIFTING_EQUIPMENT,
        ];

        if (!in_array($type, $types)) {
            $this->logRowInvalid($row);
            return;
        }

        try {
            $materialNo = $row['material_no'];
            $spare = Spare::where('material_no', $materialNo)->first() ?: new Spare;

            $row['type'] = $type === Consts::SPARE_TYPE_DURABLE_2 ? Consts::SPARE_TYPE_DURABLE : $row['type'];

            $this->saveData($spare, $row);
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
        logger()->error('===============SparesImport::row_invalid = ', [$row]);
    }
}
