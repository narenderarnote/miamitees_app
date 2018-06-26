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
Auth::routes();
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

//Route::get('/dashboard/store', 'HomeController@index')->name('dashboard.stores');
// order webhooks
Route::get('/dashboard/orders/webhook', 'Dashboard\OrdersController@webhook');

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

        // orders
        Route::get('/orders', 'OrdersController@index');

        Route::get('/orders/create', 'OrdersController@create');

        Route::get('/orders/{order_id}/update', 'OrdersController@updateView');

        //Route::post('/orders/{order_id}/update', 'OrdersController@update');

        Route::get('/orders/{order_id}/shipping', 'OrdersController@shippingView');

        Route::post('/orders/{order_id}/shipping', 'OrdersController@saveShipping');

        Route::get('/orders/{order_id}/review', 'OrdersController@reviewView');

        Route::post('/orders/{order_id}/review', 'OrdersController@saveReview');

        Route::post('/orders/{order_id}/cancel', 'OrdersController@cancel');

        Route::post('/orders/{order_id}/refund', 'OrdersController@refund');

        //Route::post('/orders/{order_id}/add-variant', 'OrdersController@addVariant');

        Route::post('/orders/{order_id}/update-variant/{variant_id}', 'OrdersController@updateVariant');

        //Route::post('/orders/{order_id}/copy-variant/{variant_id}', 'OrdersController@copyVariant');

        Route::post('/orders/{order_id}/attach-variants', 'OrdersController@attachVariants');

        Route::post('/orders/{order_id}/detach-variant/{variant_id}', 'OrdersController@detachVariant');

        Route::get('/orders/{order_id}/view-shopify', 'OrdersController@viewShopify');

        Route::get('/orders/{order_id}/get-with-new-shipping-price', 'OrdersController@getWithNewShippingPrice');
    });

    
});
