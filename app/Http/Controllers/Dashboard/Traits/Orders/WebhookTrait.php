<?php

namespace App\Http\Controllers\Dashboard\Traits\Orders;

use Auth;
use Gate;
use Exception;
use Bugsnag;
use Illuminate\Http\Request;

use App\Components\Shopify;
use App\Components\Logger;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\StoreSettings;
use App\Models\Payment;

trait WebhookTrait
{

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
        Logger::i(Logger::WEBHOOK_ORDERS)->notice($request->getContent());
        Logger::i(Logger::WEBHOOK_ORDERS)->notice('HTTP_X_SHOPIFY_TOPIC: '.$topic);
        Logger::i(Logger::WEBHOOK_ORDERS)->notice('HTTP_X_SHOPIFY_SHOP_DOMAIN: '.$domain);

        if ($isValid) {
            $stores = Store::findByDomain($domain);

            switch($topic) {
                case Shopify::WEBHOOK_TOPIC_ORDERS_PAID:

                    foreach ($stores as $store) {

                        if (!$store->user) {
                            Bugsnag::notifyException(new Exception("User does not exist for store {$store->id}"));
                            continue;
                        }

                        // we need to auth user to get his price modifiers
                        Auth::onceUsingId($store->user->id);

                        Order::pullFromShopifyJson($store, $json);
                    }

                    break;

                case Shopify::WEBHOOK_TOPIC_ORDERS_UPDATED:

                    $orders = Order::findAllByProviderId($json->id);

                    if (!empty($orders)) {
                        foreach ($orders as $order) {
                            if (
                                $order
                                && $json->financial_status == Shopify::ORDER_FINANCIAL_STATUS_REFUNDED
                            ) {
                                // TODO: do nothing for now if refunded
                            }
                        }
                    }

                    break;

                case Shopify::WEBHOOK_TOPIC_ORDERS_CANCELLED:

                    $orders = Order::findAllByProviderId($json->id);

                    if (!empty($orders)) {
                        foreach ($orders as $order) {
                            if ($order) {
                                $order->cancel();
                            }
                        }
                    }

                    break;
            }


        }
    }
}
