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

        $user = auth()->user();

        $people = Person::all()->where('user_id', $user['id']);

        return response()->json($people, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function show($id): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $user = auth()->user();
        $person = Person::all()->where('user_id', $user['id'])->where('id', $id)->first();

        return response()->json($person, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function update(Request $request): JsonResponse
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];

        $this->validate($request, [
            'id' => 'required',
            'name' => 'required',
            'surname' => 'required',
            'patronymic' => 'required',
            'phone' => 'required',
            'address' => 'required',
        ]);

        $person = Person::find($request->input('id'));

        $user = auth()->user();

        $success = ['message' => 'Сотрудник успешно отредактирован!'];
        $error = ['error' => 'Сотрудник не может быть отредактирован'];

        if ($user['id'] === $request->input('user_id')) {
            $person->name = $request->input('name');
            $person->surname = $request->input('surname');
            $person->patronymic = $request->input('patronymic');
            $person->phone = $request->input('phone');
            $person->address = $request->input('address');

            $person->save();

            $person->save();

            return response()->json($success, 200, $headers, JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json($error, 200, $headers, JSON_UNESCAPED_UNICODE);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'surname' => 'required',
            'patronymic' => 'required',
            'phone' => 'required',
            'address' => 'required',
        ]);

        $user = auth()->user();

        $person = new Person();

        $person->name = $request->input('name');
        $person->surname = $request->input('surname');
        $person->patronymic = $request->input('patronymic');
        $person->phone = $request->input('phone');
        $person->address = $request->input('address');
        $person->user_id = $user['id'];

        $person->save();

        return response()->json(['success' => 'Человек успешно добавлен!']);
    }
}
