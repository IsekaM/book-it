<?php

namespace App\Mixins;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ResponseMixin
{
    public function formattedJson(): \Closure
    {
        return function (
            array|Collection|Model|LengthAwarePaginator $data = null,
            int $statusCode = 200,
            string $message = null,
            bool $wrapData = true,
        ): JsonResponse {
            $responseData = [];

            if ($statusCode >= 400) {
                $responseData["success"] = false;
            }

            if ($statusCode < 400) {
                $responseData["success"] = true;
            }

            if (isset($message)) {
                $responseData["message"] = $message;
            }

            if (
                isset($data) &&
                $wrapData &&
                !($data instanceof LengthAwarePaginator)
            ) {
                $responseData["data"] = $data;
            }

            if (
                isset($data) &&
                !$wrapData &&
                !($data instanceof LengthAwarePaginator)
            ) {
                $responseData += $data;
            }

            if ($data instanceof LengthAwarePaginator) {
                $responseData += $data->toArray();
            }

            return Response::json($responseData, $statusCode);
        };
    }
}
