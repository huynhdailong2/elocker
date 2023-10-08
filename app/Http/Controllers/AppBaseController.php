<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Response;
use Exception;
use Illuminate\Validation\ValidationException;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Inventory Drk Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message = null, $metaData = null)
    {
        $res = [
            'success' => true,
            'message' => $message,
            'data' => $result,
        ];

        if ($metaData && is_array($metaData)) {
            $res = array_merge($res, $metaData);
        }

        // LoggingUtils::save();

        return response()->json($res);
    }

    public function sendError(Exception $error, $code = 500)
    {
        if ($error instanceof ValidationException) {
            throw $error;
        }

        $res = [
            'success' => false,
            'message' => $error->getMessage(),
        ];

        return response()->json($res, $code);
    }

    /**
     * Send message error with status code 200
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessageFailure(string $message)
    {
        $res = [
            'success' => false,
            'message' => $message,
        ];

        return response()->json($res);
    }
}
