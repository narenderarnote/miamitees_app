<?php

namespace App\Http\Controllers\Dashboard\Traits\Store;

use Illuminate\Http\Request;

use App\Models\Store;
use App\Models\StoreSettings;

trait OrdersSettingsTrait
{
    /**
     * Reset store charges
     */
    public function resetCharges(Request $request, Store $store)
    {
        $this->authorize('edit', $store);
        
        $store->saveSetting(StoreSettings::SETTING_CARD_CHARGE_CHARGES_AMOUNT, 0);
        $store->save();
        
        return $this->returnSuccess(trans('messages.store_removed'));
    }
    
    /*
     * Update store settings
     */
    public function saveSettingsOrders(Request $request, Store $store)
    {
        $settings = StoreSettings::findByStoreId($store->id);
        $this->authorize('edit', $store);
        
        $settings->update(
            array_filter(
                $request->only([
                    StoreSettings::SETTING_AUTO_ORDERS_CONFIRM,
                    StoreSettings::SETTING_AUTO_PUSH_PRODUCTS,
                    StoreSettings::SETTING_CARD_CHARGE_LIMIT_ENABLED,
                    StoreSettings::SETTING_CARD_CHARGE_LIMIT_AMOUNT
                ]),
                function($val) {
                    return !($val === '' || is_null($val));
                }
            )
        );
        
        return $this->returnSuccess(trans('messages.store_updated'));
    }
}
