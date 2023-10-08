<?php

namespace App\Http\Services;

use DB;
use App\Consts;

class BaseService
{
    public function getParams($className, $data, $instance = null)
    {
        $fillable = $this->getFillable($className);
        $value = [];
        foreach ($fillable as $key => $field) {
            if (array_key_exists($field, $data)) {
                $value[$field] = $data[$field];
                if ($instance) {
                    $instance->{$field} = $data[$field];
                }
            }
        }
        return $instance ?: $value;
    }

    public function saveData($instance, $data)
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

    public function getFillable($className)
    {
        return (new $className())->getFillable();
    }

    public function saveManyData($data, $className, $parent, $keys = [])
    {
        $saved = [];

        $fetchExistsInstance = function ($data, $className, $keys) {
            $instance = new $className();
            $conditions = [];

            foreach ($instance->getFillable() as $key => $field) {
                if (in_array($field, $keys)) {
                    $conditions[] = [$field, '=', $data[$field]];
                }
            }
            return $className::where($conditions)->first();
        };

        foreach ($data as $value) {
            $instance = $fetchExistsInstance($value, $className, $keys);
            if (!$instance) {
                $instance = new $className();
            }

            $instance = $this->saveData($instance, $value);
            $saved[] = $instance->id;
        }

        $className::where($parent['key'], $parent['value'])
            ->whereNotIn('id', $saved)->delete();

        return true;
    }
}
