<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Gate;
use DataForm;
use Input;
use Cache;
use Illuminate\Http\Request;

use App\Components\Money;
use App\Components\Shopify;
use App\Components\Logger;
use App\Models\Order;
use App\Models\Store;
use App\Transformers\Order\ExternalOrderTransformer;
use App\Http\Controllers\Admin\Traits\RapydControllerTrait;
use App\Http\Controllers\Traits\TransformersTrait;

class OrdersController extends AdminController
{
    use RapydControllerTrait;
    use TransformersTrait;

    protected function getModelForAdd()
    {
        return new Order();
    }

    protected function getModelForEdit($id)
    {
        return Order::find($id);
    }

    protected function getModelForDelete($id)
    {
        return Order::find($id);
    }

    protected function grid($model, $title)
    {
        $filter = \DataFilter::source($model);
        $filter->add('order_number', trans('labels.order_number'), 'text');
        $filter->add('store.name', trans('labels.store'), 'text');
        $filter->submit(trans('actions.search'));
        $filter->reset(trans('actions.reset'));
        $filter->build();

        $grid = \DataGrid::source($filter);

        $grid->add('date', trans('labels.date'))
            ->cell(function($value, $row) {
                return $row->createdAtTZ('F j, h:ia T');
            });
        $grid->add('order_number', trans('labels.order_number'), false);
        $grid->add('store.name', trans('labels.store'), false)
            ->cell(function($value, $row) {
                return ($row->store ? $row->store->name : '');
            });
        $grid->add('status', trans('labels.status'), false)
            ->cell(function($value, $row) {
                return '<div class="label label-default">'.$row->getStatusName().'</div>';
            });
        $grid->add('payment_status', trans('labels.payment_status'), false)
            ->cell(function($value, $row) {
                return '<div class="label label-default">'.$row->getPaymentStatusName().'</div>';
            });
        $grid->add('refund_status', trans('labels.refund_status'), false)
            ->cell(function($value, $row) {
                return '<div class="label label-warning">'.$row->getRefundStatusName().'</div>';
            });
        $grid->add('fulfillment_status', trans('labels.fulfillment_status'), false)
            ->cell(function($value, $row) {
                return '<div class="label label-default">'.$row->getFulfillmentStatusName().'</div>';
            });
        $grid->add('sent_kz_status', trans('labels.sent_to_kz_status'))
            ->cell(function($value, $row) {
                return $row->notified_api_at
                ? ' <span class="label label-success">
                        '.trans('labels.sent_to_kz_at').': '.$row->notified_api_at.'
                    </span>'
                : ($row->isPaid()
                    ? ('
                        <span class="label label-warning">
                            '.trans('labels.not_sent_to_kz').'
                            '.( $row->getMeta(Order::META_KZAPI_LAST_NOTIFY_ERROR) ? '
                            <span
                                class="btn btn-link js-popover p-0"
                                data-toggle="popover"
                                data-trigger="click"
                                data-content-selector=".js-kzapi-notify-error-'.$row->id.'">
                                    <i class="fa fa-info-circle c-w"></i>
                            </span>
                            <span class="d-n">
                                <span class="js-kzapi-notify-error-'.$row->id.'">
                                    <span class="c-b">'.$row->getMeta(Order::META_KZAPI_LAST_NOTIFY_ERROR).'</span>
                                </span>
                            </span>
                            ' : '').'
                        </span>
                    ')
                    : '<span class="label"><span class="text-muted">'.trans('labels.pending_completion')).'</span></span>';
            });
        $grid->add('total_price', trans('labels.total'), false)
            ->cell(function($value, $row) {
                return Money::i()->format($row->total());
            });

        $grid->add('id', trans('labels.actions'))->cell(function($value, $row) {
            return '
                <a class="btn btn-xs btn-primary" href="'.url('/admin/orders/'.$value.'/edit').'">
                    <i class="fa fa-edit"></i>
                    '.trans('actions.edit').'
                </a>
            ';
        });

        $grid->orderBy('id','desc');
        $grid->paginate(15);

        return view('admin.pages.default.grid', [
            'title' => $title,
            'grid' => $grid,
            'filter' => $filter,
            'footer' => '<admin-pull-order-form clas="ml-20"></admin-pull-order-form>'
        ]);
    }

    /**
     * All orders
     */
    public function all(Request $request)
    {
        $model = Order::with('store');
        return $this->grid($model, trans('labels.orders'));
    }

    /**
     * Orders with refund requests
     */
    public function refunds(Request $request)
    {
        $model = Order::getRefundsQuery();
        return $this->grid($model, trans('labels.order_refunds'));
    }

    /**
     * Orders without shipping groups
     */
    public function withoutShippingGroups(Request $request)
    {
        $model = Order::getWithoutShippingGroupsQuery();
        return $this->grid($model, trans('labels.orders_without_shipping_groups'));
    }

    /**
     * Orders paid but not sent to KZ API yet
     */
    public function notSentToKZAPI(Request $request)
    {
        $model = Order::getNotSentToKZAPIQuery();
        return $this->grid($model, trans('labels.orders_not_sent_to_kz_api'));
    }


    protected function theForm($form)
    {
        $form
            ->add('order_number', trans('labels.order_number'), 'text')
            ->attr('readonly', 'true');
        //
        // $form
        //     ->add('status', trans('labels.status'), 'select')
        //     ->attr('disabled', 'true')
        //     ->options(Order::listStatuses());

        $form->link('/admin/orders', trans('actions.back_to_all_orders'), 'BL', [
            'class' => 'btn btn-default ml-10'
        ]);

        //$form->submit(trans('actions.save'), 'BR');
        // $form->link('/admin/orders/'.$form->model->id.'/delete', trans('actions.delete'), 'BR', [
        //     'class' => 'btn btn-default ml-10'
        // ]);

        if (Gate::allows('restore', $form->model)) {
            $form->link('/admin/orders/'.$form->model->id.'/restore', trans('actions.restore'), 'BR', [
                'class' => 'btn btn-success ml-10'
            ]);
        }

        if (Gate::allows('cancel', $form->model)) {
            $form->link('/admin/orders/'.$form->model->id.'/cancel', trans('actions.cancel'), 'BR', [
                'class' => 'btn btn-danger ml-10'
            ]);
        }

        return $form;
    }

    protected function form($form)
    {
        $create = ($form->action == 'insert');

        $form = $this->theForm($form);

        $form->saved(function() use ($form, $create) {

            if ($form->action == 'delete') {
                flash()->success(trans('messages.deleted'));
            }
            else {
                $form->model->save();

                flash()->success(trans('messages.saved'));

                if ($create) {
                    return redirect(url('/admin/orders/'.$form->model->id.'/edit'));
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

        return view('admin.pages.order.edit', [
            'title' => trans('labels.orders'),
            'subtitle' => $subtitle,
            'form' => $formView,
            'formObject' => $form,
            'model' => $form->model
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('cancel', $order);

        $order->cancel();

        return redirect()->back();
    }

    public function restore(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('restore', $order);

        $order->restore();

        return redirect()->back();
    }

    public function searchShopify()
    {
        $this->authorize('pull', new Order);

        $store_id = (int)request()->get('store_id');
        $search = filter_var(request()->get('search'), FILTER_SANITIZE_STRING);

        $store = Store::find($store_id);
        if (!$store || !$store->isInSync()) {
            return response()->apiError(
                trans('labels.store_not_found'),
                404
            );
        }

        try {
            $vendorOrdersCall = Cache::remember(__CLASS__.':'.__FUNCTION__.':'.$store->id.':'.str_slug($search), 3, function() use($search, $store) {
                return Shopify::i($store->shopifyDomain(), $store->access_token)
                    ->searchOrders($search);
            });
        }
        catch(Exception $e) {
            Logger::i(Logger::API_SHOPIFY_ORDERS)
                ->error('External search orders error: '.$e->getMessage(), [
                    'search' => $search,
                    'trace' => $e->getTrace()
                ]);

            throw $e;
        }

        return response()->api([
            'externalOrders' => $this->serializeCollection($vendorOrdersCall->orders, new ExternalOrderTransformer)
        ]);
    }

    public function pullFromShopify()
    {
        $store_id = (int)request()->get('store_id');
        $shopifyOrderId = request()->get('external_order_id');

        $store = Store::find($store_id);
        $order = Order::findByProviderId($shopifyOrderId);

        if (!$store || !$store->user) {
            return response()->apiError(
                trans('labels.store_not_found'),
                404
            );
        }

        if ($order) {
            return response()->apiError(
                trans('labels.order_already_exists'),
                400
            );
        }

        $this->authorize('pull', new Order);

        $currentUser = auth()->user();
        Auth::onceUsingId($store->user->id);

        try {
            $call = Shopify::i($store->shopifyDomain(), $store->access_token)
                ->getOrder($shopifyOrderId);

            $order = Order::pullFromShopifyJson($store, $call->order);
        }
        catch(Exception $e) {
            Auth::onceUsingId($currentUser->id);

            Logger::i(Logger::API_SHOPIFY_ORDERS)
                ->error('Pull order error: '.$e->getMessage(), [
                    'shopifyOrderId' => $shopifyOrderId,
                    'trace' => $e->getTrace()
                ]);

            throw $e;
        }
        Auth::onceUsingId($currentUser->id);

        if (!$order) {
            return response()->apiError(
                trans('labels.order_cannot_be_imported'),
                500
            );
        }

        return response()->api([
            'orderUrl' => url('/admin/orders/'.$order->id.'/edit')
        ]);
    }
}
