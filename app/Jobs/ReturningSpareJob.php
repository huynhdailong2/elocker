<?php

namespace App\Jobs;

use App\Models\TrackingMo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\IssueCard;
use App\Models\ReturnSpare;
use App\Consts;
use App\Utils\BigNumber;

class ReturningSpareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;
    private $data;
    private $spare_ids;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $data, $spare_ids)
    {
        $this->userId = $userId;
        $this->data = $data;
        $this->spare_ids = $spare_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return $this->handleReturning();
    }

    private function handleReturning()
    {
        $itemsFromCard = collect($this->data)->filter(function ($record) {
            return !empty($record['issue_card_id']);
        });
        $this->updateIssueCards($itemsFromCard);

        $itemsFromHandover = collect($this->data)->filter(function ($record) {
            return !empty($record['return_spare_id']);
        });
        $this->updateItemsHandover($itemsFromHandover);
    }

    private function getIssueCards($binIds, $spare)
    {
        return IssueCard::whereIn('bin_id', $binIds)->whereIn('spare_id', $spare)
            ->where(function ($query) {
                $query->whereNull('returned')
                    ->orWhere('returned', Consts::RETURNED_TYPE_PARTIAL);
            })
            ->orderBy('created_at')
            ->get();
    }

    private function getItemsHandover($binIds, $spare_id)
    {
        return ReturnSpare::whereIn('bin_id', $binIds)->whereIn('spare_id', $spare_id)
            ->where('type', Consts::HAND_OVER)
            ->where('receiver_id', $this->userId)
            ->whereColumn('quantity', '<>', 'quantity_returned_store')
            ->orderBy('created_at')
            ->get();
    }

    private function updateIssueCards($data)
    {
        $mapData = collect($data)->mapWithKeys(function ($item) {
            return [$item['bin_id'] => $item['spare_id']];
        });
        
        $binIds = array_keys($mapData->toArray());
        $spareIds = array_values($mapData->toArray());
        // foreach($this->spare_ids as $item){
            $cards = $this->getIssueCards($binIds, $spareIds);

        // }

        foreach ($cards as $card) {
            $inputQuantity = $mapData[$card->bin_id]['quantity'];
            if (!$inputQuantity) {
                continue;
            }

            $remainQuantityInCard = BigNumber::new($card->quantity)
                ->sub($card->returned_quantity)
                ->toString();

            $state = Consts::RETURNED_TYPE_PARTIAL;
            $returnedQuantity = BigNumber::new($card->returned_quantity ?: 0)
                ->add($inputQuantity)
                ->toString();

            // equal or bigger.
            if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                $state = Consts::RETURNED_TYPE_ALL;
                $returnedQuantity = $card->quantity;
            }

            $card->returned = $state;
            $card->returned_quantity = $returnedQuantity;
            $card->save();
        }

        // Clear tracking mo => because quantity always equal 1
        TrackingMo::whereIn('bin_id', $binIds)->where('spare_id', $this->data)
            ->delete();
    }

    private function updateItemsHandover($data)
    {
        $mapData = collect($data)->mapWithKeys(function ($item) {
            return [$item['bin_id'] => $item['spare_id']];
        });
        
        $binIds = array_keys($mapData->toArray());
        $spareIds = array_values($mapData->toArray());
        // foreach($this->spare_ids as $item){
        $returnings = $this->getItemsHandover($binIds,$spareIds);
        // }
        foreach ($returnings as $return) {
            $inputQuantity = $mapData[$return->bin_id]['quantity'];
            if (!$inputQuantity) {
                continue;
            }

            $remainQuantityInCard = BigNumber::new($return->quantity)
                ->sub($return->quantity_returned_store)
                ->toString();

            $returnedQuantity = BigNumber::new($return->quantity_returned_store ?: 0)
                ->add($inputQuantity)
                ->toString();

            // equal or bigger.
            if (~BigNumber::new($inputQuantity)->comp($remainQuantityInCard)) {
                $returnedQuantity = $return->quantity;
            }

            $return->quantity_returned_store = $returnedQuantity;
            $return->save();
        }
    }
}
