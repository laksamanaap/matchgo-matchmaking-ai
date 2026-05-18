<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'field_id' => ['required', 'exists:fields,id'],
            'match_id' => ['required', 'exists:matches,id'],
            'start_at' => ['required', 'date', 'after_or_equal:now'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:6'],
        ];
    }
}
