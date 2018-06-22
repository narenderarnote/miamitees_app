<?php

namespace App\Http\Controllers\Dashboard\Traits\Products;

use Input;
use Log;
use DB;
use Gate;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Jobs\Product\ProductPushToStoreJob;

trait PushToStoreTrait
{

    public function pushToStore($product_id)
    {
        $product = Product::find($product_id);
        if ($product->store->isInSync()) {
            return $this->pushToProviderStore($product);
        }
        else {
            return $this->pushToCustomStore($product);
        }
    }

    protected function pushToProviderStore($product)
    {
        if (Gate::denies('push_to_store', $product)) {
            return abort(403, trans('messages.product_cannot_be_pushed_to_store'));
        }

        if ($product->isQueuedForSync()) {
            return abort(400, trans('messages.product_already_queued_to_be_pushed_to_store'));
        }

        dispatch(new ProductPushToStoreJob($product));
        $product->queuedForSync();

        return $this->returnSuccess(trans('messages.product_pushed_to_store'));
    }

    protected function pushToCustomStore($product)
    {
        $product->activate();
        return $this->returnSuccess(trans('messages.product_pushed_to_store'));
    }
}
