<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Useremail' => 'required',
            'Password' => 'required',
        ];
    }

    public function failedValidation(Validator $validator, array $data = [])
    {
        throw new HttpResponseException(
            response()->json(array_merge([
                'code'      => 0,
                'success'   => 'error',
                'message'   => $validator->errors()->first() ?? 'not found'
            ], $data), 400)
        );
    }
}
