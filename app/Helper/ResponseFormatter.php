<?php

namespace App\Helper;

use Carbon\Carbon;

/**
 * JSON Response Formatter.
 */
class ResponseFormatter
{
    /**
     * API Response
     *
     * @var array
     */
    protected static $response = [
        'meta' => [
            'code' => 200,
            'status' => 'success',
            'messages' => [],
        ],
        'data' => null,
    ];

    /**
     * Give success response.
     */
    public static function success($data = null, $messages = null)
    {
        if (!is_null($messages)) {
            if (!is_array($messages)) {
                self::$response['meta']['messages'] = array($messages);
            } else {
                self::$response['meta']['messages'] = $messages;
            }
        }

        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    /**
     * Give error response.
     */
    public static function error($code = 400, $messages = null)
    {
        if (!is_null($messages)) {
            $messages = is_string($messages) ? $messages : self::reformatErrorMessage($messages);

            self::$response['meta']['messages'] = $messages;
        }

        self::$response['meta']['status'] = 'error';
        self::$response['meta']['code'] = $code;

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    private static function reformatErrorMessage($errors): array
    {
        $formattedErrors = [];

        if ($errors instanceof \Illuminate\Support\MessageBag) {
            $errors = $errors->toArray();
        }

        if (is_array($errors)) {
            foreach ($errors as $field => $messages) {
                if (is_string($messages)) {
                    $messages = [$messages];
                }

                foreach ($messages as $message) {
                    $formattedErrors[] = [
                        "type"     => "field",
                        "msg"      => $message,
                        "path"     => $field,
                        "location" => "body"
                    ];
                }
            }
        }

        return $formattedErrors;
    }
}
