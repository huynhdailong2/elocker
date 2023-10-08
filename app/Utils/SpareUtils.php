<?php

namespace App\Utils;

use App\Models\Bin;
use App\Models\EucBoxSpare;
use App\Models\IssueCard;
use Exception;

class SpareUtils
{
    public static function canDelete($spareId)
    {
        $issuedCard = static::spareIssuedBy($spareId);
        if($issuedCard) {
            $takerName = $issuedCard->taker->name;
            throw new Exception("This spare is issued by ${takerName}, it needs to be returned first");
        }

        /** @var ?Bin $assignedBin */
        $assignedBin = static::spareAssignedBin($spareId);
        if ($assignedBin) {
            $location = implode('-', [$assignedBin->cluster->name, $assignedBin->shelf->name, $assignedBin->row, $assignedBin->bin]);
            throw new Exception("This spare is in location $location, please remove it from away Bin first");
        }

        /** @var ?EucBoxSpare $assignedBin */
        $assignedEucBox = static::spareAssignedEucBox($spareId);
        if ($assignedEucBox) {
            $eucBox = $assignedEucBox->eucBox->order;
            throw new Exception("This spare is in EUC Box #${eucBox}, please remove it from away Bin first");
        }
    }

    private static function spareAssignedBin($spareId)
    {
        return Bin::query()
            ->where('spare_id', $spareId)
            ->first();
    }

    private static function spareAssignedEucBox($spareId)
    {
        return EucBoxSpare::query()
            ->with(
                [
                    'eucBox'
                ]
            )
            ->where('spare_id', $spareId)
            ->first();
    }

    private static function spareIssuedBy($spareId)
    {
        return IssueCard::query()
            ->with(
                [
                    'taker'
                ]
            )
            ->where('spare_id', $spareId)
            ->where(function($subQuery) {
                $subQuery->whereColumn('quantity', '!=', 'returned_quantity')
                    ->orWhereNull('returned_quantity');
            })
            ->first();
    }

    public static function existsInBin($spareId)
    {
        return Bin::where('spare_id', $spareId)->exists();
    }

    private static function existsInEucBox($spareId)
    {
        return EucBoxSpare::where('spare_id', $spareId)->exists();
    }

    private static function throwNoPermission()
    {
        throw new Exception('The spare is used somewhere, so you cannot delete it.');

    }
}
