<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'player_name' => ['required', 'string', 'max:255'],
            'position' => [
                'required',
                'string',
                Rule::in(['Kiper', 'Anchor', 'Flank Kiri', 'Flank Kanan', 'Pivot', 'Universal']),
            ],
            'age' => ['required', 'integer', 'min:13', 'max:50'],
        ];
    }
}
