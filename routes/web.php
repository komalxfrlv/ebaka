<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/logout', 'AuthController@logout');

        $router->group(['prefix' => 'people'], function () use ($router) {
            // People
            $router->get('/', 'PersonController@index');
            $router->get('/get-all', 'PersonController@getAll');
            $router->get('/responsible/{status}', 'PersonController@responsible');
            $router->post('/create', 'PersonController@store');


        });

        $router->group(['prefix' => 'trackers'], function () use ($router) {
            // Trackers
            $router->get('/{filter}', 'TrackerController@index');
            $router->get('/imei/{imei}', 'TrackerController@show');
            $router->get('/imei-info/{imei}', 'TrackerController@info');
            $router->post('/filters', 'TrackerController@filters');
            $router->post('/create', 'TrackerController@store');
            $router->post('/data', 'TrackerController@hardware');
        });

        $router->group(['prefix' => 'positions'], function () use ($router) {
            // Positions
            $router->get('/imei/{imei}', 'PositionController@show');
            $router->post('/hardware', 'PositionController@hardware');
        });

        $router->group(['prefix' => 'cars'], function () use ($router) {
            // Cars
            $router->get('/', 'CarController@index');
            $router->get('/id/{id}', 'CarController@show');
            $router->post('/create', 'CarController@store');
        });
    });
});
