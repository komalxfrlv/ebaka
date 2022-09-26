<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Tracker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function show(string $imei): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $tracker = Tracker::all()
            ->where('imei', $imei)
            ->where('user_id', $user['id'])
            ->first();

        if ($tracker == null)
            $positions = [];
        else
            $positions = Position::all()->where('tracker_id', $tracker->id);

        return response()->json($positions, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function hardware(Request $request): Response
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $this->validate($request, [
            'imei' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'balance' => 'required',
        ]);

        $imei = $request->input('imei');
        $balance = doubleval($request->input('balance'));
        $latitude = doubleval($request->input('latitude'));
        $longitude = doubleval($request->input('longitude'));

        if(!($latitude == 0 or $longitude == 0))
        {
            $position = new Position();

            $tracker = DB::table('trackers')->select('id')->where('imei', $imei)->first();
            var_dump($tracker);
            $position->latitude = $latitude;
            $position->longitude = $longitude;
            $position->tracker_id = $tracker->id;

            $position->save();
        }

        DB::table('trackers')->where('imei', $imei)->update(['balance' => $balance]);

        return new Response('done.', Response::HTTP_OK, $headers);
    }
}
