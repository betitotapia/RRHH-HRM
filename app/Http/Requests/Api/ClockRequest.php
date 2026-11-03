<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ClockRequest extends FormRequest
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
            'event_type'=>['required','in:clock_in,breakfast_start,breakfast_end,lunch_start,lunch_end,clock_out'],
            'station_token'=>['nullable','string'],
            'latitude'=>['nullable','numeric','between:-90,90'],
            'longitude'=>['nullable','numeric','between:-180,180'],
            'photo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'notes'=>['nullable','string','max:500'],
        ];
    }
}
