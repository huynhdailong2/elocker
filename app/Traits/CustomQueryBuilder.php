<?php

namespace App\Traits;

use App\Consts;

trait CustomQueryBuilder
{

    private function queryRange($query, $value, $property)
    {
        $value = (array) json_decode($value, true);

        $start = array_get($value, 'start');
        $end = array_get($value, 'end');

        if (empty($start) || empty($end)) {
            return $query;
        }

        return $query->where($property, '>=', $start)
            ->where($property, '<=', $end);
    }

}

