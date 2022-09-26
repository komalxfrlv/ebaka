<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function PHPUnit\Framework\isEmpty;

class CarController extends Controller
{
    public function index(): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();

        $cars = Car::all()->where('user_id', $user['id']);

        return response()->json($cars, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function show($id): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();
        $cars = Car::all()->where('user_id', $user['id'])->where('id', $id)->first();

        return response()->json($cars, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function update(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $this->validate($request, [
            'id' => 'required',
            'mark' => 'required',
            'model' => 'required',
            'reg_number' => 'required',
            'vin' => 'required',
        ]);

        $car = Car::find($request->input('id'));

        $user = auth()->user();

        $success = ['message' => 'Автомобиль успешно отредактирован!'];
        $error = ['error' => 'Автомобиль не может быть отредактирован'];

        if ($user['id'] === $request->input('user_id')) {
            $car->mark = $request->input('mark');
            $car->model = $request->input('model');
            $car->color = $request->input('color');
            $car->reg_number = $request->input('reg_number');
            $car->vin = $request->input('vin');

            $car->save();

            return response()->json($success, 200, $headers, JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json($error, 200, $headers, JSON_UNESCAPED_UNICODE);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'mark' => 'required',
            'model' => 'required',
            'reg_number' => 'required',
            'vin' => 'required',
        ]);

        $user = auth()->user();

        $car = new Car();

        $car->mark = $request->input('mark');
        $car->model = $request->input('model');
        $car->color = $request->input('color');
        $car->reg_number = $request->input('reg_number');
        $car->vin = $request->input('vin');
        $car->user_id = $user['id'];

        $car->save();

        return response()->json(['success' => 'Автомобиль успешно добавлен!']);
    }
}
