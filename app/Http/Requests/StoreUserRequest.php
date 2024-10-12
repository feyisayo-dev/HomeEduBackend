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
            'email' => 'required|email|string',
            'password' => 'required|string|min:6',
            'sname' => 'required|string|max:191',
            'fname' => 'required|string|max:191',
            'mname' => 'nullable|string|max:191',
            'Gender' => 'required|string|max:191',
            'dob' => 'required|string|max:191',
            'Status' => 'nullable|string|max:191',
            'mobile' => 'required|string|max:191',
            'Altmobile' => 'nullable|string|max:191',
            'address' => 'required|string|max:191',
            'Country' => 'required|string|max:191',
            'State' => 'required|string|max:191',
            'City' => 'nullable|string|max:191',
            'Title' => 'required|string|max:191',
            'dot' => 'nullable|string|max:191',
            'MStatus' => 'required|string|max:191',
            'ministry' => 'nullable|string|max:191',
            'parishname' => 'required|string|max:191',
            'parishcode' => 'required|string|max:191',
            // // 'thumbnail' => 'required|string |max:191',
            // 'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }
}
