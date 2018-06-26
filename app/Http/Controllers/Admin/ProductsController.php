<?php

namespace App\Http\Controllers\Admin;

use DataForm;
use Input;
use View;
use Exception;
use Log;
use Bugsnag;

use Illuminate\Http\Request;

use App\Components\Shopify;
use App\Models\Product;
use App\Models\ProductDesignerFile;
use App\Models\ProductVariant;
use App\Models\FileAttachment;
use App\Http\Requests\Admin\Product\ProductSaveFormRequest;

class ProductsController extends AdminController
{
    use \App\Http\Controllers\Admin\Traits\RapydControllerTrait;

    protected function getModelForAdd()
    {
        return new Product();
    }

    protected function getModelForEdit($id)
    {
        return Product::find($id);
    }

    protected function getModelForDelete($id)
    {
        return Product::find($id);
    }

    /**
     * All patients
     */
    public function all(Request $request)
    {
        $model = Product::with('user')->with('store');

        $filter = \DataFilter::source($model);
        $filter->add('name', trans('labels.name'), 'text');
        $filter->add('user.email', trans('labels.user'), 'tags');
        $filter->add('store.name', trans('labels.store'), 'tags');
        $filter->add('status', trans('labels.status'), 'select')
            ->options(['' => trans('labels.status')] + Product::listStatuses());
        $filter->add('moderation_status', trans('labels.moderation_status'), 'select')
            ->options(['' => trans('labels.moderation_status')] + Product::listModerationStatuses());
        $filter->submit(trans('actions.search'));
        $filter->reset(trans('actions.reset'));
        $filter->build();

        $grid = \DataGrid::source($filter);

        $grid->add('preview', trans('labels.preview'))
            ->cell(function($value, $row) {
                if ($row->mockupPreview() && $row->mockupPreview()->url()) {
                    return '
                        <img class="h-50" src="'.$row->mockupPreview()->url('thumb').'" alt="" />
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
                if (!$row->user) {
                    return;
                }
                return '
                    <a class="btn btn-default btn-xs" href="'.url('/admin/users/'.$row->user->id.'/edit').'">
                        <i class="fa fa-edit"></i>
                        '.$row->user->email.'
                    </a>
                ';
            });
        $grid->add('store.name', trans('labels.store'), false);
        $grid->add('name', trans('labels.name'), false);
        $grid->add('status', trans('labels.status'), false)
            ->cell(function($value, $row) {
                return $row->getStatusName();
            });
        $grid->add('details', trans('labels.details'), false)
            ->cell(function($value, $row) {
                return View::make('widgets.dashboard.product.moderation-status', [
                    'resource' => $row
                ]) . View::make('widgets.dashboard.product.product-details', [
                    'product' => $row
                ]);
            });

        // TODO: not needed for now
        //$grid->add('product_variants', trans('labels.product_variants'), false)
        //    ->cell(function($value, $row) {
        //        return '
        //            <a class="btn btn-default btn-xs" href="'.url('/admin/product-variants?product_name='.$row->id.'&search=1').'">
        //                <i class="fa fa-list"></i>
        //                '.trans_choice('labels.n_variants', count($row->variants)).'
        //            </a>
        //        ';
        //    });

        $grid->add('updated_at', trans('labels.updated_at'))
            ->cell(function($value, $row) {
                return '
                    <time
                        datetime="'.$row->updatedAtTZ().'"
                        title="'.$row->updatedAtTZ().'"
                        data-format="">
                        '.$row->updatedAtTZ().'
                    </time>
                ';
            });

        $grid->add('id', trans('labels.actions'))->cell(function($value, $row) {
            return '
                <a class="btn btn-xs btn-primary" href="'.url('/admin/products/'.$value.'/show').'">
                    <i class="fa fa-eye"></i>
                    '.trans('actions.show').'
                </a>
            ';
        });

        $grid->orderBy('updated_at', 'asc');
        $grid->paginate(10);

        return view('admin.pages.default.grid', [
            'title' => trans('labels.products'),
            'grid' => $grid,
            'filter' => $filter
        ]);
    }

    public function add(Request $request) {}

    public function edit(ProductSaveFormRequest $request, $id) {
        $model = $this->getModelForEdit($id);

        if (!$model) {
            abort(404);
        }

        $form = DataForm::source($model);
        return $this->form($form);
    }


    protected function form($form)
    {
        $form->saved(function() use ($form) {

            $printPositions = Input::get('print_position');

            // upload files
            foreach($form->model->clientFiles as $clientFile) {
                foreach(ProductDesignerFile::listTypes() as $type => $label) {
                    $fieldName = $clientFile->design_location.'__'.$type;
                    //$fieldName = $type;

                    if (
                        Input::hasFile($fieldName)
                        && Input::file($fieldName)->isValid()
                    ) {

                        $file = FileAttachment::create([
                            'file' => Input::file($fieldName),
                            'type' => $type
                        ]);

                        if ($file) {
                            ProductDesignerFile::create([
                                'product_client_file_id' => $clientFile->id,
                                'file_id' => $file->id
                            ]);

                            // refresh
                                $form->model->load('clientFiles');
                        }
                    }

                    $clientFile->load('designerFiles');

                    if (isset($printPositions[$clientFile->design_location.'__'.$type])) {
                        $designerFile = $clientFile->designerFile($type);
                        if ($designerFile) {
                            $designerFile->print_position = $printPositions[$clientFile->design_location.'__'.$type];
                            $designerFile->save();
                        }
                    }
                }
            }

            // update assignments
            $assignments = Input::get('product_variant_designer_file');

            foreach($form->model->clientFiles as $clientFile) {
                foreach($clientFile->designerFiles as $designerFile) {
                    $designerFileType = $designerFile->file->type;
                    $variant_ids = !empty($assignments[$clientFile->design_location][$designerFileType])
                        ? $assignments[$clientFile->design_location][$designerFileType]
                        : [];

                    $designerFile->variants()->sync($variant_ids);
                }
            }

            flash()->success(trans('messages.saved'));
            return redirect()->back();
        });

        $formView = $form->view();

        if ($form->hasRedirect()) {
            return $form->getRedirect();
        }

        return view('admin.pages.product.show', [
            'title' => trans('labels.products'),
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
            if( ! $product = Product::find($id)) {
                flash()->success(trans('messages.product_not_found'));
                return redirect()->back();
            }

            $product->approve();

            return redirect()->back();
        }

        public function decline(Request $request, $id)
        {
            if( ! $product = Product::find($id)) {
                flash()->success(trans('messages.product_not_found'));
                return redirect()->back();
            }

            // delete in case if product was auto approved
            if ($product->isAutoApproved() || $product->isApproved()) {

                // when it already synced
                if ($product->isSynced()) {
                    try {
                        Shopify::i($product->store->shopifyDomain(), $product->store->access_token)
                            ->deleteProduct($product->provider_product_id);
                    }
                    catch(Exception $e) {
                        if (Shopify::is404Exception($e)) {
                            // product doesn't exist on shopify, do nothing
                        }
                        else {
                            Log::error($e);
                            Bugsnag::notifyException($e);
                            throw $e;
                        }
                    }
                }

                $product->status = Product::STATUS_DRAFT;
            }

            $product->decline(
                $request->get('comment')
            );

            return redirect()->back();
        }

    public function saveMeta(Request $request, $id)
    {
        if( ! $product = Product::find($id)) {
            flash()->success(trans('messages.product_not_found'));
            return redirect()->back();
        }

        if ($product->template()->category->isHeadwear()) {
            $stitches = filter_var($request->get('stitches'), FILTER_SANITIZE_STRING);
            $thread_colors = filter_var($request->get('thread_colors'), FILTER_SANITIZE_STRING);

            $product->setMeta(Product::META_STITCHES, $stitches);
            $product->setMeta(Product::META_THREAD_COLORS, $thread_colors);
        }

        return redirect()->back();
    }
}
