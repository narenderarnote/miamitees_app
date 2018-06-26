<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\ProductDesignerFile;

class ProductDesignerFilesController extends AdminController
{

    public function delete(Request $request, $id)
    {
        if( ! $designerFile = ProductDesignerFile::find($id)) {
            flash()->success(trans('messages.file_not_found'));
            return redirect()->back();
        }

        $designerFile->file->delete();
        $designerFile->delete();

        flash()->success(trans('messages.file_deleted'));
        return redirect()->back();
    }

}
