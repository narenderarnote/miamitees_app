<?php

namespace App\Http\Controllers\Dashboard\Traits\Products;

use Input;
use Log;
use DB;
use Gate;
use Exception;
use Imagine;
use Storage;
use Illuminate\Http\Request;

use App\Components\Shopify;
use App\Http\Requests\Dashboard\Product\ProductSendToModerationFormRequest;
use App\Models\File;
use App\Models\FileAttachment;
use App\Models\ProductModelTemplate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\ProductClientFile;
use App\Models\Payment;

trait SendToModerationTrait
{
    private $imagine = null;
    private $palette = null;

    private function createCanvasImage(
        $model, $modelImage, $printImage,
        $printCoordinates, $canvasSize, $scaleRatio,
        $mockupPath, $fileType
    ) {
        if (
            $model->template->garment->isAllOverPrintOrSimilar()
            && config('settings.public.product.wizard.ALL_OVER_PRINTS_PREVIEW_REPLACE')
        ) {
            $imageCanvas = $this->createCanvasImageForAllOverPrintsInReplaceMode(
                $model, $printImage
            );
        }
        else {
            $imageCanvas = $this->createCanvasImageDefault(
                $model, $modelImage, $printImage,
                $printCoordinates, $canvasSize, $scaleRatio,
                $mockupPath, $fileType
            );
        }

        // place on white bg
            $widthIsBigger = $imageCanvas->getSize()->getWidth() > $imageCanvas->getSize()->getHeight();

            $biggestSide = $widthIsBigger
                ? $imageCanvas->getSize()->getWidth()
                : $imageCanvas->getSize()->getHeight();

            $smallestSide = $widthIsBigger
                ? $imageCanvas->getSize()->getHeight()
                : $imageCanvas->getSize()->getWidth();

            $pastePoint = new Imagine\Image\Point(
                (
                    $widthIsBigger
                        ? 0
                        : (($biggestSide - $smallestSide) / 2)
                ),
                (
                    $widthIsBigger
                        ? (($biggestSide - $smallestSide) / 2)
                        : 0
                )
            );

            $whiteCanvas = $this->imagine->create(
                new Imagine\Image\Box($biggestSide, $biggestSide),
                $this->palette->color('#fff', 100)
            );

            $whiteCanvas->paste(
                $imageCanvas,
                $pastePoint
            );

        // saving
        $tmpMockupFile = storage_path($mockupPath);
        $whiteCanvas->save($tmpMockupFile);
        unset($imageCanvas);
        unset($whiteCanvas);

        $mockup = File::create([
            'file' => $tmpMockupFile,
            'type' => $fileType
        ]);
        Storage::delete($tmpMockupFile);

        return $mockup;

    }

    private function createCanvasImageDefault(
        $model, $modelImage, $printImage,
        $printCoordinates, $canvasSize, $scaleRatio,
        $mockupPath, $fileType
    )
    {
        $color = $model->getColorOption()
            ? $model->getColorOption()->value
            : '#fff';

        $canvas = $this->imagine->create(
            new Imagine\Image\Box($canvasSize->getWidth(), $canvasSize->getHeight()),
            $this->palette->color($color, 100)
        );

        $left = $printCoordinates->left * $scaleRatio;
        $top = $printCoordinates->top * $scaleRatio;
        $width = $printCoordinates->width * $scaleRatio;
        $height = $printCoordinates->height * $scaleRatio;

        $printImage
            ->resize(
                new Imagine\Image\Box(
                    $printCoordinates->width * $scaleRatio,
                    $printCoordinates->height * $scaleRatio
                )
            )
            ->crop(
                new Imagine\Image\Point(
                    $left < 0 ? abs($left) : 0,
                    $top < 0 ? abs($top) : 0
                ),
                new Imagine\Image\Box(
                    $left < 0 ? $canvasSize->getWidth() : $canvasSize->getWidth() - $left,
                    $top < 0 ? $canvasSize->getHeight() : $canvasSize->getHeight() - $top
                )
            );

        $printImagePastePoint = new Imagine\Image\Point(
            $left < 0 || $left > $canvasSize->getWidth() ? 0 : $left,
            $top < 0 || $top > $canvasSize->getHeight() ? 0 : $top
        );

        // for all over prints the user's image goes under the model's image
        if ($model->template->garment->isAllOverPrintOrSimilar()) {

            $canvas->paste(
                $printImage,
                $printImagePastePoint
            );

            $canvas->paste($modelImage, new Imagine\Image\Point(0, 0));
        }
        else {
            $canvas->paste($modelImage, new Imagine\Image\Point(0, 0));
            $canvas->paste(
                $printImage,
                $printImagePastePoint
            );
        }

        return $canvas;
    }

