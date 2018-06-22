<?php

namespace App\Http\Requests\Dashboard\Store;

use App\Http\Requests\Request;
use App\Http\Controllers\Dashboard\StoreConnectController;

class StoreConnectSelectAccountFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_type' => 'string|in:'.StoreConnectController::ACCOUNT_TYPE_NEW.','.StoreConnectController::ACCOUNT_TYPE_EXISTING.','.StoreConnectController::ACCOUNT_TYPE_CURRENT
        ];
    }
}
