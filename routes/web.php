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
    $router->post('/user', 'AuthController@user');
    $router->post('/hardware-data', 'PositionController@hardware');
    $router->post('/side-tracker', 'PositionController@getDataFromSideTracker');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/logout', 'AuthController@logout');

        $router->group(['prefix' => 'people'], function () use ($router) {
            // People
            $router->get('/', 'PersonController@index');
            $router->post('/', 'PersonController@store');
            $router->get('/{id}', 'PersonController@show');
            $router->put('/', 'PersonController@update');
        });

        $router->group(['prefix' => 'cars'], function () use ($router) {
            // Cars
            $router->get('/', 'CarController@index');
            $router->get('/{id}', 'CarController@show');
            $router->post('/', 'CarController@store');
            $router->put('/', 'CarController@update');
        });

        $router->group(['prefix' => 'trackers'], function () use ($router) {
            // Trackers
            $router->get('/', 'TrackerController@index');
            $router->get('/show/{id}', 'TrackerController@show');
            $router->post('/', 'TrackerController@store');
            $router->put('/', 'TrackerController@update');

            $router->get('/positions/{id}', 'TrackerController@positions');
            $router->post('/filters', 'TrackerController@filters');
            $router->get('/low-battery', 'TrackerController@getLowBatteryTrackers');
            $router->get('/low-balance', 'TrackerController@getLowBalanceTrackers');
            $router->get('/now-offline', 'TrackerController@getOfflineNowTrackers');
            $router->post('/now-in-city', 'TrackerController@nowInCity');
        });

        $router->group(['prefix' => 'positions'], function () use ($router) {
            // Positions
            $router->get('/imei/{imei}', 'PositionController@show');
        });
    });
});