    private function createCanvasImageForAllOverPrintsInReplaceMode($model, $printImage) {
        $color = $model->getColorOption()
            ? $model->getColorOption()->value
            : '#fff';

        $canvas = $this->imagine->create(
            new Imagine\Image\Box(
                $printImage->getSize()->getWidth(),
                $printImage->getSize()->getHeight()
            ),
            $this->palette->color($color, 100)
        );

        $printImagePastePoint = new Imagine\Image\Point(0, 0);

        $canvas->paste(
            $printImage,
            $printImagePastePoint
        );

        return $canvas;
    }

    /**
     * Push product from wizard to shopify store and then save it to the db
     */
    private function prepareProduct(
        $existingProduct, $template, $selectedModels, $store,
        $file, $sourceFile,
        $fileBack, $sourceFileBack
    ) {
        $title = filter_var(Input::get('product_title'), FILTER_SANITIZE_STRING);
        $description = filter_var(Input::get('product_description'), FILTER_SANITIZE_STRING);

        // TODO: hardcoded by client's request
        $publish = true; //(bool)Input::get('publish_product');

        $retailPrices = (array)Input::get('retail_price');
        $printCoordinates = is_array(Input::get('print_coordinates'))
            ? (object)Input::get('print_coordinates')
            : json_decode(Input::get('print_coordinates'));
        $clientCanvasSize = is_array(Input::get('canvas_size'))
            ? (object)Input::get('canvas_size')
            : json_decode(Input::get('canvas_size'));
        $printCoordinatesBack = is_array(Input::get('print_coordinates_back'))
            ? (object)Input::get('print_coordinates_back')
            : json_decode(Input::get('print_coordinates_back'));
        $clientCanvasSizeBack = is_array(Input::get('canvas_size_back'))
            ? (object)Input::get('canvas_size_back')
            : json_decode(Input::get('canvas_size_back'));

        // prepare for images
            $this->imagine = new Imagine\Imagick\Imagine();
            $this->palette = new Imagine\Image\Palette\RGB();

            if ($template->image) {
                $modelImage = $this->imagine->open(
                    $template->image->file->path()
                );
            }
            else {
                if (empty($clientCanvasSize->width)) {
                    $modelImage = $this->imagine->open(
                        $file->file->path()
                    );
                }
                else {
                    $modelImage = $this->imagine->create(
                        new Imagine\Image\Box($clientCanvasSize->width, $clientCanvasSize->height),
                        $this->palette->color('#fff')
                    );
                }
            }

            $canvasSize = $modelImage->getSize();
            $scaleRatio = !empty($clientCanvasSize->width)
                ? $canvasSize->getWidth() / $clientCanvasSize->width
                : 1;

            $printImage = null;
            if ($file) {
                $printImage = $this->imagine
                    ->open($file->path());
            }

            $printImageBack = null;
            $modelImageBack = null;
            if ($fileBack) {
                if ($template->imageBack) {
                    $modelImageBack = $this->imagine->open(
                        $template->imageBack->file->path()
                    );
                }
                else {
                    $modelImageBack = $this->imagine->create(
                        new Imagine\Image\Box($clientCanvasSizeBack->width, $clientCanvasSizeBack->height),
                        $this->palette->color('#fff')
                    );
                }

                $canvasSizeBack = $modelImageBack->getSize();
                $scaleRatioBack = !empty($clientCanvasSizeBack->width)
                    ? $canvasSizeBack->getWidth() / $clientCanvasSizeBack->width
                    : 1;

                $printImageBack = $this->imagine->open(
                    $fileBack->path()
                );
            }

        // create product
            if (!$existingProduct) {
                $product = new Product();
            }
            else {
                $product = $existingProduct;
            }

            $product->store_id = $store->id;
            $product->name = $title;
            $product->type = Product::TYPE_VENDOR;
            $product->canvas_meta = [
                'printCoordinates' => $printCoordinates,
                'clientCanvasSize' => $clientCanvasSize,
                'printCoordinatesBack' => $printCoordinatesBack,
                'clientCanvasSizeBack' => $clientCanvasSizeBack
            ];

            if (!$existingProduct) {
                $product->createProduct();
            }
            else {
                $product->save();
                $product->clientFiles->each(function($clientFile) {
                    $clientFile->delete();
                });
                $product->variants->each(function($variant) {
                    $variant->delete();
                });
            }

        // create variants
            $variantObjects = collect([]);
            $variants = [];
            $options = [];
            $variantTemplate = [
                'title' => $title.' - ',
                'price' => 0,
                'weight' => $template->weight,
                'requires_shipping' => true,
                'inventory_management' => 'shopify',
                'inventory_quantity' => 100000000,
                'option1' => null,
                'option2' => null,
                'option3' => null,
                'metafields' => []
            ];

            foreach($selectedModels as $model) {

                $variant = $variantTemplate;
                $variant['price'] = $retailPrices[$model->id];

                $i = 1;
                $optionNames = [];
                foreach ($model->catalogOptions as $option) {
                    if ($i > 3) {
                        break;
                    }

                    $optionNames[] = $option->name;
                    $variant['option'.$i] = $option->name;

                    if (!isset($options[$i])) {
                        $options[$i] = [
                            'name' => null,
                            'position' => null,
                            'values' => []
                        ];
                    }

                    $options[$i] = [
                        'name' => $option->catalogAttribute->name,
                        'position' => $i,
                        'values' => array_merge($options[$i]['values'], [$option->name])
                    ];

                    $i++;
                }

                $variant['title'] .= implode(' ', $optionNames);

                $variantObject = new ProductVariant();
                $variantObject->name = $variant['title'];
                $variantObject->product_id = $product->id;
                $variantObject->product_model_id = $model->id;
                $variantObject->print_side = (
                    $file && $fileBack
                        ? ProductVariant::PRINT_SIDE_ALL
                        : (
                            $file
                            ? ProductVariant::PRINT_SIDE_FRONT
                            : ProductVariant::PRINT_SIDE_BACK
                        )
                );
                $variantObject->createVariant();

                // and save self variant id
                $variant['metafields'] = [
                    [
                        'key' => Shopify::METAFIELDS_KEY_MODEL_ID,
                        'value' => $model->id,
                        'value_type' => 'integer',
                        'namespace' => Shopify::METAFIELDS_NAMESPACE_GLOBAL
                    ],
                    [
                        'key' => Shopify::METAFIELDS_KEY_PRODUCT_VARIANT_ID,
                        'value' => $variantObject->id,
                        'value_type' => 'integer',
                        'namespace' => Shopify::METAFIELDS_NAMESPACE_GLOBAL
                    ]
                ];
                $variantObject->meta = $variant;
                $variantObject->save();

                // prepare variant image
                    if ($file) {
                        $mockupPath = 'app/storage/uploads/variant-mockup-'.$variantObject->id.'.jpg';
                        $mockup = $this->createCanvasImage(
                            $model, $modelImage, $printImage,
                            $printCoordinates, $canvasSize, $scaleRatio,
                            $mockupPath, File::TYPE_PRINT_FILE_MOCKUP
                        );

                        $variantObject->mockups()->save($mockup, [
                            'type' => File::TYPE_PRINT_FILE_MOCKUP
                        ]);
                    }

                // prepare back image for product
                    if ($fileBack) {
                        $mockupPathBack = 'app/storage/uploads/variant-mockup-back-'.$variantObject->id.'.jpg';
                        $mockupBack = $this->createCanvasImage(
                            $model, $modelImageBack, $printImageBack,
                            $printCoordinatesBack, $canvasSizeBack, $scaleRatioBack,
                            $mockupPathBack, File::TYPE_PRINT_FILE_MOCKUP_BACK
                        );

                        $variantObject->mockups()->save($mockupBack, [
                            'type' => File::TYPE_PRINT_FILE_MOCKUP_BACK
                        ]);
                    }

                $variants[] = $variant;
                $variantObjects[] = $variantObject;
            }

        $product->meta = [
            'title' => $title,
            'body_html' => $description,
            'vendor' => $store->name,
            'published' => $publish,
            'variants' => $variants,
            'product_type' => $template->category->name,
            'options' => array_values($options),
            'metafields' => [
                [
                    'key' => Shopify::METAFIELDS_KEY_PRODUCT,
                    'value' => 'true',
                    'value_type' => 'string',
                    'namespace' => Shopify::METAFIELDS_NAMESPACE_GLOBAL
                ]
            ]
        ];
        $product->save();

        // product client file
            if ($file) {
                ProductClientFile::create([
                    'design_location' => ProductClientFile::LOCATION_FRONT_CHEST,
                    'product_id' => $product->id,
                    'mockup_id' => $mockup->id,
                    'print_id' => $file->id,
                    'source_id' => ($sourceFile ? $sourceFile->id : null)
                ]);
            }

            if ($fileBack) {
                ProductClientFile::create([
                    'design_location' => ProductClientFile::LOCATION_BACK,
                    'product_id' => $product->id,
                    'mockup_id' => $mockupBack->id,
                    'print_id' => $fileBack->id,
                    'source_id' => ($sourceFileBack ? $sourceFileBack->id : null)
                ]);
            }

        $product->changeModerationStatusTo(
            Product::MODERATION_STATUS_ON_MODERATION,
            ''
        );

        return [$product, $variantObjects];
    }

