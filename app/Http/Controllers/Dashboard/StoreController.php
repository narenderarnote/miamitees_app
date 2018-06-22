<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App;
use Gate;
use Laravel\Spark\Contracts\Repositories\NotificationRepository;
use Laravel\Spark\Contracts\Repositories\TokenRepository;

use App\Components\Shopify;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Store\CreateStoreFormRequest;
use App\Http\Requests\Dashboard\Store\UpdateStoreFormRequest;
use App\Models\Store;
use App\Models\StoreSettings;
use App\Models\Product;
use App\Jobs\Store\StoreUnconnectJob;
use App\Transformers\Store\StoreBriefTransformer;

class StoreController extends Controller
{
    use Traits\Store\ApiSettingsTrait;
    use Traits\Store\OrdersSettingsTrait;

    protected $notifications;
    protected $tokens;

    public function __construct(NotificationRepository $notifications, TokenRepository $tokens)
    {
        //parent::__construct();
        $this->notifications = $notifications;
        $this->tokens = $tokens;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      
        $stores = auth()->user()->stores;
       
        if ($request->is('*.json')) {
            return response()->api([
                'stores' => $this->serializeCollection(
                    $stores,
                    new StoreBriefTransformer
                )
            ]);
        }

        else {

            if (!$request->has('first') && session()->has('tour')) {
                session([
                    'firstConnect' => false
                ]);
            }

            return view('dashboard.store.index', [
                'stores' => $stores,
                'firstConnect' => session('firstConnect'),
                'tour' => session('tour')
            ]);
        }
    }

    /**
     * Create store
     */
    public function create(CreateStoreFormRequest $request)
    {
        $store = new Store();
        if (Gate::denies('create', $store)) {
            return abort(403, trans('messages.not_authorized_to_create_store'));
        }

        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING);
        $store->createStore($name);

        return $this->returnSuccess(trans('messages.store_created'));
    }

    /*
     * Update store view
     */
    public function updateView(Request $request, $store_id)
    {
        $store = Store::find($store_id);
        if (Gate::denies('edit', $store)) {
            return abort(403, trans('messages.not_authorized_to_access_store'));
        }

        return view('dashboard.store.update', [
            'store' => $store
        ]);
    }

    /*
     * Update store
     */
    public function update(UpdateStoreFormRequest $request, $store_id)
    {
        $store = Store::find($store_id);
        if (Gate::denies('edit', $store)) {
            return abort(403, trans('messages.not_authorized_to_update_store'));
        }

        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING);
        $store->name = $name;
        $store->save();

        return $this->returnSuccess(trans('messages.store_updated'));
    }

    /*
     * Pull data from shopify store
     */
    public function reload(Request $request, $store_id)
    {
        if (!getenv('TURN_ON_FEATURE__PULL_PRODUCTS_FROM_PROVIDER')) {
            return abort(404);
        }

        $store = Store::find($store_id);
        if (Gate::denies('reload', $store)) {
            return abort(403, trans('messages.not_authorized_to_reload_store'));
        }

        $call = Shopify::i($store->shopifyDomain(), $store->access_token)->getProducts();

        // create/update products
        $existingProducts = [];
        foreach($call->products as $shopifyProduct) {
            $productMetaCall = Shopify::i($store->shopifyDomain(), $store->access_token)
                ->getProductMetafields($shopifyProduct->id);

            $shopifyProductMeta = collect($productMetaCall->metafields);
            $isAppProduct = $shopifyProductMeta->filter(function ($metafield, $key) {
                return (
                    $metafield->key == Shopify::METAFIELDS_KEY_PRODUCT
                    && $metafield->namespace == Shopify::METAFIELDS_NAMESPACE_GLOBAL
                );
            })->first();

            if (
                env('TURN_ON_FEATURE__PULL_PRODUCTS_FROM_PROVIDER')
                || $isAppProduct
            ) {
                Product::createOrUpdateShopifyProduct(
                    auth()->user(),
                    $store,
                    $shopifyProduct
                );
                $existingProducts[] = $shopifyProduct->id;
            }
        }

        // delete products which don't exist anymore
        $store->vendorProductsSynced()
            ->whereNotIn('provider_product_id', $existingProducts)
            ->delete();

        return $this->returnSuccess(trans('messages.store_data_refreshed'));
    }

    /**
     * Remove store
     */
    public function remove(Request $request, $store_id)
    {
        $store = Store::find($store_id);
        if (Gate::denies('delete', $store)) {
            return abort(403, trans('messages.not_authorized_to_remove_store'));
        }

        $store_domain = $store->shopifyDomain();
        $store_access_token = $store->access_token;
        $result = $store->delete();

        if ( ! $result ) {
            return abort(500, trans('messages.store_cannot_be_removed'));
        }

        $this->dispatch(new StoreUnconnectJob($store_domain, $store_access_token));

        $this->redirectIntent('/dashboard/store');
        return $this->returnSuccess(trans('messages.store_removed'));
    }

    /**
     * Connect provider's store
     */
    public function connect(Request $request)
    {
        return redirect(
            getenv('SHOPIFY_APP_URL')
        );
        
    }

    /**
     * Start provider's connection
     */
    public function startConnectProvider(Request $request, $provider)
    {
        return redirect('https://apps.shopify.com/');
    }

    /**
     * Sync provider's store
     */
    public function syncView(Request $request, $store_id)
    {
        $store = Store::findStoreWithRelations($store_id);
        if (Gate::denies('edit', $store)) {
            return abort(403, trans('messages.not_authorized_to_access_store'));
        }

        //\Debugbar::info(
        //    Shopify::i($store->shopifyDomain(), $store->access_token)->getWebhooks()
        //);

        return view('dashboard.store.sync', [
            'store' => $store,
            'tour' => session('tour'),
            'tourLastStep' => session('tourLastStep')
        ]);
    }

    /**
     * Shopify webhooks endpoint
     */

}
