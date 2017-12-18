<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public static function sendError($error)
    {
        return response()->json([
            'status' => 'ERROR',
            'data'   => [
                'code'    => $error['code'],
                'message' => $error['message']
            ]
        ]);
    }

    public static function sendSuccess($data)
    {
        if (\Auth::user())
            if (is_object($data))
                $data->user_id = \Auth::user()->id;
            else
                $data['user_id'] = \Auth::user()->id;

        return response()->json([
            'status' => 'SUCCESS',
            'data'   => $data
        ]);
    }

    public static function sendException(\Exception $e)
    {
        return response()->json([
            'status' => 'EXCEPTION',
            'data'   => [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]
        ]);
    }
}
