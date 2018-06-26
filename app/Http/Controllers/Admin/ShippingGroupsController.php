<?php

namespace App\Http\Controllers\Admin;

use DataForm;
use Input;
use FractalManager;

use Illuminate\Http\Request;

use App\Components\Money;
use App\Models\ShippingGroup;
use App\Models\Order;
use App\Models\ProductModelTemplate;
use App\Transformers\Product\ProductModelTemplateIncludedTransformer;

class ShippingGroupsController extends AdminController
{
    use \App\Http\Controllers\Admin\Traits\RapydControllerTrait;

    protected function getModelForAdd()
    {
        return new ShippingGroup();
    }

    protected function getModelForEdit($id)
    {
        return ShippingGroup::findOrFail($id);
    }

    protected function getModelForDelete(int $id)
    {
        return ShippingGroup::findOrFail($id);
    }

    protected function grid(Request $request, $model, $title)
    {
        $filter = \DataFilter::source($model);
        $filter->add('name', trans('labels.name'), 'text');
        $filter->submit(trans('actions.search'));
        $filter->reset(trans('actions.reset'));
        $filter->build();

        $grid = \DataGrid::source($filter);

        $grid->add('name', trans('labels.name'), false);
        $grid->add('price_us', trans('labels.price_us'), false)->cell(function($value, $row) {
            return '
                <div>
                    '.trans('labels.first_class').':
                    '.Money::i()->format($row->fullPriceUS(Order::SHIPPING_METHOD_FIRST_CLASS)).',
                    '.Money::i()->format($row->additionalPriceUS(Order::SHIPPING_METHOD_FIRST_CLASS)).'
                </div>
                <div>
                    '.trans('labels.priority_mail').':
                    '.Money::i()->format($row->fullPriceUS(Order::SHIPPING_METHOD_PRIORITY_MAIL)).',
                    '.Money::i()->format($row->additionalPriceUS(Order::SHIPPING_METHOD_PRIORITY_MAIL)).'
                </div>
            ';
        });
        $grid->add('price_ca', trans('labels.price_ca'), false)->cell(function($value, $row) {
            return '
                <div>
                    '.trans('labels.first_class').':
                    '.Money::i()->format($row->fullPriceCanada(Order::SHIPPING_METHOD_FIRST_CLASS)).',
                    '.Money::i()->format($row->additionalPriceCanada(Order::SHIPPING_METHOD_FIRST_CLASS)).'
                </div>
                <div>
                    '.trans('labels.priority_mail').':
                    '.Money::i()->format($row->fullPriceCanada(Order::SHIPPING_METHOD_PRIORITY_MAIL)).',
                    '.Money::i()->format($row->additionalPriceCanada(Order::SHIPPING_METHOD_PRIORITY_MAIL)).'
                </div>
            ';
        });
        $grid->add('price_intl', trans('labels.price_intl'), false)->cell(function($value, $row) {
            return '
                <div>
                    '.trans('labels.first_class').':
                    '.Money::i()->format($row->fullPriceIntl(Order::SHIPPING_METHOD_FIRST_CLASS)).',
                    '.Money::i()->format($row->additionalPriceIntl(Order::SHIPPING_METHOD_FIRST_CLASS)).'
                </div>
                <div>
                    '.trans('labels.priority_mail').':
                    '.Money::i()->format($row->fullPriceIntl(Order::SHIPPING_METHOD_PRIORITY_MAIL)).',
                    '.Money::i()->format($row->additionalPriceIntl(Order::SHIPPING_METHOD_PRIORITY_MAIL)).'
                </div>
            ';
        });
        $grid->add('templates', trans('labels.products'))->cell(function($value, $row) {
            $list = [];

            foreach($row->templates as $template) {

                $img = '';
                if ($template->image) {
                    $img = '<img src="'.$template->image->url('thumb').'" alt="" class="h-25" />';
                }

                $list[] = '
                    <li class="list-group-item">
                        <a target="_blank" href="'.url('/admin/product-models/'.$template->id.'/edit').'">
                            '.$img.'
                            '.$template->name.'
                        </a>
                    </li>
                ';
            }

            return '
                <ul class="list-group">'.implode('', $list).'</ul>
            ';
        });

        $grid->add('id', trans('labels.actions'))->cell(function($value, $row) {
            return '
                <a class="btn btn-xs btn-primary" href="'.url('/admin/orders/shipping/'.$value.'/edit').'">
                    <i class="fa fa-edit"></i>
                    '.trans('actions.edit').'
                </a>
                <a class="btn btn-xs btn-danger" href="'.url('/admin/orders/shipping/'.$value.'/delete').'">
                    <i class="fa fa-times"></i>
                    '.trans('actions.delete').'
                </a>
            ';
        });

        $grid->link('/admin/orders/shipping/add', trans('actions.add'), 'TR');
        $grid->orderBy('name','asc');
        $grid->paginate(10);

        return view('admin.pages.default.grid', [
            'title' => $title,
            'grid' => $grid,
            'filter' => $filter
        ]);
    }

    /**
     * All product templates
     */
    public function all(Request $request)
    {
        $model = new ShippingGroup();

        return $this->grid($request, $model, trans('labels.shipping_settings'));
    }

    public function add(Request $request)
    {
        $form = DataForm::source($this->getModelForAdd());
        return $this->form($form);
    }

