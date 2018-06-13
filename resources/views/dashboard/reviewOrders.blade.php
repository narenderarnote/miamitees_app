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

                <li><a class="nav-item nav-link"  href="{{URL::route('dashboard.ordersedit')}}" role="tab" aria-controls="printfile">Product</a></li>

                <li><a class="nav-item nav-link" href="{{URL::route('dashboard.ordershipping')}}"  role="tab" aria-controls="shipping">Shipping</a></li>

                <li><a class="nav-item nav-link active" href="{{URL::route('dashboard.ordersreview')}}"  role="tab" aria-controls="revieworder"  aria-selected="true">Review order</a></li>

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

                        <table class="table table-hover">

                            <thead>

                                <tr>

                                    <th>Product</th>

                                    <th>&nbsp;</th>

                                    <th>Print File</th>

                                    <th>Size / Qty</th>

                                    <th>Price</th>

                                    <th>Retail</th>

                                    <th>&nbsp;</th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td class="product-image">

                                        <img alt="" class="img-responsive" src="/images/order1.png">

                                    </td>

                                    <td>

                                        <div class="details">

                                            <div class="mb-15">Shopify T-Shirt - Guys Guy - S Black </div>

                                            <div class="item-details">

                                                <div class="product-detail-widget">

                                                    <div>

                                                        <b>Model:</b>

                                                        Shopify T-Shirt - Guys Guy

                                                    </div>

                                                    <div>

                                                        S

                                                        <small class="text-muted">

                                                            Size

                                                        </small>

                                                    </div>

                                                    <div>

                                                        Black

                                                        <small class="text-muted">

                                                        Color

                                                        </small>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="img-thumbnail bg-white">

                                            <div>

                                                <img alt="" class="mxw-100 img-responsive" src="/images/variant-mockup-80297.jpg">

                                            </div>

                                            <div class="text-center">Print file mockup</div>

                                        </div>

                                    </td>

                                    <td>

                                        <input type="text" name="qty" class="d-inline form-control w-70">

                                    </td>

                                    <td class="price py-3">

                                        <span class="d-inline va-m h-35">

                                        $20.14

                                        </span>

                                    </td>

                                    <td>

                                        <span class="d-inline">$</span>

                                        <input type="text" class="d-inline form-control w-75" placeholder="0.00">

                                    </td>

                                    <td>

                                        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#order-delete">

                                        <i class="fa fa-trash d-b"></i>

                                        Delete...

                                        </button>

                                    </td>
                                
                                </tr>
              
                                <tr>

                                    <td class="product-image">

                                        <img alt="" class="img-responsive" src="/images/order1.png">

                                    </td>

                                    <td>

                                        <div class="details">

                                        <div class="mb-15">Shopify T-Shirt - Guys Guy - S Black </div>

                                        <div class="item-details">

                                            <div class="product-detail-widget">

                                                <div>

                                                    <b>Model:</b>

                                                    Shopify T-Shirt - Guys Guy

                                                </div>

                                                <div>

                                                    S

                                                    <small class="text-muted">

                                                    Size

                                                    </small>

                                                </div>

                                                <div>

                                                    Black

                                                    <small class="text-muted">

                                                    Color

                                                    </small>

                                                </div>

                                            </div>

                                        </div>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="img-thumbnail bg-white">

                                            <div>

                                                <img alt="" class="mxw-100 img-responsive" src="/images/variant-mockup-80297.jpg">

                                            </div>

                                            <div class="text-center">Print file mockup</div>

                                        </div>

                                    </td>

                                    <td>

                                        <input type="text" name="qty" class="d-inline form-control w-70">

                                    </td>

                                    <td class="price py-3">

                                        <span class="d-inline va-m h-35">

                                        $20.14

                                        </span>

                                    </td>

                                    <td>

                                        <span class="d-inline">$</span>

                                        <input type="text" class="d-inline form-control w-75" placeholder="0.00">

                                    </td>

                                    <td>

                                        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#order-delete">

                                            <i class="fa fa-trash d-b"></i>

                                            Delete...

                                        </button>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                        <div class="row mt-5">
	
                            <div class="col-md-4">
                            
                                <h5 class="no-margin-top">Shipping from</h5>
                            
                                <div>
                            
                                    160 SW 12th avenue<br>suite 105<br>Deerfield beach<br>FL 33442<br>United States
                                
                                </div>
                            
                            </div>
                            
                            <div class="col-md-4">
                            
                                <h5 class="no-margin-top">Shipping to <a href="shippingmethod.html">Change</a></h5>
                                <div>
                            
                                    <br>
                                    
                                    <br>
                                    
                                    <br>
                                    
                                    <br>
                                
                                </div>
                            
                            </div>
                            
                            <div class="col-md-4">
                            
                                <h5 class="no-margin-top">
                                
                                    Shipping &amp; Handling <a href="shippingmethod.html">Change</a>
                                    
                                </h5>
                                
                                <p>
                            
                                    Shipping method:
                                
                                    <span class="color-warning">Required</span>
                                
                                </p>
                            
                                <p>Price: 
                                
                                $0.00    
                                
                                </p>
                                
                            </div>
                            
                        </div>

                        <div class="form-group text-center col-md-6 offset-md-3 mt-5">

                            <h4 class="ta-st">Payment method</h4>

                            <div class="alert alert-warning mb-0">

                                You need to add payment method to complete the order  
                                
                            </div>

                            <a href="profilesetting.html" class="btn btn-link">

                                <span>Add payment method</span>
                                
                            </a>

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

                                <a class="btn btn-danger" href="{{route('dashboard.ordershipping')}}">

                                Continue to shipping

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
    
</home>
@endsection
