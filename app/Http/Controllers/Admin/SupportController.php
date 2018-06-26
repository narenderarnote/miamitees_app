<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use DataForm;

use App\Http\Controllers\Admin\AdminController;
use App\Models\SupportRequest;

class SupportController extends AdminController
{
    use \App\Http\Controllers\Admin\Traits\RapydControllerTrait;
    
    protected function getModelForAdd()
    {
        return new SupportRequest();
    }
    
    protected function getModelForEdit($id)
    {
        return SupportRequest::find($id);
    }
    
    protected function getModelForDelete($id)
    {
        return SupportRequest::find($id);
    }
    
    protected function grid(Request $request, $model, $title)
    {
        $grid = \DataGrid::source($model);
        
        $grid->add('subject', trans('labels.subject'), false);
        $grid->add('user.email', trans('labels.user'), false);
        
        $grid->add('status', trans('labels.status'), true)
            ->cell(function($value, $row) {
                return $row->getStatusName();
            });
        
        
        $grid->add('updated_created', trans('labels.updated_created'))
            ->cell(function($value, $row) {
                return '
                    <time
                        datetime="'.$row->updatedAtTZ().'"
                        title="'.$row->updatedAtTZ().'"
                        data-format="">
                        '.$row->updatedAtTZ().'
                    </time>
                    /
                    <time
                        datetime="'.$row->createdAtTZ().'"
                        title="'.$row->createdAtTZ().'"
                        data-format="">
                        '.$row->createdAtTZ().'
                    </time>
                ';
            });
        
        $grid->add('id', trans('labels.actions'))
            ->cell(function($value, $row) {
                return '
                    <a class="btn btn-xs btn-primary" href="'.url('/admin/support/'.$value.'/show').'">
                        <i class="fa fa-arrow-right"></i>
                        '.trans('actions.view').'
                    </a>
                ';
            });
     
        $grid->orderBy('created_at','desc');
        $grid->paginate(10);
        
        return view('admin.pages.default.grid', [
            'title' => $title,
            'grid' => $grid
        ]);
    }
    
    public function all() {}
    
    public function tickets(Request $request)
    {
        $model = SupportRequest::getAllTicketsQuery();
        return $this->grid($request, $model, trans('labels.tickets'));
    }
    
    public function newTickets(Request $request)
    {
        $model = SupportRequest::getNewTicketsQuery();
        return $this->grid($request, $model, trans('labels.new_tickets'));
    }
    
    public function refunds(Request $request)
    {
        $model = SupportRequest::getAllRefundsQuery();
        return $this->grid($request, $model, trans('labels.refund_requests'));
    }
    
    public function newRefunds(Request $request)
    {
        $model = SupportRequest::getNewRefundsQuery();
        return $this->grid($request, $model, trans('labels.new_refund_requests'));
    }
    
    public function pendingTickets(Request $request)
    {
        $model = SupportRequest::getPendingTicketsQuery();
        return $this->grid($request, $model, trans('labels.pending_tickets'));
    }
    
    /**
     * Add/edit form
     */
    protected function form($form) {}
    
    public function show(Request $request, $id)
    {
        $model = SupportRequest::find($id);
        
        if (!$model) {
            abort(404);
        }
        
        if ($model->isNew()) {
            $model->pending();
        }
        
        if ($model->isRefund()) {
            $view = 'admin.pages.support.refund';
        }
        else {
            $view = 'admin.pages.support.show';
        }
        
        return view($view, [
            'title' => trans('labels.ticket'),
            'model' => $model
        ]);
    }
}
