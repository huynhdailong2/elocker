<?php


namespace App\Http\Controllers\API;


use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Services\WeightSystemService;
use Exception;
use Illuminate\Http\Request;


class WeightSystemAPIController extends BaseController
{
    protected $weightSystemService;

    public function __construct(WeightSystemService $weightSystemService)
    {
        $this->weightSystemService = $weightSystemService;
    }

    public function getListSites(Request $request)
    {
        try {
            $data = $this->weightSystemService->getListSites();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getBinsOfShelf($shelfId)
    {
        try {
            $data = $this->weightSystemService->getBinsOfShelf($shelfId);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function updateBin(Request $request)
    {
        try {
            $body = $request->all();
            $data = $this->weightSystemService->updateBin($body);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getAllBins(Request $request)
    {
        try {
            $data = $this->weightSystemService->getAllBins();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function transactionsWeighingSystem(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->weightSystemService->transactionsWeighingSystem($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
