<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;
use Exception;

use App\Components\Shopify;

class Store extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Culpa\Traits\Blameable;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use Traits\DatetimeTrait;
    use Traits\HashidTrait;
    use Traits\CacheTrait;
    
    const CONNECT_MODE__UNIQUE_REPLACE = 'unique_replace'; // store with the same provider_store_id will be replaced
    const CONNECT_MODE__MULTIPLE = 'multiple'; // few stores with the same domain could exists parallel

    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    const PROVIDER_SHOPIFY = 'shopify';

    protected $table = 'stores';

    protected $fillable = [

    ];

    protected $casts = [

    ];

    // revisions
    protected $revisionEnabled = true;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf = [
        'status'
    ];

    // blameable
    protected $blameable = [
        'created' => 'user_id'
    ];

    public function __construct(array $attributes = [])
    {
        $this->setRawAttributes(array_merge($this->attributes, [
          'status' => static::STATUS_ACTIVE,
        ]), true);
        parent::__construct($attributes);
    }

    /************
     * Mutators
     */

        public function setStatusAttribute($value)
        {
            if (!$this->status && !$value) {
                $this->attributes['status'] = static::STATUS_ACTIVE;
            }
            else {
                $this->attributes['status'] = $value;
            }
        }

    /*********
     * Scopes
     */

        public function scopeOwns($query, $user)
        {
            return $query
                ->where('user_id', ($user ? $user->id : 0));
        }

        public function scopeHasDomain($query, $domain)
        {
            return $query
                ->where(function($q) use($domain) { $q
                    ->where('domain', $domain)
                    ->orWhere('provider_domain', $domain);
                });
        }

        public function scopeWhichSynced($query)
        {
            return $query->where('access_token', '!=', null);
        }

    /***********
     * Relations
     */

        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

        public function products()
        {
            return $this->hasMany(Product::class);
        }

        public function localProducts()
        {
            return $this->hasMany(Product::class)
                ->where('type', Product::TYPE_LOCAL);
        }

        public function vendorProducts()
        {
            return $this->hasMany(Product::class)
                ->where('type', Product::TYPE_VENDOR);
        }

        public function vendorProductsSynced()
        {
            return $this->hasMany(Product::class)
                ->where('type', Product::TYPE_VENDOR)
                ->whereIn('status', [
                    Product::STATUS_ACTIVE,
                    Product::STATUS_IGNORED
                ]);
        }

        public function vendorProductsPending()
        {
            return $this->hasMany(Product::class)
                ->where('type', Product::TYPE_VENDOR)
                ->whereIn('status', [
                    Product::STATUS_DRAFT,
                    Product::STATUS_QUEUED_FOR_SYNC
                ]);
        }

        public function vendorProductsApproved()
        {
            return $this->hasMany(Product::class)
                ->where('type', Product::TYPE_VENDOR)
                ->where('moderation_status', Product::MODERATION_STATUS_APPROVED);
        }

        public function vendorProductsActive()
        {
            return $this->hasMany(Product::class)
                ->where('type', Product::TYPE_VENDOR)
                ->where('status', Product::STATUS_ACTIVE)
                ->where('moderation_status', Product::MODERATION_STATUS_APPROVED);
        }

        public function vendorProductsAllowedDirectOrder()
        {
            return $this->hasMany(Product::class)
                ->where('type', Product::TYPE_VENDOR)
                ->whereIn('moderation_status', [
                    Product::MODERATION_STATUS_APPROVED,
                    Product::MODERATION_STATUS_AUTO_APPROVED
                ]);
        }


        public function orders()
        {
            return $this->hasMany(Order::class);
        }

        public function settings()
        {
            return $this->hasOne(StoreSettings::class);
        }

        public function token()
        {
            return $this->hasOne(ApiToken::class);
        }

    /***********
     * Checks
     */

        public function isInSync()
        {
            return (bool)($this->access_token);
        }

        public static function shopExists($shopDomain)
        {
            return static::hasDomain($shopDomain)
                ->first();
        }

        public static function shopExistsForCurrentUser($shopDomain)
        {
            return static::hasDomain($shopDomain)
                ->owns(auth()->user())
                ->first();
        }

        public static function shopExistsExceptCurrentUser($shopDomain)
        {
            return static::hasDomain($shopDomain)
                ->where('user_id', '!=', auth()->user()->id)
                ->first();
        }

        public function isAutoOrderAmountReached()
        {
            $enabled = $this->getSetting(StoreSettings::SETTING_CARD_CHARGE_LIMIT_ENABLED);

            if (!$enabled) {
                return false;
            }

            $limit = $this->getSetting(StoreSettings::SETTING_CARD_CHARGE_LIMIT_AMOUNT);
            $charges = $this->getSetting(StoreSettings::SETTING_CARD_CHARGE_CHARGES_AMOUNT);
            return $charges > $limit;
        }

        public function shopifyWebhooksAreSetUp($forceUpdate = false)
        {
            if (!$this->hasCache('webhooks_exist') || $forceUpdate) {

                $exist = false;
                try {
                    $exist = Shopify::i($this->shopifyDomain(), $this->access_token)
                        ->allWebhooksExist();
                }
                catch(Exception $e) {
                    Log::error($e);

                    $logMetadata = [
                        'store' => $this->toArray()
                    ];
                    Log::error('Cannot check webhooks for the store', $logMetadata);
                    Bugsnag::registerCallback(function ($report) use($logMetadata) {
                        $report->setMetaData($logMetadata);
                    });
                    Bugsnag::notifyException($e);
                }

                $this->putToCache('webhooks_exist', $exist, 60 * 24 * 30);
            }

            $exist = $this->getCache('webhooks_exist');

            if ($exist) {
                $this->getWebhooks($forceUpdate);
            }

            return $exist;
        }

    /**********
     * Counters
     */



    /*************
     * Decorators
     */

        public function getProviderName()
        {
            return static::providerName($this->provider);
        }

        public function getStatusName()
        {
            return static::statusName($this->status);
        }

        public function getShippingAddressFormatted() {
            return '160 SW 12th avenue<br>suite 105<br>Deerfield beach<br>FL 33442<br>United States';
        }

        public function getSetting($name)
        {
            return ($this->settings && isset($this->settings->{$name})
                ? $this->settings->{$name}
                : null
            );
        }

        public function getWebhooks($forceUpdate = false)
        {
            if (!$this->hasCache('webhooks') || $forceUpdate) {

                $webhooks = [];
                try {
                    $webhooks = Shopify::i($this->shopifyDomain(), $this->access_token)
                        ->getWebhooks();
                }
                catch(Exception $e) {
                    Log::error($e);

                    $logMetadata = [
                        'store' => $this->toArray()
                    ];
                    Log::error('Cannot get webhooks for the store', $logMetadata);
                    Bugsnag::registerCallback(function ($report) use($logMetadata) {
                        $report->setMetaData($logMetadata);
                    });
                    Bugsnag::notifyException($e);
                }

                $this->putToCache('webhooks', $webhooks, 60 * 24 * 30);
            }

            return $this->getCache('webhooks');
        }

        public function shopifyDomain()
        {
            return $this->provider_domain ?: $this->domain;
        }

    /*********
     * Helpers
     */

        public static function statusName($status)
        {
            $statuses = static::listStatuses();
            return $statuses[$status];
        }

        public static function listStatuses()
        {
            return [
                static::STATUS_ACTIVE => trans('labels.status__active'),
                static::STATUS_CLOSED => trans('labels.status__closed')
            ];
        }

        public static function providerName($type)
        {
            $types = static::listProviders();
            return $types[$type];
        }

        public static function listproviders()
        {
            return [
                static::PROVIDER_SHOPIFY => 'Shopify'
            ];
        }

    /**************
     * Transformers
     */

        public function transformBrief()
        {
            return FractalManager::serializeItem($this, new StoreBriefTransformer);
        }

        public function transformWithSettings()
        {
            return FractalManager::serializeItem($this, new StoreWithSettingsTransformer);
        }


    /***********
     * Functions
     */

        public static function findByDomain($domain)
        {
            return static::hasDomain($domain)
                ->get();
        }

        public static function findByDomainForCurrentUser($domain)
        {
            return static::hasDomain($domain)
                ->owns(auth()->user())
                ->first();
        }

        public static function findSynced()
        {
            return static::whichSynced()->get();
        }

        public function createStore($name = '')
        {
            if ($name) {
                $this->name = $name;
            }
            $result = $this->save();

            \Event::fire(new \App\Events\Store\StoreCreatedEvent($this));

            StoreSettings::createForStoreIfNotExists($this->id);

            return $result;
        }

        public function prepareShopifyStore($shopDomain, $shopId, $accessToken)
        {
            $this->name = $shopDomain;
            $this->provider = static::PROVIDER_SHOPIFY;
            $this->provider_store_id = $shopId;
            $this->domain = $shopDomain;

            // get real shopify domain
            $this->provider_domain = Shopify::getMyshopifyDomain($shopDomain);

            $this->access_token = $accessToken;
            $this->website = 'https://'.$shopDomain;
        }

        public function createShopifyStore($shopDomain, $shopId, $accessToken)
        {
            $this->prepareShopifyStore($shopDomain, $shopId, $accessToken);
            return $this->createStore();
        }

        public static function createShopifyStoreIfNotExist($shopDomain, $shopId, $accessToken)
        {
            if ($store = static::findByDomainForCurrentUser($shopDomain)) {
                $store->access_token = $accessToken;
                $store->save();
                return $store;
            }
            else {
                $store = new static();
                $store->createShopifyStore($shopDomain, $shopId, $accessToken);
                return $store;
            }
        }

        public static function saveTemporaryShopifyStore($shop, $accessToken)
        {
            $store = new static();
            $store->prepareShopifyStore($shop->domain, $shop->id, $accessToken);
            $store->name = $shop->name;
            session(['preparedStore' => $store]);
        }

        public static function removeTemporaryStore()
        {
            Session::forget('preparedStore');
        }

        public function saveSetting($name, $value)
        {
            $this->settings->{$name} = $value;
            $this->settings->save();
        }

        public function addCharges($amount)
        {
            $charges = $this->getSetting(StoreSettings::SETTING_CARD_CHARGE_CHARGES_AMOUNT);
            $charges += $amount;
            $this->saveSetting(StoreSettings::SETTING_CARD_CHARGE_CHARGES_AMOUNT, $charges);
        }

        public static function findStoreWithRelations($store_id)
        {
            return static::with('vendorProductsSynced.variants.model')
                ->with('vendorProductsSynced.variantsSynced.model')
                ->with('vendorProductsSynced.variantsNotSynced.model')
                ->with('vendorProductsSynced.variantsIgnored.model')
                ->with('vendorProductsPending.variants.model')
                ->with('vendorProductsPending.variantsSynced.model')
                ->with('vendorProductsPending.variantsNotSynced.model')
                ->with('vendorProductsPending.variantsIgnored.model')
                ->find($store_id);
        }

    /*************
     * Collections
     */

        public static function getStoresByDomainExceptCurrentUser($shopDomain)
        {
            $user_id = auth()->user() ? auth()->user()->id : 0;
            return static::hasDomain($shopDomain)
                ->where('user_id', '!=', $user_id)
                ->get();
        }
}
