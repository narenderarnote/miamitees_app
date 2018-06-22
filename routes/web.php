<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('index');
})->name('/');
/**
     * Dashboard - Allowed for anonymous access
     */
Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard'], function () {
    // connect store
        // shopify Application URL
        Route::get('/store/connect/{provider}/initiate', 'StoreConnectController@initiate');

        // shopify Redirection URL
        Route::get('/store/connect/{provider}/confirm', 'StoreConnectController@confirm');

        // redirected to this after confirm
        Route::get('/store/connect/connect-to-account', 'StoreConnectController@connectToAccount');

        // account to connect shop is selected, will connect
        Route::get('/store/connect/connect-to-account/{account_type}', 'StoreConnectController@connectToAccount');
});



Route::group(['middleware' => 'members'], function () {

    Route::group(['prefix' => 'dashboard','namespace'=>'Dashboard','as' => 'dashboard.'], function () {
        Route::get('/', function() {
            return redirect('/dashboard/store');
        });

        /*Store routes*/
        Route::get('/store', 'StoreController@index');

        Route::match(['get'], '/store/sync', ["as" => "sync", "uses" => "StoreController@index"]);
        
        /*files routes*/

        Route::match(['get'], '/library', ["as" => "prints", "uses" => "PrintLibraryController@index"]);
        
        /*Orders Routes*/
        
        Route::match(['get'], '/orders', ["as" => "orders", "uses" => "OrdersController@index"]);

        Route::match(['get','post'], '/orders/edit', ["as" => "ordersedit", "uses" => "OrdersController@editOrder"]);

        Route::match(['get'], '/orders/shipping', ["as" => "ordershipping", "uses" => "OrdersController@shippingOrder"]);

        Route::match(['get'], '/orders/review', ["as" => "ordersreview", "uses" => "OrdersController@reviewOrder"]);

    });

    
});
