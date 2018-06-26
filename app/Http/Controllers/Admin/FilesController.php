<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\File;

class FilesController extends AdminController
{
    
    public function delete(Request $request, $id)
    {
        if( ! $file = File::find($id)) {
            flash()->success(trans('messages.file_not_found'));
            return redirect()->back();
        }
        
        $file->delete();
        
        flash()->success(trans('messages.file_deleted'));
        return redirect()->back();
    }
        
}
