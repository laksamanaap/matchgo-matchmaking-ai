<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Field;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function index(Request $request)
    {
        $query = Field::query();

        if ($request->boolean('available', true)) {
            $query->where('is_available', true);
        }

        if ($request->filled('start_at') && $request->filled('duration_hours')) {
            $start = Carbon::parse($request->input('start_at'));
            $end = $start->copy()->addHours((int) $request->input('duration_hours'));

            $blockedFieldIds = Booking::query()
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_at', [$start, $end])
                        ->orWhere(function ($query) use ($start, $end) {
                            $query->where('start_at', '<', $start)
                                ->whereRaw('DATE_ADD(start_at, INTERVAL duration_hours HOUR) > ?', [$start]);
                        });
                })
                ->pluck('field_id');

            $query->whereNotIn('id', $blockedFieldIds);
        }

        return response()->json($query->get());
    }

    public function show(Field $field)
    {
        return response()->json($field);
    }
}
