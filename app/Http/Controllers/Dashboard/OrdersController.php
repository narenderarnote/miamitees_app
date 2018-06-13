<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class OrdersController extends Controller
{
    public function index(){
        
        return view('dashboard.orders');
    }
    
    public function editOrder(){
        
        return view('dashboard.editOrders');
    
    }

    public function shippingOrder(){

        return view('dashboard.shippingOrders');

    }

    public function reviewOrder(){

        return view('dashboard.reviewOrders');

    }

}
