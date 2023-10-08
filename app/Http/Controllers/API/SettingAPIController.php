<?php

namespace App\Http\Controllers\API;

use App\Consts;
use App\Http\Controllers\AppBaseController;
use App\Http\Services\SettingService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SettingAPIController extends AppBaseController
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function getScheduleSettings(Request $request)
    {
        $data = $this->settingService->getScheduleSettings($request->all());
        return $this->sendResponse($data);
    }

    public function saveSenderEmail(Request $request)
    {
        $request->validate([
            'sender_email'     => 'required|email',
            'sender_password'  => 'required',
        ]);

        DB::beginTransaction();
        try {
            $data = $this->settingService->saveSenderEmail($request->all());
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function saveReceiverEmail(Request $request)
    {
        $request->validate([
            'type'  => [
                'required',
                Rule::in(
                    Consts::RECEIVER_EMAIL_TYPE_CYCLE_COUNT,
                    Consts::RECEIVER_EMAIL_TYPE_INVENTORY_COUNT,
                    Consts::RECEIVER_EMAIL_TYPE_MAINTENANCE,
                    Consts::RECEIVER_EMAIL_TYPE_VEHICLE,
                    Consts::RECEIVER_EMAIL_TYPE_DEFAULT,
                    Consts::RECEIVER_EMAIL_TYPE_ALERT_WEIGHING_SYSTEM,
                )
            ],
            'value' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $data = $this->settingService->saveReceiverEmail($request->all());
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function saveCycleCountSchedule(Request $request)
    {
        $request->validate([
            'report_type'  => [
                'required',
                Rule::in(
                    Consts::SCHEDULE_REPORT_WEEKLY,
                    Consts::SCHEDULE_REPORT_MONTHLY
                )
            ],
            'schedule' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $params['type'] = Consts::SCHEDULE_TYPE_CYCLE_COUNT_KEY;

            $data = $this->settingService->saveSchedule($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function saveInventoryCountSchedule(Request $request)
    {
        $request->validate([
            'report_type'  => [
                'required',
                Rule::in(
                    Consts::SCHEDULE_REPORT_WEEKLY,
                    Consts::SCHEDULE_REPORT_MONTHLY
                )
            ],
            'schedule' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $params['type'] = Consts::SCHEDULE_TYPE_INVENTORY_COUNT_KEY;

            $data = $this->settingService->saveSchedule($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function saveAlertWeighingSystemSchedule(Request $request)
    {
        $request->validate(
            [
                'report_type' => [
                    'required',
                    Rule::in(
                        Consts::SCHEDULE_REPORT_WEEKLY,
                        Consts::SCHEDULE_REPORT_MONTHLY
                    )
                ],
                'schedule' => 'required'
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();
            $params['type'] = Consts::SCHEDULE_TYPE_ALERT_WEIGHING_SYSTEM_KEY;

            $data = $this->settingService->saveSchedule($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function saveByKey(Request $request)
    {
        $request->validate(
            [
                'key' => 'required|string',
                'value' => 'required|string'
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();

            $data = $this->settingService->saveByKey(Arr::get($params, 'key'), Arr::get($params, 'value'));
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getByKey(Request $request)
    {
        $request->validate(
            [
                'key' => 'required|string',
            ]
        );

        try {
            $params = $request->all();

            $data = $this->settingService->getByKey(Arr::get($params, 'key'));
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
