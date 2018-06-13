@extends('spark::layouts.app')

@section('content')
<home :user="user" inline-template>
    
    <div class="container">

        <div class="row mb-20 mt-20">

            <div class="col-xs-12">

                <a href="orders.html">

                <i class="fa fa-arrow-left"></i>

                Back to all orders        

                </a>

            </div>

        </div>

        <div class="row mb-20">

            <div class="col-xs-12">

                <div class="order-header-widget mt-3 mb-3">

                    <div class="d-inline">

                    direct_1589

                    </div>

                    <div class="d-inline">|</div>

                    <div class="d-inline">

                    sandeep_singh

                    </div>

                    <div class="d-inline">|</div>

                    <div class="d-inline">

                    <time datetime="2018-05-21T02:54:34-04:00" title="May 21st 2018, 12:24:34 pm" data-app-time="May 21, 2018 02:54am" data-format=""><span>2 minutes ago</span></time>

                    </div>

                </div>

            </div>

        </div>

        <div class="store-tabs printfile-tabs">

            <ul class="nav nav-tabs">

                <li><a class="nav-item nav-link"  href="{{URL::route('dashboard.ordersedit')}}" aria-controls="printfile">Product</a></li>

                <li><a class="nav-item nav-link active" href="{{URL::route('dashboard.ordershipping')}}"  aria-controls="shipping" aria-selected="true">Shipping</a></li>

                <li><a class="nav-item nav-link" href="{{URL::route('dashboard.ordersreview')}}" aria-controls="revieworder">Review order</a></li>

                <li class="order-badge">

                    <span class="d-inline">

                    <div class="badge badge-secondary">

                        Order status: Draft

                    </div>

                    </span>

                    <span class="d-inline">

                    <div class="badge badge-secondary">

                        Payment status: Pending

                    </div>

                    </span>

                    <span class="d-inline">

                    <div class="badge badge-info">Fulfillment status: Unfulfilled

                    </div>

                    </span>

                </li>

            </ul>

            <div class="tab-content">

                <div id="product" class="tab-pane active">

                    <div class="store-tablesection">

                        <form method="post" action="#">
                            
                            <div class="alert alert-warning">
                               
                                Please check the order to set correct shipping method       
                            
                            </div>

                            <h4>Shipping Address</h4>

                            <div class="row">

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="first_name">First Name</label>
                                        
                                        <input type="text" name="first_name" value="" id="first_name" class="form-control">
                                   
                                    </div>

                                </div>

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="last_name">Last Name</label>
                                       
                                        <input type="text" name="last_name" value="" id="last_name" class="form-control">
                                   
                                    </div>
                                
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="first_name">Address 1</label>
                                       
                                        <input type="text" name="address-1" value="" id="address-1" class="form-control">
                                   
                                    </div>
                                
                                </div>
                                
                                <div class="col-xs-12 col-sm-6">
                                    
                                    <div class="form-group">

                                        <label class="control-label" for="last_name">Address 2</label>

                                        <input type="text" name="address-2" value="" id="address-2" class="form-control">
                                    
                                    </div>
                                
                                </div>
                            
                            </div>
                            <div class="row">

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="first_name">City</label>

                                        <input type="text" name="city" value="" id="city" class="form-control">

                                    </div>

                                </div>

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="last_name">Province</label>

                                        <input type="text" name="province" value="" id="province" class="form-control">

                                    </div>
                                    
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="first_name">Country</label>

                                        <select name="country_code" id="country_code" class="form-control" value="">

                                            <option value="AF">Afghanistan</option>

                                            <option value="AX">Åland Islands</option>

                                        </select>

                                    </div>

                                </div>

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="last_name">Zip Code</label>

                                        <input type="text" name="zip_code" value="" id="zip_code" class="form-control">

                                    </div>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="first_name">Company</label>

                                        <input type="text" name="company" value="" id="company" class="form-control">

                                    </div>

                                </div>

                                <div class="col-xs-12 col-sm-6">

                                    <div class="form-group">

                                        <label class="control-label" for="last_name">Phone</label>

                                        <input type="text" name="phone" value="" id="phone" class="form-control">

                                    </div>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12 form-group">

                                    <h5>Select shipping method</h5>

                                    <div class="btn-group btn-group-toggle" role="group" data-toggle="buttons">
  	
                                        <label class="btn btn-secondary active">
                                            
                                            <input type="radio" name="options" id="option1" autocomplete="off" checked>  $12.00 First Class 
                                        
                                        </label>

                                        <label class="btn btn-secondary">
                                            
                                            <input type="radio" name="options" id="option2" autocomplete="off"> $20.00 Priority Mail  

                                        </label>

                                    </div>

                                    <input type="text" class="d-none" name="shipping_method">

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12">

                                    <div class="col-md-6 offset-md-3 mt-3">

                                        <table class="table mt-15">

                                            <tbody>

                                                <tr>

                                                    <td class="fw-b tt-u">Subtotal</td>

                                                    <td class="ta-nd">
                                                        $20.14
                                                    </td>

                                                </tr>

                                                <tr>

                                                    <td class="fw-b tt-u">Shipping &amp; Handling</td>

                                                    <td class="ta-nd">
                                                        $0.00
                                                    </td>

                                                </tr>

                                                <tr>

                                                    <td class="fw-b tt-u">Total</td>

                                                    <td class="ta-nd">
                                                        $20.14
                                                    </td>

                                                </tr>

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12 text-center">

                                    <a class="btn btn-danger" href="{{URL::route('dashboard.ordersreview')}}">
                                        Continue to review
                                    </a>

                                </div>

                            </div>
                            
                        </form>
                        
                    </div>

                </div>

            </div>

        </div>

    </div>
    
</home>
@endsection
