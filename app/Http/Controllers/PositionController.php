<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Tracker;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function show($id): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $tracker = Tracker::all()
            ->where('id', $id)
            ->where('user_id', $user['id'])
            ->first();

        if ($tracker == null)
            $positions = [];
        else
            $positions = Position::all()->where('tracker_id', $tracker->id);

        return response()->json($positions, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function hardware(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $this->validate($request, [
            'imei' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'balance' => 'required',
            'charge' => 'required',
            'is_charging' => 'required'
        ]);

        $imei = $request->input('imei');
        $balance = doubleval($request->input('balance'));
        $charge = doubleval($request->input('charge'));
        $is_charging = doubleval($request->input('is_charging'));
        $latitude = doubleval($request->input('latitude'));
        $longitude = doubleval($request->input('longitude'));


        DB::table('trackers')->where('imei', $imei)->update([
            'balance' => $balance,
            'power' => $charge,
            'is_charging' => $is_charging,
            'updated_at' => (new \DateTime())
        ]);

        $address = null;

        if(!($latitude == 0 or $longitude == 0))
        {
            $position = new Position();

            $tracker = DB::table('trackers')
                ->select('id')
                ->where('imei', $imei)
                ->first();

            $position->latitude = $latitude;
            $position->longitude = $longitude;
            $position->tracker_id = $tracker->id;

            try {
                $url = "https://nominatim.openstreetmap.org/search.php?q=".$latitude.",".$longitude."&polygon_geojson=1&format=json&addressdetails=1";

                $client = new Client();

                if(env('APP_DEPLOY') === 'DEV')
                    $response = $client->request('GET', $url, ['proxy' => 'http://proxy.k-telecom.org:3128']);
                else
                    $response = $client->request('GET', $url);

                $address = json_decode($response->getBody()->getContents())[0]->address;

                $address_str = "";

                if(isset($address->amenity))
                    $address_str .= $address->amenity.", ";
                if(isset($address->road))
                    $address_str .= $address->road.", ";
                if(isset($address->house_number))
                    $address_str .= "дом ".$address->house_number.", ";
                if(isset($address->suburb))
                    $address_str .= $address->suburb.", ";
                if(isset($address->city_district))
                    $address_str .= $address->city_district.", ";
                if(isset($address->village))
                    $address_str .= $address->village.", ";
                if(isset($address->town))
                    $address_str .= $address->town.", ";
                if(isset($address->city))
                    $address_str .= $address->city.", ";
                if(isset($address->state))
                    $address_str .= $address->state.", ";

                $address_str = substr_replace(trim($address_str),'',-1);

                $position->address = $address_str;
                $position->save();
            }
            catch (GuzzleException $e)
            {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }

        $message = $address;

        return response()->json($message, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function getDataFromSideTracker(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        $header = $request->header('Authorization');

        $this->validate($request, [
            'tracker_ID' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if($header === env('JWT_SECRET'))
        {
            $imei = $request->input('tracker_ID');
            $latitude = doubleval($request->input('latitude'));
            $longitude = doubleval($request->input('longitude'));

            DB::table('trackers')->where('imei', $imei)->update([
                'balance' => 0,
                'power' => 100,
                'is_charging' => true,
                'updated_at' => (new \DateTime())
            ]);

            $address = '';

            if(!($latitude == 0 or $longitude == 0))
            {
                $position = new Position();

                $tracker = DB::table('trackers')
                    ->select('id')
                    ->where('imei', $imei)
                    ->first();

                $position->latitude = $latitude;
                $position->longitude = $longitude;
                $position->tracker_id = $tracker->id;

                try {
                    $url = "https://nominatim.openstreetmap.org/search.php?q=".$latitude.",".$longitude."&polygon_geojson=1&format=json&addressdetails=1";

                    $client = new Client();

                    if(env('APP_DEPLOY') === 'DEV')
                        $response = $client->request('GET', $url, ['proxy' => 'http://proxy.k-telecom.org:3128']);
                    else
                        $response = $client->request('GET', $url);

                    $address = json_decode($response->getBody()->getContents())[0]->address;

                    $address_str = "";

                    if(isset($address->amenity))
                        $address_str .= $address->amenity.", ";
                    if(isset($address->road))
                        $address_str .= $address->road.", ";
                    if(isset($address->house_number))
                        $address_str .= "дом ".$address->house_number.", ";
                    if(isset($address->suburb))
                        $address_str .= $address->suburb.", ";
                    if(isset($address->city_district))
                        $address_str .= $address->city_district.", ";
                    if(isset($address->village))
                        $address_str .= $address->village.", ";
                    if(isset($address->town))
                        $address_str .= $address->town.", ";
                    if(isset($address->city))
                        $address_str .= $address->city.", ";
                    if(isset($address->state))
                        $address_str .= $address->state.", ";

                    $address_str = substr_replace(trim($address_str),'',-1);

                    $position->address = $address_str;
                    $position->save();
                }
                catch (GuzzleException $e)
                {
                    return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }

            $message = $address;

            return response()->json($message, 200, $headers, JSON_UNESCAPED_UNICODE);
        }
        else
        {
            return response()->json('Get the fuck outta here, faggot!', 401, $headers, JSON_UNESCAPED_UNICODE);
        }
    }
}
