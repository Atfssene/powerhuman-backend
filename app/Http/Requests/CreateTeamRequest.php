<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(){
        return [
            'name'=>'required|string|max:255',
            'icon'=>'nullable|image|mimes:jpeg,png,gif,svg|max:2048',
            'company_id'=>'required|interger|exists:companies,id'
        ];
    }
}
