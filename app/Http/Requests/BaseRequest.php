<?php


namespace App\Http\Requests;


use App\Exceptions\ApiException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{

    /**
     * @param Validator $validator
     *
     * @throws ApiException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ApiException(422, ['fields' => $validator->errors()->toArray()], 422);
    }

    /**
     * @throws ApiException
     */
    protected function failedAuthorization()
    {
        throw new ApiException(403, [], 403);
    }
}
