@extends('spark::layouts.app')

@section('content')
<home :user="user" inline-template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">    
                <div class="store-tablesection store-tableactive">
                    <div class="container">
                        <h4>Stores</h4>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                <th>Store</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Product Sync</th>
                                <th class="text-center">Settings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a href="#">
                                        sandeep_singh
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">
                                        Active                   
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{route('dashboard.sync')}}" class="sync-add btn btn-default">
                                        Sync / Add Product  
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="storesetting.html" class="sync-add btn btn-default">
                                        Edit                     
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="#">
                                        sandeep_singh
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning">
                                        Pending                  
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{route('dashboard.sync')}}" class="sync-add btn btn-default">
                                        Sync / Add Product         
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="storesetting.html" class="sync-add btn btn-default">
                                        Edit                     
                                        </a>
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
