<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class StoresController extends Controller
{

    public function index(){
        
        return view('dashboard.stores.index');
    }

}
