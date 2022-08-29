<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CarController extends Controller
{
    public function index(): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $cars = Car::all();

        return response()->json($cars, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function show($id): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $cars = Car::all()->find($id);

        return response()->json($cars, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request): Response
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $this->validate($request, [
            'mark' => 'required',
            'model' => 'required',
            'reg_number' => 'required',
            'vin' => 'required',
        ]);

        $car = new Car();

        $car->mark = $request->input('mark');
        $car->model = $request->input('model');
        $car->reg_number = $request->input('reg_number');
        $car->vin = $request->input('vin');

        $car->save();

        return new Response('Автомобиль успешно добавлен!', Response::HTTP_CREATED, $headers);
    }
}
