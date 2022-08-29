<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PersonController extends Controller
{
    public function index(): JsonResponse
    {
        $headers = [ 'Content-Type' => 'application/json; charset=utf-8' ];

        $people = Person::with('trackers')->get();

        return response()->json($people, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function getAll(): JsonResponse
    {
        $headers = [ 'Content-Type' => 'application/json; charset=utf-8' ];

        $people = Person::all();

        return response()->json($people, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function responsible($status): JsonResponse
    {
        $headers = [ 'Content-Type' => 'application/json; charset=utf-8' ];

        $status = intval($status);

        if ($status == 0)
            $people = Person::all()->where('is_responsible', $status);
        else
            $people = Person::with('trackers')->where('is_responsible', $status)->get();

        return response()->json($people, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request): Response
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $this->validate($request, [
            'name' => 'required',
            'surname' => 'required',
            'phone' => 'required',
        ]);

        $person = new Person();

        $person->name = $request->input('name');
        $person->surname = $request->input('surname');
        $person->phone = $request->input('phone');

        $person->save();

        return new Response('Человек успешно добавлен!', Response::HTTP_CREATED, $headers);
    }
}
