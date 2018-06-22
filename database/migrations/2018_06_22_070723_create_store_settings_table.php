<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Store;
use App\Models\StoreSettings;

class CreateStoreSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->increments('id');
            
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            
            $table->boolean('auto_orders_confirm');
            $table->boolean('import_unsynced');
            $table->boolean('notify_unsynced');
            $table->boolean('auto_stock_update');

            $table->engine = 'InnoDB';
        });
        
        $stores = Store::all();
        foreach ($stores as $store) {
            StoreSettings::createForStoreIfNotExists($store->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('store_settings');
    }
}
