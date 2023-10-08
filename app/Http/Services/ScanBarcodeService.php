<?php


namespace App\Http\Services;
use App\Consts;
use App\Models\Vehicle;
use Cache;
use Illuminate\Support\Arr;

class ScanBarcodeService extends BaseService
{
    const SCAN_BARCODE = 'SCAN_BARCODE';
    const SCAN_BARCODE_STEP = 'SCAN_BARCODE_STEP';
    const SCAN_BARCODE_STEP_ONE = 'SCAN_BARCODE_STEP_ONE';
    const SCAN_BARCODE_STEP_TWO = 'SCAN_BARCODE_STEP_TWO';
    const SCAN_BARCODE_STEP_THREE = 'SCAN_BARCODE_STEP_THREE';
    const SCAN_BARCODE_DATA = 'SCAN_BARCODE_DATA';

    private $barcodeData = null;
    private $response = [];

    public function __construct()
    {
        $this->barcodeData = $this->getScanBarcodeFromCache();
    }

    public function scanBarcode($params = [])
    {
        $barcodeValue = Arr::get($params, 'barcode');

        $this->updateScanBarcodeCurrentStep($barcodeValue);

        return $this->response;
    }

    public function finishedScanBarcode()
    {
        Cache::forget(self::SCAN_BARCODE);

        return true;
    }

    private function updateScanBarcodeCurrentStep($barcodeValue) {
        $currentStep = Arr::get($this->barcodeData, self::SCAN_BARCODE_STEP);
        switch ($currentStep) {
            case 1:
                $this->updateScanBarcodeStepTwo($barcodeValue);
                break;
            case 2:
                if($this->barcodeData[self::SCAN_BARCODE_DATA][self::SCAN_BARCODE_STEP_TWO]) {
                    $this->updateScanBarcodeStepThree($barcodeValue);
                } else {
                    $this->updateScanBarcodeStepTwo($barcodeValue);
                }
                break;
            default:
                $this->updateScanBarcodeStepOne($barcodeValue);
                break;
        }
    }

    /**
     * Step input Service/MO# of Service/MO Management screen
     * @param $barcode
     */
    private function updateScanBarcodeStepOne($barcode) {
        $this->barcodeData[self::SCAN_BARCODE_STEP] = 1;
        $this->barcodeData[self::SCAN_BARCODE_DATA][self::SCAN_BARCODE_STEP_ONE] = $barcode;

        $this->updateBarcodeData();

        $this->response = [
            'status' => true,
            'currentStep' => 1,
            'nextStep' => 2,
            'barcodeData' => $this->barcodeData,
            'barcodeValue' => $barcode,
        ];
    }

    /**
     * Step input Veh# of Service/MO Management screen
     * @param $barcode
     */
    private function updateScanBarcodeStepTwo($barcode)
    {
        $vehicle = Vehicle::query()->where('vehicle_num', trim($barcode))->first();
        $vehicleId = null;
        $status = false;
        if($vehicle) {
            $vehicleId = $vehicle->id;
            $status = true;
        }

        $this->barcodeData[self::SCAN_BARCODE_STEP] = 2;
        $this->barcodeData[self::SCAN_BARCODE_DATA][self::SCAN_BARCODE_STEP_TWO] = $vehicleId;
        $this->updateBarcodeData();

        $this->response = [
            'status' => $status,
            'currentStep' => 2,
            'nextStep' => $status ? 3 : 2,
            'barcodeData' => $this->barcodeData,
            'barcodeValue' => $barcode,
        ];
    }

    /**
     * Step input Platform of Service/MO Management screen
     * @param $barcode
     */
    private function updateScanBarcodeStepThree($barcode)
    {
        $this->barcodeData[self::SCAN_BARCODE_STEP] = 3;
        $this->barcodeData[self::SCAN_BARCODE_DATA][self::SCAN_BARCODE_STEP_THREE] = $barcode;

        $this->updateBarcodeData();

        $this->response = [
            'status' => true,
            'currentStep' => 3,
            'nextStep' => null,
            'barcodeData' => $this->barcodeData,
            'barcodeValue' => $barcode,
        ];
    }

    private function updateBarcodeData()
    {
        Cache::forever(self::SCAN_BARCODE, $this->barcodeData);
    }

    private function getScanBarcodeFromCache() {
        // Get step from cache
        if (Cache::has(self::SCAN_BARCODE)) {
            return Cache::get(self::SCAN_BARCODE);
        }

        return $this->initScanBarcode();
    }

    private function initScanBarcode() {
        $barcodeData = [
            self::SCAN_BARCODE_STEP => null,
            self::SCAN_BARCODE_DATA => [
                self::SCAN_BARCODE_STEP_ONE => null,
                self::SCAN_BARCODE_STEP_TWO => null,
                self::SCAN_BARCODE_STEP_THREE => null,
            ]
        ];

        Cache::forever(self::SCAN_BARCODE, $barcodeData);

        return $barcodeData;
    }
}
