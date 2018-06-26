<?php

namespace App\Http\Controllers\Admin;

use DataForm;
use Input;
use View;

use Illuminate\Http\Request;

use App\Models\ProductVariant;
use App\Models\File;
use App\Models\FileAttachment;

use App\Http\Requests\Admin\ProductVariant\ProductVariantSaveFormRequest;

class ProductVariantsController extends AdminController
{
    use \App\Http\Controllers\Admin\Traits\RapydControllerTrait;
    
    protected function getModelForAdd()
    {
        return new ProductVariant();
    }
    
    protected function getModelForEdit($id)
    {
        return ProductVariant::find($id);
    }
    
    protected function getModelForDelete($id)
    {
        return ProductVariant::find($id);
    }
    
    /**
     * All patients
     */
    public function all(Request $request)
    {
        $model = ProductVariant::with('user')->with('product');
        
        $filter = \DataFilter::source($model);
        $filter->add('name', trans('labels.name'), 'text');
        $filter->add('moderation_status', trans('labels.moderation_status'), 'select')
            ->options(['' => trans('labels.status')] + ProductVariant::listModerationStatuses());
        $filter->add('user.email', trans('labels.user'), 'tags');
        $filter->add('product.name', trans('labels.product'), 'tags');
        $filter->submit(trans('actions.search'));
        $filter->reset(trans('actions.reset'));
        $filter->build();
        
        $grid = \DataGrid::source($filter);

        $grid->add('preview', trans('labels.preview'))
            ->cell(function($value, $row) {
                if ($row->preview) {
                    return '
                        <img class="h-50" src="'.$row->preview->url('thumb').'" alt="" />
                    ';
                }
                else {
                    return '
                        <img class="h-50" src="'.url('img/placeholders/placeholder-300x200.png').'" alt="" />
                    ';
                }
            });
        $grid->add('user.email', trans('labels.user'), false)
            ->cell(function($value, $row) {
                return '
                    <a class="btn btn-default btn-xs" href="'.url('/admin/users/'.$row->user->id.'/edit').'">
                        <i class="fa fa-edit"></i>
                        '.$row->user->email.'
                    </a>
                ';
            });
        $grid->add('user.product', trans('labels.product'), false)
            ->cell(function($value, $row) {
                return '
                    <a class="btn btn-default btn-xs" href="'.url('/admin/products?product_name='.$row->product->id.'&search=1').'">
                        <i class="fa fa-shopping-cart"></i>
                        '.$row->product->name.', '.trans('labels.store').': '.$row->product->store->name.'
                    </a>
                ';
            });
        $grid->add('name', trans('labels.name'), false)
            ->cell(function($value, $row) {
                return $row->getFullTitle();
            });
        $grid->add('details', trans('labels.details'), false)
            ->cell(function($value, $row) {
                return View::make('widgets.dashboard.product.variant-details', [
                    'variant' => $row
                ]);
            });
        $grid->add('status', trans('labels.status'), false)
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
        
        $grid->add('id', trans('labels.actions'))->cell(function($value, $row) {
            return '
                <a class="btn btn-xs btn-primary" href="'.url('/admin/product-variants/'.$value.'/show').'">
                    <i class="fa fa-eye"></i>
                    '.trans('actions.view').'
                </a>
            ';
        });
     
        $grid->orderBy('name','asc');
        $grid->paginate(10);
        
        return view('admin.pages.default.grid', [
            'title' => trans('labels.product_variants'),
            'grid' => $grid,
            'filter' => $filter
        ]);
    }
    
    public function add(Request $request) {}
    
    public function edit(ProductVariantSaveFormRequest $request, $id) {
        $model = $this->getModelForEdit($id);
        
        if (!$model) {
            abort(404);
        }
        
        $form = DataForm::source($model);
        return $this->form($form);
    }
    
    protected function addDesignerAttachmentsForm($form)
    {
        $form->attr('enctype', 'multipart/form-data');
        
        $form->add('designer-attachment', null, 'container')->content(
            '<div class="ta-c">
                <div class="fileinput fileinput-new d-ib" data-provides="fileinput">
                    <span class="btn btn-default btn-file">
                        <span class="fileinput-new">'.trans('actions.select_file').'</span>
                        <span class="fileinput-exists">'.trans('actions.change').'</span>
                        <input type="file" name="designer-attachment" />
                    </span>
                    <span class="fileinput-filename"></span>
                    <a href="#" class="close fileinput-exists" data-dismiss="fileinput" style="float: none">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
            '
        );
        
        $form->submit(trans('actions.save'), 'BR');
        
        return $form;
    }
    
    protected function form($form)
    {
        $form = $this->addDesignerAttachmentsForm($form);
        
        $form->saved(function() use ($form) {
            
            if (
                Input::hasFile('designer-attachment')
                && Input::file('designer-attachment')->isValid()
            ) {
                $file = FileAttachment::create([
                    'file' => Input::file('designer-attachment'),
                    'type' => File::TYPE_VARIANT_AR3_DESIGNER_ATTACHMENT
                ]);
                
                if ($file) {
                    $form->model->designerAttachments()->save($file, [
                        'type' => File::TYPE_VARIANT_AR3_DESIGNER_ATTACHMENT
                    ]);
                }
            }
            
            flash()->success(trans('messages.saved'));
            return redirect()->back();
        });
        
        $formView = $form->view();
        
        if ($form->hasRedirect()) {
            return $form->getRedirect();
        }
        
        return view('admin.pages.product-variant.show', [
            'title' => trans('labels.product_variants'),
            'subtitle' => $form->model->name,
            'model' => $form->model,
            'form' => $formView,
            'formObject' => $form,
            'moderationStatusHistory' => $form->model->moderationStatusRevisionHistory,
            'moderationCommentHistory' => $form->model->moderationCommentRevisionHistory
        ]);
    }
    
    /**
     * moderation
     */
        public function approve(Request $request, $id)
        {
            if( ! $variant = ProductVariant::find($id)) {
                flash()->success(trans('messages.product_variant_not_found'));
                return redirect()->back();
            }
            
            $variant->changeModerationStatusTo(ProductVariant::MODERATION_STATUS_APPROVED, '');
            
            return redirect()->back();
        }
        
        public function decline(Request $request, $id)
        {
            if( ! $variant = ProductVariant::find($id)) {
                flash()->success(trans('messages.product_variant_not_found'));
                return redirect()->back();
            }
            
            $variant->decline(
                $request->get('comment')
            );
            
            return redirect()->back();
        }
    
}
