<?php


namespace App\Http\Services;


use App\Consts;
use App\Exports\WeighingSystemTransactionExport;
use App\Mails\WeighingSystemTransactionReport;
use App\Models\WeighingHistory;
use App\Models\WeighingTransaction;
use App\Utils\SettingUtils;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Excel;
use Mail;
use Storage;


class WeightSystemService extends BaseService
{
    public const WEIGHT_TOKEN = 'weight_token';

    public $client;

    public function __construct()
    {
        // If connect with weighing system
        if(env('WEIGHT_SYSTEM_HOST')) {
            $headers = [
                'Accept' => 'application/json',
                "Content-Type" => "application/json"
            ];
            $token = $this->getToken();
            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
            $this->client = new Client(
                [
                    'headers' => $headers,
                    'base_uri' => env('WEIGHT_SYSTEM_HOST')
                ]
            );
        }
    }

    private function loginWeightSystem()
    {
        $client = new Client();
        $response = $client->request('POST', env('WEIGHT_SYSTEM_HOST') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => 1,
                'client_secret' => 'KXpXYeFHwE2LiCmTB9jxIrmTKGqiX1gnqn44WXQd',
                'username' => env('WEIGHT_SYSTEM_USERNAME'),
                'password' => env('WEIGHT_SYSTEM_PASSWORD'),
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function getToken()
    {
        if (cache()->has(self::WEIGHT_TOKEN)) {
            return cache()->get(self::WEIGHT_TOKEN);
        }
        $authenticationContent = $this->loginWeightSystem();
        $token = $authenticationContent['accessToken'];

        cache()->put(self::WEIGHT_TOKEN, $token, 12 * 60 * 60); // 12h

        return $token;
    }

    private function getContentFromResponse($response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getListSites()
    {
        $response = $this->client->request('GET', 'api/sites/list');

        return $this->getContentFromResponse($response);
    }

    public function getBinsOfShelf($shelfId)
    {
        $response = $this->client->request('POST', 'api/shelf/get-bins-without-reader', [
            'form_params' => [
                'shelfId' => $shelfId,
            ]
        ]);

        $content = $this->getContentFromResponse($response);
        return Arr::get($content, 'devices', []);
    }

    public function updateBin($bin)
    {
        $response = $this->client->request('POST', 'api/devices/update', [
            'form_params' => $this->formatBin($bin)
        ]);

        return $this->getContentFromResponse($response);
    }

    private function formatBin($bin)
    {
        return [
            "id" => Arr::get($bin, 'id', ''),
            "quantity" => Arr::get($bin, 'quantity'),
            "quantityCritThreshold" => Arr::get($bin, 'quantityCritThreshold', '') ?? '',
            "quantityMinThreshold" => Arr::get($bin, 'quantityMinThreshold', '') ?? '',
            "weight" => Arr::get($bin, 'weight', '') ?? '',
            "zeroWeight" => Arr::get($bin, 'zeroWeight', '') ?? '',
            "deviceId" => Arr::get($bin, 'deviceId'),
            "calcQuantity" => Arr::get($bin, 'calcQuantity', '') ?? '',
            "calcWeight" => Arr::get($bin, 'calcWeight', '') ?? '',
            "deviceDescription" => [
                "batchNoBag" => Arr::get($bin, 'deviceDescription.batchNoBag'),
                "batchNoBag2" => Arr::get($bin, 'deviceDescription.batchNoBag2'),
                "batchNoBag3" => Arr::get($bin, 'deviceDescription.batchNoBag3'),
                "criCode" => Arr::get($bin, 'deviceDescription.criCode'),
                "expiryBag" => Arr::get($bin, 'deviceDescription.expiryBag'),
                "expiryBag2" => Arr::get($bin, 'deviceDescription.expiryBag2'),
                "expiryBag3" => Arr::get($bin, 'deviceDescription.expiryBag3'),
                "field1" => Arr::get($bin, 'deviceDescription.field1'),
                "itemAcct" => Arr::get($bin, 'deviceDescription.itemAcct'),
                "jom" => Arr::get($bin, 'deviceDescription.jom'),
                "materialNo" => Arr::get($bin, 'deviceDescription.materialNo'),
                "matlGrp" => Arr::get($bin, 'deviceDescription.matlGrp'),
                "name" => Arr::get($bin, 'deviceDescription.name'),
                "partNumber" => Arr::get($bin, 'deviceDescription.partNumber'),
                "quantityBag" => Arr::get($bin, 'deviceDescription.quantityBag'),
                "quantityBag2" => Arr::get($bin, 'deviceDescription.quantityBag2'),
                "quantityBag3" => Arr::get($bin, 'deviceDescription.quantityBag3'),
                "supplierEmail" => Arr::get($bin, 'deviceDescription.supplierEmail'),
            ]
        ];
    }

    public function getAllBins()
    {
        $collection = collect();
        $listSites = $this->getListSites();
        $site = $listSites['sites'][0];
        $siteName = Arr::get($site, 'name');
        $room = $site['rooms'][0];
        $roomName = Arr::get($room, 'name');
        foreach ($room['shelves'] as $shelf) {
            $siteId = Arr::get($room, 'siteId');
            $roomId = Arr::get($room, 'id');

            $shelfId = $shelf['id'];
            $shelfName = Arr::get($shelf, 'name');

            $listBins = $this->getBinsOfShelf($shelfId);
            foreach ($listBins as $bin) {
                $collection->add(
                    [
                        'bin_id' => Arr::get($bin, 'id'),
                        'device_id' => Arr::get($bin, 'deviceId'),
                        'part_number' => Arr::get($bin, 'deviceDescription.partNumber'),
                        'name' => Arr::get($bin, 'deviceDescription.name'),
                        'quantity' => Arr::get($bin, 'quantity'),
                        'site_id' => $siteId,
                        'site_name' => $siteName,
                        'room_id' => $roomId,
                        'room_name' => $roomName,
                        'shelf_id' => $shelfId,
                        'shelf_name' => $shelfName,
                        'key' => $shelfId . '_' . Arr::get($bin, 'deviceId')
                    ]
                );
            }
        }

        return $collection->keyBy('key');
    }

    public function transactionsWeighingSystem($params = [])
    {
        $transactionDate = Arr::get($params, 'transaction_date');
        return WeighingTransaction::with(['weighingHistory'])->when($transactionDate, function ($query) use ($transactionDate) {
            return $this->queryRange($query, $transactionDate, "created_at");
        })->when(
            !empty($params['no_pagination']),
            function ($query) {
                return $query->get();
            },
            function ($query) use ($params) {
                return $query->paginate((int)Arr::get($params, 'limit', Consts::DEFAULT_PER_PAGE));
            }
        );
    }

    private function queryRange($query, $value, $property)
    {
        if(!is_array($value)) {
            $value = (array)json_decode($value, true);
        }

        $start = array_get($value, 'start');
        $end = array_get($value, 'end');

        if (empty($start) || empty($end)) {
            return $query;
        }

        return $query->where($property, '>=', Carbon::createFromTimeString($start))
            ->where($property, '<=', Carbon::createFromTimeString($end));
    }

    public function sendWeighingSystemTransactionReport($params = [])
    {
        SettingUtils::validateMailSender();

        $currentTIme = now()->format('Y-m-d');

        $filename = "reports/weighing-system-transaction-{$currentTIme}.xlsx";
        Excel::store(new WeighingSystemTransactionExport($params), $filename, 'public');
        $filePath = Storage::disk('public')->path($filename);

        $emails = array_get($params, 'emails', []);

        collect($emails)
            ->each(function ($receiver) use ($filePath) {
                Mail::queue(new WeighingSystemTransactionReport($receiver, $filePath));
            });

        return true;
    }
}
