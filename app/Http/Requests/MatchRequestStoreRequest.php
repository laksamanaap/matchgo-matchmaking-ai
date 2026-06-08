<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MatchRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'opponent_team_id' => ['required', 'exists:teams,id'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_location' => ['required', 'string', 'max:255'],
        ];
    }
}
