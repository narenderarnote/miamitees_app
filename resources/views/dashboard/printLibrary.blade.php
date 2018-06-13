@extends('spark::layouts.app')

@section('content')
<home :user="user" inline-template>
    <div class="container">

         <div class="store-tabs printfile-tabs">

            <ul class="nav nav-tabs" role="tablist">

               <li class=""><a class="nav-item nav-link active" data-toggle="tab" href="#printfile"  role="tab" aria-controls="printfile"  aria-selected="true">Print Files</a></li>

               <li  ><a class="nav-item nav-link" data-toggle="tab" aria-controls="sourcefile" href="#sourcefile"  aria-selected="false">Source Files</a></li>

            </ul>

            <div class="tab-content">

                <div id="printfile" class="tab-pane active">

                  <div class="choose-itemsearch">

                        <form method="post" action="">

                            <div class="row">

                            <div class="col-md-10 col-xs-12">

                                <div class="input-group">

                                    <input type="search" class="form-control" placeholder="Search files">

                                    <span class="input-group-btn">

                                    <button class="btn btn-primary" type="button">Search</button>

                                    </span>

                                </div>

                            </div>

                            <div class="col-md-2 col-xs-12">

                                <div class="upload-productfile">

                                    <label for="print-files-library-print-library-file" class="btn btn-primary w-150">Upload</label>

                                    <input id="print-files-library-print-library-file" type="file" class="upload-fileinput" name="file">

                                </div>

                            </div>

                            </div>

                        </form>

                    </div>

                    <div class="show-chooseitems">

                        <div class="row">

                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">

                            <div class="text-center img-thumbnail bg-white">

                                <div class="h-150" data-toggle="modal" data-target="#choose-printfile">

                                    <img src="/images/choose1.png" class="img-responsive mxh-150 img-fluid" >

                                </div>

                                <div class="caption">

                                    <div class="ovt-e h-20" data-toggle="tooltip" title="" data-original-title="rtertgret.png">rtertgret.png</div>

                                    <!--<button class="btn btn-default btn-block" type="button" hidden="hidden">

                                    Choose</button>-->

                                </div>

                            </div>

                            </div>

                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">

                            <div class="text-center img-thumbnail bg-white">

                                <div class="h-150">

                                    <img src="/images/choose2.jpg" class="img-responsive mxh-150 img-fluid">

                                </div>

                                <div class="caption">

                                    <div class="ovt-e h-20" data-toggle="tooltip" title="" data-original-title="maxresdefault (2).jpg">maxresdefault (2).jpg</div>

                                    <!--<button class="btn btn-default btn-block" type="button" hidden="hidden">

                                    Choose</button>-->

                                </div>

                            </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div id="sourcefile" class="tab-pane fade">

                    <div class="choose-itemsearch">

                        <form method="post" action="">

                            <div class="row">

                            <div class="col-md-10 col-xs-12">

                                <div class="input-group">

                                    <input type="search" class="form-control" placeholder="Search files">

                                    <span class="input-group-btn">

                                    <button class="btn btn-primary" type="button">Search</button>

                                    </span>

                                </div>

                                </div>

                            <div class="col-md-2 col-xs-12">

                                <div class="upload-productfile">

                                    <label for="print-files-library-print-library-file" class="btn btn-primary w-150">Upload</label>

                                    <input id="print-files-library-print-library-file" type="file" class="upload-fileinput" name="file">

                                </div>

                            </div>

                            </div>

                        </form>

                    </div>

                    <div class="alert alert-warning">

                        <h3 class="text-center">You have no files yet</h3>

                    </div>

                </div>

            </div>

        </div>

    </div>

</home>
@endsection
