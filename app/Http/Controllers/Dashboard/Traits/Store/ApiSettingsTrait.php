<?php

namespace App\Http\Controllers\Dashboard\Traits\Store;

use Laravel\Spark\Token;
use Laravel\Spark\Contracts\Repositories\TokenRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

use App\Models\ApiToken;
use App\Models\Store;

trait ApiSettingsTrait
{
    /**
     * Create a new API token for the store.
     */
    public function enableApi(Request $request, Store $store)
    {
        $this->authorize('edit', $store);
        
        $user = auth()->user();
        
        // delete expired tokens
            $user->tokens()->where('expires_at', '<=', Carbon::now())->delete();

        // create new token
            $user->tokens()->create([
                'id' => Uuid::uuid4(),
                'type' => ApiToken::TYPE_STORE,
                'user_id' => $user->id,
                'store_id' => $store->id,
                'name' => $store->name.' '.trans('labels.token'),
                'token' => str_random(60),
                'metadata' => [],
                'transient' => false,
                'expires_at' => null
            ]);

        return $this->returnSuccess(trans('messages.store_api_enabled'));
    }
    
    /**
     * Create a new API token for the store.
     */
    public function regenerateToken(Request $request, Store $store)
    {
        $this->disableApi($request, $store);
        $this->enableApi($request, $store);

        return $this->returnSuccess(trans('messages.store_api_token_created'));
    }

    /**
     * Delete the given token.
     */
    public function disableApi(Request $request, Store $store)
    {
        $this->authorize('edit', $store);
        if ($store->token) {
            $store->token->delete();
        }
        
        return $this->returnSuccess(trans('messages.store_api_disabled'));
    }
}
