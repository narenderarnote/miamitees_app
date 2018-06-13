@extends('spark::layouts.app')

@section('content')
<home :user="user" inline-template>
    <div class="container">

        <div class="row">

            <div class="col-md-12">

                <div class="jumbotron jumbotron-fluid bg-light orders-stats">
                    
                    <div class="row">

                        <div class="col-md-3">
                            
                            <h2>$0.00</h2>

                            <h4>0 Orders <strong>today</strong>

                            </h4>
                            
                        </div>
                        
                        <div class="col-md-3">

                            <h2>$0.00</h2>

                            <h4>0 Orders <strong>last 7 days</strong>

                            </h4>

                        </div>

                        <div class="col-md-3">

                            <h2>$0.00</h2>

                            <h4>0 Orders <strong>last 28 days</strong>

                            </h4>

                        </div>

                        <div class="col-md-3">

                            <h2>$0.00</h2>

                            <h4>Profit <strong>last 28 days</strong>

                            </h4>

                        </div>

                        <div class="btn btn-link js-popover order-info" data-toggle="popover" data-trigger="hover" data-content="Calculated without direct orders" data-original-title="" title="">

                        <i class="fa fa-info-circle"></i>

                        </div>
        
                    </div>
                    
                </div>

                <div class="order-form">

                    <h4>Orders</h4>

                    <form method="post" action="#" class="row">

                        <div class="col-md-3 col-sm-3">

                        <div class="form-group">

                            <label class="control-label sr-only" for="search">Search</label>

                            <input type="text" name="search" value="fgg" id="search" class="form-control" placeholder="Search">

                        </div>

                        </div>

                        <div class="col-md-3 col-sm-3">

                        <div class="form-group">

                            <label class="control-label sr-only" for="search">All Statuses</label>

                            <select name="status" id="status" class="form-control">

                                <option value="" selected="">All Statuses</option>

                                <option value="draft">Draft</option>

                                <option value="placed">Placed</option>

                                <option value="accepted">Accepted</option>

                                <option value="completed">Completed</option>

                                <option value="cancelled">Cancelled</option>

                            </select>

                        </div>

                        </div>

                        <div class="col-md-3 col-sm-3">

                        <div class="form-group">

                            <label class="control-label sr-only" for="search">All Statuses</label>

                            <select name="status" id="status" class="form-control">

                                <option value="" selected="">All Stores</option>

                                <option value="422">Sandeep_singh</option>

                            </select>

                        </div>

                        </div>

                        <div class="col-md-3 col-sm-3">

                        <div class="form-group pull-left w-150">

                            <label class="control-label sr-only" for="search">All Statuses</label>

                            <select name="per_page" id="per_page" class="form-control">

                                <option value="10" selected="">10 per page</option>

                                <option value="25">25 per page</option>

                                <option value="50">50 per page</option>

                                <option value="100">100 per page</option>

                            </select>

                        </div>

                        <div class="pull-right">

                            <button type="submit" class="btn btn-default">Submit</button>

                        </div>

                        </div>

                    </form>

                    <div class="alert alert-warning d-none">

                        <h3 class="text-center">You have no files yet</h3>

                    </div>

                    <div class="store-tablesection">

                        <table class="table table-hover table-responsive">

                            <thead>

                                <tr>

                                    <th class="w-25">Store</th>

                                    <th class="w-25">Order</th>

                                    <th class="w-25">Date</th>

                                    <th class="">From</th>

                                    <th class="w-75">Status</th>

                                    <th class="w-100">Total</th>

                                    <th class="w-100">Actions</th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td><a href="#js-order-details-modal-1588" data-toggle="modal"> #1588 sandeep_singh </a>

                                    </td>

                                    <td>

                                        <div>direct_1588</div>

                                        <div class="mtb-5"><small class="badge badge-secondary">Direct order</small></div>

                                    </td>

                                    <td>10 minutes ago

                                    </td>

                                    <td></td>

                                    <td>

                                        <div>

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

                                        <span class="d-inline mtb-5">

                                            <div class="badge badge-info">

                                                Fulfillment status: Unfulfilled

                                            </div>

                                        </span>

                                        </div>

                                    </td>

                                    <td>

                                        $0.00	

                                    </td>

                                    <td>

                                        <div class="dropdown">
        
                                            <button class="btn btn-danger dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                
                                                Actions
                                            
                                            </button>
                                            
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                
                                                <a class="dropdown-item" role="menuitem" href="{{URL::route('dashboard.ordersedit')}}">Edit</a>
                                                
                                                <a class="dropdown-item" role="menuitem" href="{{URL::route('dashboard.ordersreview')}}">Order details</a>
                                                
                                                <a class="dropdown-item" href="#">Cancel order</a>
                                                
                                            </div>
                                            
                                        </div>

                                    </td>

                                </tr>

                                <tr>

                                    <td><a href="#js-order-details-modal-1588" data-toggle="modal"> #1588 sandeep_singh </a>

                                    </td>

                                    <td>

                                        <div>direct_1588</div>

                                        <div class="mtb-5"><small class="badge badge-secondary">Direct order</small></div>

                                    </td>

                                    <td>10 minutes ago

                                    </td>

                                    <td></td>

                                    <td>

                                        <div>

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

                                        <span class="d-inline mtb-5">

                                            <div class="badge badge-info">

                                                Fulfillment status: Unfulfilled

                                            </div>

                                        </span>

                                        </div>

                                    </td>

                                    <td>

                                        $0.00	

                                    </td>

                                    <td>

                                        <div class="dropdown">
        
                                            <button class="btn btn-danger dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                
                                                Actions
                                            
                                            </button>
                                            
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                
                                                <a class="dropdown-item" role="menuitem" href="{{URL::route('dashboard.ordersedit')}}">Edit</a>
                                                
                                                <a class="dropdown-item" role="menuitem" href="{{URL::route('dashboard.ordersreview')}}">Order details</a>
                                                
                                                <a class="dropdown-item" href="#">Cancel order</a>
                                                
                                            </div>
                                            
                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</home>
@endsection
