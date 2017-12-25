<?php

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

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['namespace' => 'Api', 'prefix' => '/api'], function () use ($router) {
    $router->get('/doc', 'DocumentationController@getDocumentation');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/user/change_pin', 'UserController@postChangePin');
        $router->post('/user/enable_premium_news', 'UserController@postEnablePremiumNews');

        $router->group(['prefix' => 'wallet'], function () use ($router) {
            $router->post('/balance', 'WalletController@postBalance');
            $router->post('/new_address', 'WalletController@postNewAddress');
            $router->post('/last_address', 'WalletController@postLastAddress');
            $router->post('/history', 'WalletController@postHistory');
            $router->post('/spend', 'WalletController@postSpend');
        });
    });

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->post('/verification', 'UserController@postVerification');
        $router->post('/create', 'UserController@postCreate');
        $router->post('/login', 'UserController@postLogin');
    });
});

$router->get('/random-words', function(){
    return [
        'words' =>  get_recovery_phrase()
    ];
});