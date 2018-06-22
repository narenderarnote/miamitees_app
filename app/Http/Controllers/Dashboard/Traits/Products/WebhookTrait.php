<?php

namespace App\Http\Controllers\Dashboard\Traits\Products;

use Exception;
use Bugsnag;

use Illuminate\Http\Request;

use App\Components\Shopify;
use App\Components\Logger;
use App\Models\Store;
use App\Models\Product;

trait WebhookTrait {

    /**
     * Webhook endpoint
     */
    public function webhook(Request $request)
    {
        $json = json_decode($request->getContent());
        $isValid = Shopify::verifyWebhook($request->getContent(), $request->server('HTTP_X_SHOPIFY_HMAC_SHA256'));

        $topic = $request->server('HTTP_X_SHOPIFY_TOPIC');
        $domain = filter_var($request->server('HTTP_X_SHOPIFY_SHOP_DOMAIN'), FILTER_SANITIZE_STRING);

        // we will log all webhooks
        Logger::i(Logger::WEBHOOK_PRODUCTS)->notice('HTTP_X_SHOPIFY_TOPIC: '.$topic);
        Logger::i(Logger::WEBHOOK_PRODUCTS)->notice('HTTP_X_SHOPIFY_SHOP_DOMAIN: '.$domain);
        Logger::i(Logger::WEBHOOK_PRODUCTS)->notice($request->getContent());

        if (!$isValid) {
            abort(400, trans('messages.request_is_not_valid'));
        }

        $stores = Store::findByDomain($domain);

        switch($topic) {
            case Shopify::WEBHOOK_TOPIC_PRODUCT_CREATE:

                $shopifyProduct = $json;
                foreach ($stores as $store) {

                    if (!$store->user) {
                        Bugsnag::notifyException(new Exception("User does not exist for store {$store->id}"));
                        continue;
                    }

                    $call = Shopify::i($store->shopifyDomain(), $store->access_token)
                        ->getProductMetafields($shopifyProduct->id);

                    $isMntzProduct = false;
                    foreach($call->metafields as $metafield) {
                        if (
                            $metafield->namespace == Shopify::METAFIELDS_NAMESPACE_GLOBAL
                            && $metafield->key == Shopify::METAFIELDS_KEY_PRODUCT
                        ) {
                            $isMntzProduct = true;
                            break;
                        }
                    }

                    // NOTE: we need to avoid products duplicate
                    if (
                        !$isMntzProduct
                        && env('TURN_ON_FEATURE__PULL_PRODUCTS_FROM_PROVIDER')
                    ) {
                        Product::createOrUpdateShopifyProduct(
                            $store->user,
                            $store,
                            $shopifyProduct
                        );
                    }
                }
                break;

            case Shopify::WEBHOOK_TOPIC_PRODUCT_UPDATE:
                $shopifyProduct = $json;
                foreach ($stores as $store) {

                    if (!$store->user) {
                        Bugsnag::notifyException(new Exception("User does not exist for store {$store->id}"));
                        continue;
                    }

                    Product::updateShopifyProductIfExists(
                        $store->user,
                        $store,
                        $shopifyProduct
                    );
                }
                break;

            case Shopify::WEBHOOK_TOPIC_PRODUCT_DELETE:
                foreach ($stores as $store) {

                    if (!$store->user) {
                        Bugsnag::notifyException(new Exception("User does not exist for store {$store->id}"));
                        continue;
                    }

                    $product = Product::findByProviderId($json->id, $store->id);
                    if ($product) {
                        $product->changeStatus(Product::STATUS_DRAFT);
                    }
                }
                break;
        }



    }
}
