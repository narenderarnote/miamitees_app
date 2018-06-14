@extends('spark::layouts.app')

@section('content')
<home :user="user" inline-template>
    
    <div class="container">

        <div class="alert alert-danger d-none">

            <button type="button" class="close pull-right" data-dismiss="alert" aria-hidden="true">×</button>

            Security token invalid. Please try again.

        </div>

        <nav aria-label="breadcrumb">
            
            <ol class="breadcrumb bg-light">

                <li class="breadcrumb-item"><a href="{{route('/')}}">Stores</a></li>

                <li class="breadcrumb-item active">Sandeep_singh</li>

            </ol>
        </nav>

        <div class="store-setting">

            <a href="storesetting.html" class="pull-right">

                <span class="fa fa-cog"></span>

            Settings                

            </a>

        </div>

        <h5 class="mt-5">PreslavDemo</h5>

        <div class="card bg-light py-3">

            <div class="card-body text-center">
            
                <button type="button" class="btn btn-warning btn-lg text-white px-5 py-1" data-toggle="modal" data-target="#addproduct-ist">

                    Add Product                   

                </button>

            </div>

        </div>

        <div class="store-tabs printfile-tabs">

            <ul class="nav nav-tabs">

                <li><a class="nav-item nav-link active" data-toggle="tab" href="#home" role="tab" aria-controls="sync"  aria-selected="true">Synced (0)</a></li>

                <li><a class="nav-item nav-link" href="#menu1" data-toggle="tab" role="tab" aria-controls="shipping">Pending (6)</a></li>
             
            </ul>

            <div class="tab-content">

                <div id="home" class="tab-pane active">

                    <div class="store-tablesection">

                        <table class="table table-hover">

                            <thead>

                                <tr>

                                    <th colspan="2">Product</th>

                                    <th colspan="4">Variants</th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr hidden>

                                    <td colspan="5" class="ta-c">

                                        <div class="fs-i">No products found.</div>

                                    </td>

                                </tr>

                                <tr>

                                    <td class="product-tableimage">

                                        <img class="img-responsive" src="/images/variant-mockup-43339.jpg" alt="">

                                    </td>

                                    <td colspan="1">

                                        <div class="product-name fw-b">

                                            Shopify Tank Top - Guys Guy

                                        </div>

                                        <div class="external-id">

                                            #10765110408

                                        </div>

                                    </td>

                                    <td class="variant-column">

                                        <div class="mtb-5">

                                            <b>Size</b>:

                                            <span class="badge badge-secondary">S</span>

                                            <span class="badge badge-secondary">M</span>

                                            <span class="badge badge-secondary">L</span>

                                            <span class="badge badge-secondary">XL</span>

                                            <span class="badge badge-secondary">2XL</span>

                                        </div>

                                        <div class="mtb-5">

                                            <b>Color</b>:

                                            <span title="Kelly Green" class="d-inline-block w-15 h-15 bd" style="background-color: #278835"></span>

                                            <span title="Purple Rush" class="d-inline-block w-15 h-15 bd" style="background-color: #5d1399"></span>

                                            <span title="Red" class="d-inline-block w-15 h-15 bd" style="background-color: #c01c34"></span>

                                        </div>

                                        <div class="sync-status text-center">

                                        <div class="synced d-inline-block">

                                            <div class="value">15</div>

                                            <div class="caption">Synced</div>

                                        </div>

                                        <div class="d-inline-block">|</div>

                                        <div class="notSynced d-inline-block">

                                            <div class="value">0</div>

                                            <div class="caption">Not Synced</div>

                                        </div>

                                        <div class="d-inline-block">|</div>

                                        <div class="ignored d-inline-block">

                                            <div class="value">0</div>

                                            <div class="caption">Ignored</div>

                                        </div>

                                        </div>

                                    </td>

                                    <td colspan="2" class="store-delete text-center">

                                        <a href="editproduct.html" class="btn btn-primary btn-block px-5">

                                        Edit                       

                                        </a>

                                    </td>

                                    <td class="text-center store-last">

                                        <form method="POST" action="#">

                                            <input type="hidden" name="_token" value="Hy7GXzQk6gZ5vBLM2xwVprPrgkWtmPdgayEpsMyF">

                                            <button type="submit" class="btn btn-white btn-block px-5">Ignore Product</button>

                                        </form>

                                    </td>

                                </tr>

                                <tr>

                                    <td class="product-tableimage">

                                        <img class="img-responsive" src="/images/variant-mockup-43339.jpg" alt="">

                                    </td>

                                    <td colspan="1">

                                        <div class="product-name fw-b">

                                        Shopify Tank Top - Guys Guy

                                        </div>

                                        <div class="external-id">

                                        #10765110408

                                        </div>

                                    </td>

                                    <td class="variant-column">

                                        <div class="mtb-5">

                                            <b>Size</b>:

                                            <span class="badge badge-secondary">S</span>

                                            <span class="badge badge-secondary">M</span>

                                            <span class="badge badge-secondary">L</span>

                                            <span class="badge badge-secondary">XL</span>

                                            <span class="badge badge-secondary">2XL</span>

                                        </div>

                                        <div class="mtb-5">

                                            <b>Color</b>:

                                            <span title="Kelly Green" class="d-inline-block w-15 h-15 bd" style="background-color: #278835"></span>

                                            <span title="Purple Rush" class="d-inline-block w-15 h-15 bd" style="background-color: #5d1399"></span>

                                            <span title="Red" class="d-inline-block w-15 h-15 bd" style="background-color: #c01c34"></span>

                                        </div>

                                        <div class="sync-status text-center">

                                            <div class="synced d-inline-block">

                                                <div class="value">15</div>

                                                <div class="caption">Synced</div>

                                            </div>

                                            <div class="d-inline-block">|</div>

                                            <div class="notSynced d-inline-block">

                                                <div class="value">0</div>

                                                <div class="caption">Not Synced</div>

                                            </div>

                                            <div class="d-inline-block">|</div>

                                            <div class="ignored d-inline-block">

                                                <div class="value">0</div>

                                                <div class="caption">Ignored</div>

                                            </div>

                                        </div>

                                    </td>

                                    <td colspan="2" class="store-delete text-center">

                                        <a href="#" class="btn btn-primary btn-block px-5">

                                            Edit                       

                                        </a>

                                    </td>

                                    <td class="text-center">

                                        <form method="POST" action="#">

                                            <input type="hidden" name="_token" value="Hy7GXzQk6gZ5vBLM2xwVprPrgkWtmPdgayEpsMyF">

                                            <button type="submit" class="btn  btn-block px-5 btn-white">Ignore Product</button>

                                        </form>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

                <div id="menu1" class="tab-pane fade">

                    <div class="store-tablesection">

                        <table class="table table-hover">

                            <thead>

                                <tr>

                                    <th colspan="2">Product</th>

                                    <th colspan="4">Variants</th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td class="product-tableimage">

                                        <img class="img-responsive" src="/images/variant-mockup-43219.jpg" alt="">

                                    </td>

                                    <td colspan="1">

                                        <div class="product-name fw-b">

                                            Shopify - T-Shirt (Cost Efficient) Guy

                                        </div>

                                        <div>

                                            Moderation Status:

                                            <div class="badge badge-secondary">Auto Approved</div>

                                        </div>

                                    </td>

                                    <td class="variant-column">

                                        <div class="mtb-5">

                                            <b>Size</b>:

                                            <span class="badge badge-secondary">XS</span>

                                            <span class="badge badge-secondary">S</span>

                                            <span class="badge badge-secondary">M</span>

                                            <span class="badge badge-secondary">L</span>

                                            <span class="badge badge-secondary">XL</span>

                                            <span class="badge badge-secondary">2XL</span>

                                            <span class="badge badge-secondary">3XL</span>

                                        </div>

                                        <div class="mtb-5">

                                            <b>Color</b>:

                                            <span title="CHERRY RED" class="d-inline-block w-15 h-15 bd" style="background-color: #d10134"></span>

                                            <span title="Daisy" class="d-inline-block w-15 h-15 bd" style="background-color: #fed201"></span>

                                            <span title="DARK HEATHER" class="d-inline-block w-15 h-15 bd" style="background-color: #4b4a50"></span>

                                            <span title="HEATHER RED" class="d-inline-block w-15 h-15 bd" style="background-color: #e53e5c"></span>

                                            <span title="HEATHER SAPPHIRE" class="d-inline-block w-15 h-15 bd" style="background-color: #44c8fe"></span>

                                            <span title="KIWI" class="d-inline-block w-15 h-15 bd" style="background-color: #bdd357"></span>

                                            <span title="Maroon" class="d-inline-block w-15 h-15 bd" style="background-color: #6c1f3f"></span>

                                            <span title="NATURAL" class="d-inline-block w-15 h-15 bd" style="background-color: #dcd6c1"></span>

                                            <span title="Azalea" class="d-inline-block w-15 h-15 bd" style="background-color: #f27eb2"></span>

                                        </div>

                                    </td>

                                    <td class="text-center store-delete">

                                    </td>

                                    <td class="text-center store-delete">

                                        <button type="submit" class="btn btn-danger btn-block js-confirm" data-toggle="modal" data-target="#delete-product">Delete</button>

                                    </td>

                                    <td class="text-center store-last">

                                        <button disabled="disabled" type="button" class="btn btn-danger px-4 btn-block disabled">

                                        Queued for sync                                

                                        </button>

                                    </td>

                                </tr>

                                <tr>

                                    <td class="product-tableimage">

                                        <img class="img-responsive" src="/images/variant-mockup-43219.jpg" alt="">

                                    </td>

                                    <td colspan="1">

                                        <div class="product-name fw-b">

                                            Shopify - T-Shirt (Cost Efficient) Guy

                                        </div>

                                        <div>

                                            Moderation Status:

                                            <div class="badge badge-secondary">Auto Approved</div>

                                        </div>

                                    </td>

                                    <td class="variant-column">

                                        <div class="mtb-5">

                                            <b>Size</b>:

                                            <span class="badge badge-secondary">XS</span>

                                            <span class="badge badge-secondary">S</span>

                                            <span class="badge badge-secondary">M</span>

                                            <span class="badge badge-secondary">L</span>

                                            <span class="badge badge-secondary">XL</span>

                                            <span class="badge badge-secondary">2XL</span>

                                            <span class="badge badge-secondary">3XL</span>

                                        </div>

                                        <div class="mtb-5">

                                            <b>Color</b>:

                                            <span title="CHERRY RED" class="d-inline-block w-15 h-15 bd" style="background-color: #d10134"></span>

                                            <span title="Daisy" class="d-inline-block w-15 h-15 bd" style="background-color: #fed201"></span>

                                            <span title="DARK HEATHER" class="d-inline-block w-15 h-15 bd" style="background-color: #4b4a50"></span>

                                            <span title="HEATHER RED" class="d-inline-block w-15 h-15 bd" style="background-color: #e53e5c"></span>

                                            <span title="HEATHER SAPPHIRE" class="d-inline-block w-15 h-15 bd" style="background-color: #44c8fe"></span>

                                            <span title="KIWI" class="d-inline-block w-15 h-15 bd" style="background-color: #bdd357"></span>

                                            <span title="Maroon" class="d-inline-block w-15 h-15 bd" style="background-color: #6c1f3f"></span>

                                            <span title="NATURAL" class="d-inline-block w-15 h-15 bd" style="background-color: #dcd6c1"></span>

                                            <span title="Azalea" class="d-inline-block w-15 h-15 bd" style="background-color: #f27eb2"></span>

                                        </div>

                                    </td>

                                    <td class="text-center store-delete">

                                    </td>

                                    <td class="text-center store-delete">

                                        <button type="submit" class="btn btn-danger btn-block js-confirm px-4" data-toggle="modal" data-target="#delete-product">Delete</button>

                                    </td>

                                    <td class="text-center store-last">

                                        <button disabled="disabled" type="button" class="btn btn-danger btn-block disabled">

                                        Queued for sync                                

                                        </button>

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
