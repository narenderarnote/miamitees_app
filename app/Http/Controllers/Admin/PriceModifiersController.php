<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\PriceModifier;
use App\Transformers\PriceModifier\PriceModifierBriefTransformer;

class PriceModifiersController extends AdminController
{
    public function add(Request $request)
    {
        $modifierValue = (float)$request->get('modifier');
        $user_ids = $request->get('user_ids');
        $template_ids = $request->get('template_ids');

        $modifiers = [];
        foreach($user_ids as $user_id) {
            foreach($template_ids as $template_id) {
                $modifier = PriceModifier::where([
                    'user_id' => $user_id,
                    'template_id' => $template_id
                ])->first();

                if (!$modifier) {
                    $modifier = new PriceModifier();
                    $modifier->user_id = $user_id;
                    $modifier->template_id = $template_id;
                }

                $modifier->modifier = $modifierValue;
                $modifier->save();

                $modifiers[] = $modifier;
            }
        }

        return response()->api(null, [
            'priceModifiers' => $this->serializeCollection($modifiers, new PriceModifierBriefTransformer)
        ]);
    }

    public function delete(Request $request, $id)
    {
        $modifier = PriceModifier::findOrFail($id);
        $modifier->delete();

        return response()->api(null, []);
    }
}
