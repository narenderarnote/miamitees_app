<?php

namespace App\Http\Controllers\Admin;

use DataForm;
use Input;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ProductModelTemplate;
use App\Transformers\Product\ProductModelTemplateBriefTransformer;
use App\Transformers\PriceModifier\PriceModifierBriefTransformer;

class UsersController extends AdminController
{
    use \App\Http\Controllers\Admin\Traits\RapydControllerTrait;

    protected function getModelForAdd()
    {
        return new User();
    }

    protected function getModelForEdit($id)
    {
        return User::find($id);
    }

    protected function getModelForDelete($id)
    {
        return User::find($id);
    }

    /**
     * All patients
     */
    public function all(Request $request)
    {
        $model = new User();

        $filter = \DataFilter::source($model);
        $filter->add('user.email', trans('labels.email'), 'text');
        $filter->submit(trans('actions.search'));
        $filter->reset(trans('actions.reset'));
        $filter->build();

        $grid = \DataGrid::source($filter);

        $grid->add('name', trans('labels.name'), false);
        $grid->add('email', trans('labels.email'), false);
        $grid->add('status', trans('labels.status'), false)
            ->cell(function($value, $row) {
                return $row->getStatusName();
            });
        $grid->add('updated_created', trans('labels.updated_created'))
            ->cell(function($value, $row) {
                return '
                    <time
                        datetime="'.$row->updatedAtTZ().'"
                        title="'.$row->updatedAtTZ().'"
                        data-format="">
                        '.$row->updatedAtTZ().'
                    </time>
                    /
                    <time
                        datetime="'.$row->createdAtTZ().'"
                        title="'.$row->createdAtTZ().'"
                        data-format="">
                        '.$row->createdAtTZ().'
                    </time>
                ';
            });
        $grid->add('products', trans('labels.products'), false)
            ->cell(function($value, $row) {
                return '
                    <a class="btn btn-default btn-xs" href="'.url('/admin/products?user_email='.$row->id.'&search=1').'">
                        <i class="fa fa-shopping-bag"></i>
                        '.trans_choice('labels.n_products', count($row->products)).'
                    </a>
                ';
            });
        $grid->add('id', trans('labels.actions'))->cell(function($value, $row) {
            return '
                <a class="btn btn-xs btn-primary" href="'.url('/admin/users/'.$value.'/edit').'">
                    <i class="fa fa-edit"></i>
                    '.trans('actions.edit').'
                </a>
            ';
        });

        $grid->orderBy('created_at','desc');
        $grid->paginate(10);

        return view('admin.pages.default.grid', [
            'title' => trans('labels.users'),
            'grid' => $grid,
            'filter' => $filter
        ]);
    }

    protected function addUserForm($form)
    {
        // user
        $form->add('user_header', null, 'container')
            ->content('
                <h4>'.trans('labels.user').'</h4>
            ');

            $form
                ->add('email', trans('labels.email'), 'text')
                ->rule('required|email|max:255|unique:users,email,'.$form->model->id);

            $form
                ->add('username', trans('labels.username'), 'text')
                ->rule('required|string|max:255|unique:users,username,'.$form->model->id);

            $form
                ->add('first_name', trans('labels.first_name'), 'text')
                ->rule('string');

            $form
                ->add('last_name', trans('labels.last_name'), 'text')
                ->rule('string');

            $form
                ->add('password', trans('labels.password'), 'password')
                ->rule('min:6')
                ->attr([
                    'autocomplete' => 'new-password'
                ]);

        if ($form->model->id && !$form->model->isAdmin()) {

            $form->link('/admin/users/'.$form->model->id.'/delete', trans('actions.delete'), 'BR', [
                'class' => 'js-confirm btn btn-danger mr-10'
            ]);

            if (!$form->model->isActive()) {
                $form->link('/admin/users/'.$form->model->id.'/activate', trans('actions.activate_user'), 'BR', [
                    'class' => 'btn btn-success mr-10'
                ]);
            }

            if (!$form->model->isBanned()) {
                $form->link('/admin/users/'.$form->model->id.'/ban', trans('actions.ban_user'), 'BR', [
                    'class' => 'btn btn-warning mr-10'
                ]);
            }

            if (!$form->model->isEmailConfirmed()) {
                $form->link('/admin/users/'.$form->model->id.'/confirm', trans('actions.confirm_user_email'), 'BR', [
                    'class' => 'btn btn-info mr-10'
                ]);
            }
        }

        if ($form->model->id && !auth()->user()->isMe($form->model)) {
            $form->link('/spark/kiosk/users/impersonate/'.$form->model->id, trans('actions.impersonate'), 'TR', [
                'class' => 'btn btn-info mr-10'
            ]);
        }

        $form->link('/admin/users', trans('actions.back_to_users'), 'BL', [
            'class' => 'btn btn-default ml-10'
        ]);

        $form->submit(trans('actions.save'), 'BR');

        return $form;
    }

    protected function form($form)
    {
        $create = ($form->action == 'insert');

        $form = $this->addUserForm($form);

        $form->saved(function() use ($form, $create) {

            if ($form->action == 'delete') {

                foreach($form->model->stores as $store) {
                    $store->delete();
                }

                foreach($form->model->products as $product) {
                    $product->delete();
                }

                flash()->success(trans('messages.deleted'));
                return redirect(url('/admin/users/'));
            }
            else {
                $form->model->setPassword($form->fields['password']->value);
                $form->model->save();

                flash()->success(trans('messages.saved'));

                if ($create) {
                    return redirect(url('/admin/users/'.$form->model->id.'/edit'));
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

        $productModelTemplates = ProductModelTemplate::getAllVisible();
        $productModelTemplates = $this->serializeCollection($productModelTemplates, new ProductModelTemplateBriefTransformer);

        $priceModifiers = [];
        if ($form->model->id) {
            $priceModifiers = $this->serializeCollection(
                $form->model->priceModifiers, new PriceModifierBriefTransformer
            );
        }

        return view('admin.pages.user.edit', [
            'title' => trans('labels.users'),
            'subtitle' => $subtitle,
            'form' => $formView,
            'formObject' => $form,
            'priceModifiers' => $priceModifiers,
            'productModelTemplates' => $productModelTemplates
        ]);
    }

    public function activate(Request $request, $id)
    {
        if( ! $user = User::find($id)) {
            return response()->apiError(
                trans('messages.user_not_found'),
                404
            );
        }

        if (!$user->isAdmin()) {
            $user->changeStatusTo(User::STATUS_ACTIVE);
        }

        return redirect()->back();
    }

    public function ban(Request $request, $id)
    {
        if( ! $user = User::find($id)) {
            return response()->apiError(
                trans('messages.user_not_found'),
                404
            );
        }

        if (!$user->isAdmin()) {
            $user->changeStatusTo(User::STATUS_BANNED);
        }

        return redirect()->back();
    }

    public function confirm(Request $request, $id)
    {
        if( ! $user = User::find($id)) {
            return response()->apiError(
                trans('messages.user_not_found'),
                404
            );
        }

        $user->is_confirmed = 1;
        $user->confirmation_code = null;
        $user->save();

        return redirect()->back();
    }
}
