<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Rule;



class StoreUserRequest extends FormRequest
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
            'username' => 'required|string|string',
            'fullName' => 'required|string|min:6',
            'dob' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'password' => 'required|string|max:191',
            'phoneNumber' => 'required|string|max:191',
            'class' => 'required|string|max:191',
            'parentName' => 'required|string|max:191',
            'parentContact' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'role' => 'required|string|max:191',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }
}
