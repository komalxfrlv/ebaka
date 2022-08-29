<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Position;
use App\Models\Tracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TrackerController extends Controller
{
    public function index(int $filter): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        if($filter == 1)
        {
            $trackers = Tracker::with('responsible')
                ->with('car')
                ->with('person')
                ->with('position')
                ->orderByDesc('updated_at')
                ->get()->values();
        }
        else if ($filter == 2)
        {
            $trackers = Tracker::with('responsible')
                ->with('car')
                ->with('person')
                ->with('position')
                ->orderByDesc('balance')
                ->get()->values();
        }
        else
        {
            $trackers = Tracker::with('responsible')
                ->with('car')
                ->with('person')
                ->with('position')
                ->orderByDesc('power')
                ->get()->values();
        }


        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function show(string $imei): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $tracker = Tracker::all()->where('imei', $imei)->first();

        $positions = Position::all()->where('tracker_id', $tracker->id)->sortByDesc('id',)->values()->all();

        return response()->json($positions, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function info(string $imei): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $tracker = Tracker::with('responsible')
            ->with('car')
            ->with('person')
            ->where('imei', $imei)
            ->first();

        return response()->json($tracker, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function filters(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $responsible = $request->input('responsible');
        $persons = $request->input('persons');
        $cars = $request->input('cars');
        $date_from = $request->input('from');
        $date_to = $request->input('to');

        $trackers = Tracker::whereHas('positions',
            function ($query) use ($date_to, $date_from) {
                return $query->whereBetween('created_at', [$date_from, $date_to]);
            })
            ->with(['responsible', 'person', 'car', 'positions'])
            ->where(function ($query) use ($responsible, $persons, $cars) {
                $query->whereIn('responsible_id', $responsible)
                    ->orWhereIn('person_id', $persons)
                    ->orWhereIn('car_id', $cars);
            })
            ->get();
        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];


        $this->validate($request, [
            'imei' => 'required',
            'phone' => 'required',
            'responsible_id' => 'required',
            'tracked' => 'required',
        ]);


        $tracker = new Tracker();

        $responsible_id = $request->input('responsible_id');

        $tracker->imei = $request->input('imei');
        $tracker->phone = $request->input('phone');
        $tracker->responsible_id = $responsible_id;
        $tracker->balance = 0;
        $tracker->power = 0;
        $tracker->is_charging = false;

        if ($request->input('tracked') == 'auto') {
            $tracker->person_id = null;
            $tracker->car_id = $request->input('car_id');
        } else {
            $tracker->car_id = null;
            $tracker->person_id = $request->input('person_id');
        }

        $person = DB::table('people')->find($responsible_id);

        if($person->is_responsible != true)
        {
            DB::table('people')
                ->where('id', $responsible_id)
                ->update(['is_responsible' => true]);
        }

        $tracker->save();

        return new Response('Трекер успешно добавлен!', Response::HTTP_CREATED, $headers);
    }
}
