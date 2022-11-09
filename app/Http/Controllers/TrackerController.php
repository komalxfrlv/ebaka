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

    public function filters(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $persons = $request->input('workersSelected');
        $cars = $request->input('carsSelected');
        $date_from = $request->input('fromDate');
        $date_to = $request->input('toDate');
        $selectedCity = $request->input('selectedCity');

        /*
        $trackers = Tracker::with(['person', 'car', 'positions'])
            ->whereHas('positions',
                function ($query) use ($request, $date_to, $date_from, $selectedCity) {
                    if($selectedCity !== null)
                        return $query->whereBetween('created_at', [$date_from, $date_to])
                            ->where('address', 'like', '%' . $selectedCity . '%');
                    else
                        return $query->whereBetween('created_at', [$date_from, $date_to]);
                })
            ->where('user_id', $user['id'])
            ->where(function ($query) use ($persons, $cars) {
                $query
                    ->orWhereIn('car_id', $cars)
                    ->orWhereIn('person_id', $persons);
            })
            ->get();
        */

        $trackers = Tracker::with(['person', 'car'])
            ->with(['positions' =>
                function ($query) use ($request, $date_to, $date_from, $selectedCity) {
                        return $query->whereBetween('created_at', [$date_from, $date_to]);
                }
            ])
            ->whereHas('positions',
                function ($query) use ($request, $date_to, $date_from, $selectedCity) {
                    if($selectedCity !== null)
                        return $query->whereBetween('created_at', [$date_from, $date_to])
                            ->where('address', 'like', '%' . $selectedCity . '%');
                    else
                        return $query->whereBetween('created_at', [$date_from, $date_to]);
                })
            ->where('user_id', $user['id'])
            ->where(function ($query) use ($persons, $cars) {
                $query
                    ->orWhereIn('car_id', $cars)
                    ->orWhereIn('person_id', $persons);
            })
            ->get();
        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }


    //TODO: допилить функцию сейчас в городе
    public function nowInCity(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $selectedCity = $request->input('selectedCity');

        $date = (new \DateTime())->modify('-25 minutes');

        $trackers = Tracker::whereHas('positions',
            function ($query) use ($request, $selectedCity, $date) {
                return $query
                    ->where('address', 'like', '%' . $selectedCity . '%')
                    ->where('created_at', '>', $date);
            })
            ->with(['person', 'car'])
            ->where('user_id', $user['id'])
            ->get();

        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function getLowBatteryTrackers(): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $trackers = Tracker::with(['person', 'car'])
            ->where('user_id', $user['id'])
            ->where('power', '<', 25)
            ->get();

        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function getLowBalanceTrackers(): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $trackers = Tracker::with(['person', 'car'])
            ->where('user_id', $user['id'])
            ->where('balance', '<', 250)
            ->get();

        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function getOfflineNowTrackers(): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $date = (new \DateTime())->modify('-24 hours');

        $trackers = Tracker::with(['person', 'car'])
            ->where('user_id', $user['id'])
            ->where('updated_at', '<', $date)
            ->get();

        return response()->json($trackers, 200, $headers, JSON_UNESCAPED_UNICODE);
    }
}
