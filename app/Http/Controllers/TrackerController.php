<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Tracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackerController extends Controller
{
    public function index(): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $trackers = Tracker::with('car')
                ->with('person')
                ->with('position')
                ->where('user_id', $user['id'])
                ->orderByDesc('id')
                ->get()->values();

        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function show($id): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $tracker = Tracker::with('car')
            ->with('person')
            ->with('positions')
            ->where('id', $id)
            ->where('user_id', $user['id'])
            ->first();

        return response()->json($tracker, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request): Response
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        $request->toArray();
        $this->validate($request, [
            'imei' => 'required',
            'phone' => 'required',
            'tracked' => 'required',
        ]);

        $user = auth()->user();

        $tracker = new Tracker();

        $tracker->imei = $request->input('imei');
        $tracker->phone = $request->input('phone');
        $tracker->user_id = $user['id'];
        $tracker->balance = null;
        $tracker->power = null;
        $tracker->is_charging = false;

        if ($request->input('tracked') == 'auto') {
            $tracker->person_id = null;
            $tracker->car_id = $request->input('car_id');
        } else {
            $tracker->car_id = null;
            $tracker->person_id = $request->input('person_id');
        }

        $tracker->save();

        return new Response('Трекер успешно добавлен!', Response::HTTP_CREATED, $headers);
    }

    public function update(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $this->validate($request, [
            'id' => 'required',
            'imei' => 'required',
            'phone' => 'required',
            'tracked' => 'required',
        ]);

        $success = ['message' => 'Автомобиль успешно отредактирован!'];
        $error = ['error' => 'Автомобиль не может быть отредактирован'];

        $tracker = Tracker::find($request->input('id'));

        $user = auth()->user();

        if($tracker->user_id == $user['id'])
        {
            $tracker->imei = $request->input('imei');
            $tracker->phone = $request->input('phone');


            if ($request->input('tracked') == 'auto') {
                $tracker->person_id = null;
                $tracker->car_id = $request->input('car_id');
            } else {
                $tracker->car_id = null;
                $tracker->person_id = $request->input('person_id');
            }

            $tracker->save();

            return response()->json($success, 200, $headers, JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json($error, 200, $headers, JSON_UNESCAPED_UNICODE);
        }
    }

    /******************************************************************************************************************
     ******************************************************************************************************************
     *****************************************************************************************************************/

    public function positions($id): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $tracker = Tracker::all()
            ->where('id', $id)
            ->where('user_id', $user['id'])
            ->first();

        $positions = Position::all()
            ->where('tracker_id', $tracker->id)
            ->sortByDesc('id',)
            ->values()->all();

        return response()->json($positions, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function filters(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $persons = $request->input('workersSelected');
        $cars = $request->input('carsSelected');
        $date_from = $request->input('fromDate');
        $date_to = $request->input('toDate');

        $trackers = Tracker::whereHas('positions',
            function ($query) use ($request, $date_to, $date_from) {
                if($request->input('selectedCity') !== null)
                    return $query->whereBetween('updated_at', [$date_from, $date_to])
                        ->where('address', 'like', '%' . $request->input('selectedCity') . '%');
                else
                    return $query->whereBetween('updated_at', [$date_from, $date_to]);
            })
            ->with(['person', 'car', 'positions'])
            ->where('user_id', $user['id'])
            ->where(function ($query) use ($persons, $cars) {
                $query
                    ->orWhereIn('car_id', $cars)
                    ->orWhereIn('person_id', $persons);
            })
            ->get();
        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

}