    public function edit(Request $request, $id)
    {
        $model = $this->getModelForEdit($id);
        $form = DataForm::source($model);
        return $this->form($form);
    }

    protected function addForm($form)
    {
        $form->attr('enctype', 'multipart/form-data');

        $form->add('name', trans('labels.name'), 'text')
            ->rule('required|string|max:255');

        $form->add('us', null, 'container')->content('
            <hr />
            <h4 class="col-sm-offset-2 col-sm-10">'.trans('labels.us').'</h4>
        ');

            $form->add('full_price_us_first_class', trans('labels.full_price_us_first_class'), 'text')
                ->rule('numeric');

            $form->add('additional_price_us_first_class', trans('labels.additional_price_us_first_class'), 'text')
                ->rule('numeric');

            $form->add('full_price_us_priority_mail', trans('labels.full_price_us_priority_mail'), 'text')
                ->rule('numeric');

            $form->add('additional_price_us_priority_mail', trans('labels.additional_price_us_priority_mail'), 'text')
                ->rule('numeric');

        $form->add('canada', null, 'container')->content('
            <hr />
            <h4 class="col-sm-offset-2 col-sm-10">'.trans('labels.canada').'</h4>
        ');

            $form->add('full_price_ca_first_class', trans('labels.full_price_ca_first_class'), 'text')
                ->rule('numeric');

            $form->add('additional_price_ca_first_class', trans('labels.additional_price_ca_first_class'), 'text')
                ->rule('numeric');

            $form->add('full_price_ca_priority_mail', trans('labels.full_price_ca_priority_mail'), 'text')
                ->rule('numeric');

            $form->add('additional_price_ca_priority_mail', trans('labels.additional_price_ca_priority_mail'), 'text')
                ->rule('numeric');

        $form->add('international', null, 'container')->content('
            <hr />
            <h4 class="col-sm-offset-2 col-sm-10">'.trans('labels.international').'</h4>
        ');

            $form->add('full_price_intl_first_class', trans('labels.full_price_intl_first_class'), 'text')
                ->rule('numeric');

            $form->add('additional_price_intl_first_class', trans('labels.additional_price_intl_first_class'), 'text')
                ->rule('numeric');

            $form->add('full_price_intl_priority_mail', trans('labels.full_price_intl_priority_mail'), 'text')
                ->rule('numeric');

            $form->add('additional_price_intl_priority_mail', trans('labels.additional_price_intl_priority_mail'), 'text')
                ->rule('numeric');

        $form->add('templates_hr', null, 'container')->content('<hr />');

        $form->add('templates', null, 'container')->content('
            <div class="form-group clearfix">
                <label class="col-sm-2 control-label">'.trans('labels.products').'</label>
                <div class="col-sm-10">
                    <multiselect
                        :allow-empty="true"
                        :close-on-select="false"
                        :clear-on-select="true"
                        :options="availableOptions"
                        :selected.sync="selectedOptions"
                        :searchable="true"
                        :multiple="true"
                        :custom-label="styleTemplateSelectLabel"
                        option-partial="customOptionPartial"
                        select-label=""
                        selected-label=""
                        placeholder="'.trans('actions.choose_products').'"
                        key="id"
                        label="name"
                        @update="onOptionSelected"
                        />
                </div>

                <div class="hidden">
                    <input
                        v-for="selectedOption in selectedOptions"
                        type="text"
                        name="template_ids[]"
                        :value="selectedOption.id"
                        />
                </div>
            </div>
        ');

        // ---------------

        $form->link('/admin/orders/shipping', trans('actions.back'), 'BL', [
            'class' => 'btn btn-default ml-10'
        ]);

        $form->submit(trans('actions.save'), 'BR');

        return $form;
    }

    protected function form($form)
    {
        $create = ($form->action == 'insert');

        $form = $this->addForm($form);

        $form->saved(function() use ($form, $create) {

            if ($form->action == 'delete') {
                flash()->success(trans('messages.deleted'));
            }
            else {
                // update templates assignments
                    $template_ids = (array)Input::get('template_ids');

                    $form->model->templates()->sync($template_ids);
                    $form->model->load('templates');

                    \Event::fire(
                        new \App\Events\ProductModelTemplate\ShippingGroupAssignedEvent(
                            $form->model->templates
                        )
                    );

                flash()->success(trans('messages.saved'));
                if ($create) {
                    return redirect(url('/admin/orders/shipping/'.$form->model->id.'/edit'));
                }
            }
            return redirect()->back();
        });

        if ($form->model->id) {
            $subtitle = trans('labels.editing').' - #'.$form->model->id;
        }
        else {
            $subtitle = trans('labels.adding');
        }

        $formView = $form->view();

        if ($form->hasRedirect()) {
            return $form->getRedirect();
        }

        $templates = ProductModelTemplate::getAvailableForShippingGroups($form->model);

        $template_id = Input::get('preselect_template_id');
        if ($template_id) {
            $preselectedTemplates = ProductModelTemplate::find($template_id);
            $form->model->templates->push($preselectedTemplates);
        }

        return view('admin.pages.order.shipping_setting_edit', [
            'title' => trans('labels.shipping_settings'),
            'subtitle' => $subtitle,
            'form' => $formView,
            'formObject' => $form,
            'model' => $form->model,
            'templates' => FractalManager::serializeCollection($templates, new ProductModelTemplateIncludedTransformer)
        ]);
    }

}
