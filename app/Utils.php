<?php

namespace App;

use Carbon\Carbon;
use App\Consts;
use Auth;
use DB;
use App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class Utils
{

    public static function previous24hInMillis() {
        return Carbon::now()->subDay()->timestamp * 1000;
    }

    public static function previousDayInMillis($day) {
        return Carbon::now()->subDay($day)->timestamp * 1000;
    }

    public static function currentMilliseconds() {
        return round(microtime(true) * 1000);
    }

    public static function toMilliseconds($hours) {
        return floatval($hours) * 60 * 60 * 1000;
    }

    public static function millisecondsToDateTime($timestamp, $timezoneOffsetInMins, $format) {
        return Utils::millisecondsToCarbon($timestamp, $timezoneOffsetInMins)->format($format);
    }

    public static function millisecondsToCarbon($timestamp, $timezoneOffsetInMins) {
        return Carbon::createFromTimestampUTC(floor($timestamp/1000))->subMinutes($timezoneOffsetInMins);
    }

    public static function dateTimeToMilliseconds($stringDate) {
        $date = !empty($stringDate) ? Carbon::parse($stringDate) : Carbon::now();
        return $date->timestamp * 1000 + $date->micro;
    }

    public static function generateRandomString($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = strlen($keyspace) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public static function trimFloatNumber($val) {
        return strpos($val,'.')!==false ? rtrim(rtrim($val,'0'),'.') : $val;
    }

    public static function saveFileToStorage ($file, $pathFolder, $prefixName = null) {
        $storagePath = 'public' . DIRECTORY_SEPARATOR . $pathFolder;

        $filename = Utils::currentMilliseconds() . '.' . $file->getClientOriginalExtension();
        if (!empty($prefixName)) {
            $filename = $prefixName . '.' .$filename;
        }
        $file->storeAs($storagePath, $filename);
        return "/storage/$pathFolder/$filename";
    }

    public static function convertBase64FromImage($imgPath)
    {
        $type = pathinfo($imgPath, PATHINFO_EXTENSION);
        $data = file_get_contents($imgPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    public static function getSchemeAndHttpHost()
    {
        return env('APP_URL', 'http://localhost');
    }

    public static function escapeLike(string $value, string $char = '\\'): string
    {
        return str_replace(
            [$char, '%', '_'],
            [$char.$char, $char.'%', $char.'_'],
            $value
        );
    }

    public static function maskData($data, $ignoreAttributes)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = static::maskData($value, $ignoreAttributes);
                continue;
            }

            if (! in_array($key, $ignoreAttributes)) {
                continue;
            }

            $data[$key] = sprintf('%s**********', substr($value, 0, 2));
        }

        return $data;
    }

    public static function standardValues($data)
    {
        $nullable = ['', 'null'];

        foreach ($data as $key => $value) {
            if (in_array($value, $nullable)) {
                $data[$key] = null;
            }
        }

        return $data;
    }

    public static function convertArrayToPagination($data, $limit)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($data);
        $currentPageItems = $itemCollection->slice(($currentPage * $limit) - $limit, $limit)->all();

        return new LengthAwarePaginator($currentPageItems, count($itemCollection), $limit);
    }
}
