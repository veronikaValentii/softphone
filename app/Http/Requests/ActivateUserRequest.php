<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateUserRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'max:255',
                Rule::exists('users', 'email')
                    ->where('status', User::STATUS_INACTIVE)
            ],
            'code' => [
                'required',
                'numeric',
                Rule::exists('user_confirmations', 'code')
                    ->where('context', $this->get('email'))
                    ->where('is_active', true)
            ]
        ];
    }
}
