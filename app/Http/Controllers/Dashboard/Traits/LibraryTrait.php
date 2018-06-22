<?php

namespace App\Http\Controllers\Dashboard\Traits;

use Gate;

use Illuminate\Http\Request;

use App\Models\File;
use App\Models\FileAttachment;

trait LibraryTrait
{

    /**
     * Show the page
     */
    public function index()
    {
        return view('pages.dashboard.library.index');
    }
    
    public function getFile(Request $request, $file_id)
    {
        $file = File::findOrFail($file_id);
        
        return response()->api(null, [
            'file' => $file->transformFull()
        ]);
    }
    
    /**
     * Download file
     */
    public function downloadFile(Request $request, $file_id)
    {
        $file = File::find($file_id);
        
        if (method_exists($file, 'isFileAtachment') && $file->isFileAtachment()) {
            $file = FileAttachment::find($file_id);
        }
        
        $this->authorize('show', $file);
        
        return response()->download($file->file->path(), $file->file->filename);
    }
    
    /**
     * Delete file
     */
    public function deleteFile(Request $request, $file_id)
    {
        $file = File::find($file_id);
        $this->authorize('delete', $file);
    
        $result = $file->delete();
        
        return response()->api(trans('messages.file_deleted'));
    }
    
}
