<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    protected array $data;
    protected int $httpCode;

    /**
     * ApiException constructor.
     *
     * @param int $code
     * @param array $data
     * @param int $httpCode
     */
    public function __construct(int $code, array $data = [], int $httpCode = 520)
    {
        $previous = null;
        $this->data = $data;
        $this->httpCode = $httpCode;
        parent::__construct('', $code, $previous);
    }

    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        //
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function render(Request $request) : JsonResponse
    {
        $response = array_merge(['error' => $this->code], $this->data);
        return response()->json($response, $this->httpCode);
    }
}