    public function createAndSendToModeration(ProductSendToModerationFormRequest $request)
    {
        $store_id = $request->get('store_id');
        $product_model_template_id = $request->get('product_model_template_id');
        $modelIds = (array)$request->get('model_id');
        $existing_file_id = (int)$request->get('existing_file_id');
        $existing_source_file_id = (int)$request->get('existing_source_file_id');
        $existing_file_back_id = (int)$request->get('existing_file_back_id');
        $existing_source_file_back_id = (int)$request->get('existing_source_file_back_id');
        $product_id = (int)$request->get('product_id');

        // files
            $file = null;
            if ($existing_file_id) {
                $file = File::find($existing_file_id);
            }

            $fileBack = null;
            if ($existing_file_back_id) {
                $fileBack = File::find($existing_file_back_id);
            }

            $sourceFile = null;
            if ($existing_source_file_id) {
                $sourceFile = FileAttachment::find($existing_source_file_id);
            }

            $sourceFileBack = null;
            if ($existing_source_file_back_id) {
                $sourceFileBack = FileAttachment::find($existing_source_file_back_id);
            }

        $store = Store::find($store_id);
        $template = ProductModelTemplate::find($product_model_template_id);

        if (
            $file
            && !auth()->user()->isOwnerOf($file)
        ) {
            return abort(403, trans('messages.selected_file_is_not_available'));
        }

        if (
            $fileBack
            && !auth()->user()->isOwnerOf($fileBack)
        ) {
            return abort(403, trans('messages.selected_file_is_not_available'));
        }

        if (
            $sourceFile
            && !auth()->user()->isOwnerOf($sourceFile)
        ) {
            return abort(403, trans('messages.selected_source_file_is_not_available'));
        }

        if (
            $sourceFileBack
            && !auth()->user()->isOwnerOf($sourceFileBack)
        ) {
            return abort(403, trans('messages.selected_source_file_is_not_available'));
        }

        $product = null;
        if ($product_id) {
            $product = Product::find($product_id);
            if (Gate::denies('edit', $product)) {
                return abort(403, trans('messages.not_authorized_to_access_product'));
            }
        }

        $selectedModels = $template->models()
            ->whereIn('id', $modelIds)
            ->get();

        DB::beginTransaction();

        // push products
        try {
            list($product, $variantObjects) = $this->prepareProduct(
                $product, $template, $selectedModels, $store,
                $file, $sourceFile,
                $fileBack, $sourceFileBack
            );

            // pay for prepaid catalog_categories
            if ($template->category->isPrepaid()) {
                $payment = Payment::payForPrepaidProduct(auth()->user(), $product);
                if (!$payment->isPaid()) {
                    DB::rollback();
                    return abort(400, trans('messages.payment_cannot_be_processed'));
                }
            }
        }
        catch(Exception $e) {
            DB::rollback();
            Log::error('Push to store: '.$e->getMessage().' Stack trace: '.$e->getTraceAsString());
            \Bugsnag::notifyException($e);
            throw $e;
        }

        DB::commit();

        if (Gate::allows('push_to_store_without_moderation', $product)) {
            $product->autoApprove();
            return $this->pushToStore($product->id);
        }
        else {
            return $this->productSendToModeration($product);
        }
    }

    public function productSendToModeration($product)
    {
        return response()->api(trans('messages.product_sent_to_moderation'));
    }

    public function sendToModeration(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        if (Gate::denies('send_to_moderation', $product)) {
            return abort(403, trans('messages.not_authorized_to_access_product'));
        }

        $product->changeModerationStatusTo(
            Product::MODERATION_STATUS_ON_MODERATION,
            ''
        );

        return $this->returnSuccess(trans('messages.product_sent_to_moderation'));
    }


}
