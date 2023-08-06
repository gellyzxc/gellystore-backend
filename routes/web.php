<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

$router->post('register', 'UserController@register');
$router->post('login', 'UserController@login');
$router->get('/code/{token}', 'UserController@acceptLink');

$router->group(['prefix' => 'item'], function () use ($router) {
    $router->get('list[/{count}]', 'ItemsController@index');
    $router->get('{id}', 'ItemsController@view');
    $router->get('shop/{id}/items', 'ShopsController@getItemsInShop');

});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('me', 'UserController@index');

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('cart', 'UserController@showCart');
        $router->post('cart', 'UserController@addToCart');
        $router->delete('cart/{ids}', 'UserController@deleteCart');

        $router->post('order', 'UserController@createOrder');
        $router->get('order', 'UserController@listOrders');

    });

    $router->group(['middleware' => 'roles:seller,support'], function () use ($router) {

        $router->group(['prefix' => 'shop'], function () use ($router) {
            $router->post('create', 'ShopsController@create');
            $router->get('list', 'ShopsController@listMy');
            $router->delete('delete/{id}', 'ShopsController@deleteShop');


            $router->group(['prefix' => 'item'], function () use ($router) {
                $router->post('create', 'ItemsController@create');
                $router->delete('/{id}', 'ItemsController@delete');
                $router->get('metric/{id}', 'ItemsController@metrics');
            });
        });

    });

});



// костыли с файлами для люмпена

$router->get('image/{filename}', function (Request $request, $filename)
{
    if (!file_exists("./storage/app/public/". $filename)) {
        return response()->json(["message" => "not found"], 404);
    }

    $file = scandir ("./storage/app/public/". $filename);
    $result = Storage::disk('public')->get($filename.'/'.$file[2]);
    // ->header('Content-Disposition', 'inline; filename="file.'. $request->ext . '')
    return response($result)->header('Content-Type', 'image/png');
});

$router->get('attachement/{filename}', function (Request $request, $filename)
{
    if (!file_exists("./storage/app/public/". $filename)) {
        return response()->json(["message" => "not found"], 404);
    }

    $file = scandir ("./storage/app/public/". $filename);
    $result = Storage::disk('public')->get($filename.'/'.$file[2]);
    return response($result)->header('Content-Type', 'application/octet-stream')->header('Content-Disposition', 'attachement; filename="file.'. $request->ext . '');
});
