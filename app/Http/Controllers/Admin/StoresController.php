<?php

namespace App\Http\Controllers\Admin;

use DataForm;
use Input;

use Illuminate\Http\Request;

use App\Components\Shopify;
use App\Models\Store;
use App\Models\ProductModelTemplate;
use App\Transformers\Product\ProductModelTemplateBriefTransformer;
use App\Transformers\PriceModifier\PriceModifierBriefTransformer;

class StoresController extends AdminController
{
    use \App\Http\Controllers\Admin\Traits\RapydControllerTrait;

    protected function getModelForAdd()
    {
        return new Store();
    }

    protected function getModelForEdit($id)
    {
        return Store::find($id);
    }

    protected function getModelForDelete($id)
    {
        return Store::find($id);
    }

    /**
     * All patients
     */
    public function all(Request $request)
    {
        $model = new Store();

        $filter = \DataFilter::source($model);
        $filter->add('name', trans('labels.name'), 'text');
        $filter->add('user.email', trans('labels.email'), 'text');
        $filter->submit(trans('actions.search'));
        $filter->reset(trans('actions.reset'));
        $filter->build();

        $grid = \DataGrid::source($filter);

        $grid->add('name', trans('labels.name'), false);
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
        $grid->add('status', trans('labels.status'), false)
            ->cell(function($value, $row) {
                if ($row->isInSync()) {
                    if ($row->shopifyWebhooksAreSetUp()) {
                        return '<span class="label label-success">'
                            .trans('labels.active').
                        '</span>';
                    }
                    else {
                        return '<span class="label label-warning">'
                            .trans('labels.pending').
                        '</span>';
                    }
                }
                else {
                    return '<span class="label label-success">'
                        .trans('labels.active').
                    '</span>';
                }
            });
        $grid->add('type', trans('labels.type'), false)
            ->cell(function($value, $row) {
                return $row->isInSync() ? trans('labels.shopify_store') : trans('labels.custom_store');
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
        $grid->add('products', trans('labels.products'), false)
            ->cell(function($value, $row) {
                return '
                    <a class="btn btn-default btn-xs" href="'.url('/admin/products?store_name='.$row->id.'&search=1').'">
                        <i class="fa fa-shopping-bag"></i>
                        '.trans_choice('labels.n_products', count($row->products)).'
                    </a>
                ';
            });
        $grid->add('id', trans('labels.actions'))->cell(function($value, $row) {
            return '
                <a class="btn btn-xs btn-primary" href="'.url('/admin/stores/'.$value.'/show').'">
                    <i class="fa fa-eye"></i>
                    '.trans('actions.show').'
                </a>
            ';
        });

        $grid->orderBy('created_at','desc');
        $grid->paginate(10);

        return view('admin.pages.default.grid', [
            'title' => trans('labels.stores'),
            'grid' => $grid,
            'filter' => $filter
        ]);
    }

    protected function form($form)
    {
        $create = ($form->action == 'insert');

        $form->add('inserts', trans('labels.inserts'), 'text');
        $form->submit(trans('actions.save'), 'BR');

        $form->saved(function() use ($form, $create) {

            if ($form->action == 'delete') {
                flash()->success(trans('messages.deleted'));
            }
            else {
                flash()->success(trans('messages.saved'));

                if ($create) {
                    return redirect(url('/admin/stores/'.$form->model->id.'/edit'));
                }
            }
            return redirect()->back();
        });

        if ($form->model->id) {
            $subtitle = trans('labels.editing').' - #'.$form->model->id;
        }
        else {
            $subtitle = trans('labels.adding');
        }

        $formView = $form->view();

        if ($form->hasRedirect()) {
            return $form->getRedirect();
        }

        return view('admin.pages.store.edit', [
            'title' => trans('labels.stores'),
            'subtitle' => $subtitle,
            'form' => $formView,
            'formObject' => $form
        ]);
    }

    public function recreateWebhooks($id) {
        $store = Store::findOrFail($id);

        if ($store->isInSync()) {
            Shopify::i($store->shopifyDomain(), $store->access_token)
                ->replaceWebhooks();

            // cache webhooks statuses
            //$store->shopifyWebhooksAreSetUp(Store::$CACHE_FORCE_UPDATE);
        }

        return redirect()->back();
    }

}
