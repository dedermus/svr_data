<?php

namespace Svr\Data\Controllers;

use Svr\Core\Enums\ApplicationStatusEnum;
use Svr\Core\Models\SystemUsers;

use Svr\Data\Actions\ApplicationAnimals;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataCompanies;

use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Grid\Displayers\Actions\DropdownActions;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;


class ApplicationsController extends AdminController
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('svr-data-lang::data.application.title'));
            $content->description(trans('svr-data-lang::data.application.description'));
            $content->body($this->grid());
        });
    }

    /**
     * Create interface.
     *
     * @param Content $content
     */
    public function create(Content $content)
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('svr-data-lang::data.application.title'));
            $content->description(trans('svr-data-lang::data.application.create'));
            $content->body($this->form());
        });
    }

    /**
     * Edit interface.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        //dd($this->form()->edit($id));

        return $content
            ->title(trans('svr-data-lang::data.application.title'))
            ->description(trans('svr-data-lang::data.application.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Edit interface.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(__('svr-data-lang::data.application.title'))
            ->description(__('svr-data-lang::data.application.description'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function animals_list($id, Content $content)
    {
        return $content
            ->title(__('svr-data-lang::data.application.title'))
            ->description(__('svr-data-lang::data.application.description'))
            ->body($this->detail($id));
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Applications';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new DataApplications());

        $grid->column('application_id', __('svr-data-lang::data.application.application_id'))->sortable();
        $grid->column('company_location', __('svr-data-lang::data.application.company_location'))->display(function($company_location_data)
        {
            if ($company_location_data && is_array($company_location_data))
            {
                $company_data	= DataCompanies::find($company_location_data['company_location_id']);

                if($company_data)
                {
                    return $company_data['company_name_full'].' [company_location_id: '.$company_location_data['company_location_id'].' company_id: '.$company_data['company_id'].']';
                }else{
                    return $company_location_data['company_location_id'].']';
                }
            } else {
                return '-';
            }

        })->sortable();
        $grid->column('user', __('svr-data-lang::data.application.user'))->display(function($user)
        {
            if($user && is_array($user))
            {
                return $user['user_last'].' '.$user['user_first'].' '.$user['user_middle'].' [user_id: '.$user['user_id'].']';
            }else {
                return '-';
            }
        })->sortable();
        $grid->column('doctor', __('svr-data-lang::data.application.doctor'))->display(function($user)
        {
            if($user && is_array($user))
            {
                return $user['user_last'].' '.$user['user_first'].' '.$user['user_middle'].' [user_id: '.$user['user_id'].']';
            }else {
                return '-';
            }
        })->sortable();
        $grid->column('application_date_create', __('svr-data-lang::data.application.application_date_create'))->sortable();
        $grid->column('application_date_horriot', __('svr-data-lang::data.application.application_date_horriot'))->sortable();
        $grid->column('application_date_complete', __('svr-data-lang::data.application.application_date_complete'))->sortable();
        $grid->column('application_status', __('svr-data-lang::data.application.application_status'))->sortable();
//		$grid->column('application_created_at', __('svr-data-lang::data.application.application_created_at'))->sortable();
//		$grid->column('update_at', __('svr-data-lang::data.application.update_at'))->sortable();

        $grid->actions(function($actions){
//			$actions->disableView();
//			$actions->disableEdit();
            $actions->add(new ApplicationAnimals());
        });
        $grid->fixColumns(-1);

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('application_id', __('svr-data-lang::data.application.application_id'));
            $filter->where(function($query)
            {
                $query->whereRaw("company_location_id IN (SELECT company_location_id FROM data.data_companies_locations WHERE company_id = (SELECT company_id FROM data.data_companies WHERE company_base_index LIKE '%{$this->input}%'))");
            }, 'Базовый индекс', 'base_index');
            $filter->equal('company_location_id', __('svr-data-lang::data.application.company_location'));
            $filter->equal('user_id', __('svr-data-lang::data.application.user_id'));
            $filter->equal('doctor_id', __('svr-data-lang::data.application.doctor_id'));
            $filter->in('application_status', __('svr-data-lang::data.application.application_status'))->multipleSelect(ApplicationStatusEnum::get_option_list());
        });

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->setActionClass(DropdownActions::class);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(DataApplications::findOrFail($id));

        $show->field('application_id', __('svr-data-lang::data.application.application_id'));
//		$show->field('company_location_id', __('svr-data-lang::data.application.company_location_id'));
//		$show->field('user_id', __('svr-data-lang::data.application.user_id'));
//		$show->field('doctor_id', __('svr-data-lang::data.application.doctor_id'));
        $show->field('user', __('svr-data-lang::data.application.user'))->as(function($user)
        {
            return $user->user_last.' '.$user->user_first.' '.$user->user_middle.' [user_id: '.$user->user_id.']';
        });
        $show->field('doctor', __('svr-data-lang::data.application.doctor'))->as(function($user)
        {
            return $user->user_last.' '.$user->user_first.' '.$user->user_middle.' [user_id: '.$user->user_id.']';
        });
        $show->field('company_location', 'Хозяйство')->as(function($company_location_data)
        {
            $company_data	= DataCompanies::findOrFail($company_location_data['company_location_id']);

            return $company_data['company_name_full'].' [company_location_id: '.$company_location_data['company_location_id'].' company_id: '.$company_data['company_id'].']';
        });
        $show->field('application_date_create', __('svr-data-lang::data.application.application_date_create'));
        $show->field('application_date_horriot', __('svr-data-lang::data.application.application_date_horriot'));
        $show->field('application_date_complete', __('svr-data-lang::data.application.application_date_complete'));
        $show->field('application_status', __('svr-data-lang::data.application.application_status'));
        $show->field('created_at', __('svr-data-lang::data.application.created_at'));
        $show->field('updated_at', __('svr-data-lang::data.application.updated_at'));
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DataApplications());

        $form->text('application_id', __('svr-data-lang::data.application.application_id'))
            ->required()
            ->readonly(true)
            ->rules('required')
            ->help(__('svr-data-lang::data.application.application_id'));
        $form->text('company_location_id', __('svr-data-lang::data.application.company_location_id'))
            ->required()
            ->rules('required')
            ->help(__('svr-data-lang::data.application.company_location_id'));
        $form->select('user_id', __('svr-data-lang::data.application.user_name'))
            ->required()
            ->rules('required')
            ->help(__('svr-data-lang::data.application.user_id'))
            ->options(function(){
                $user = SystemUsers::where('user_id', $this->toArray()['user_id'])->get()[0];

                return [$user['user_id'] => $user['user_last'].' '.$user['user_first'].' '.$user['user_middle']];
            })
            ->ajax('/admin/api_users/users_list', 'user_id', 'user_fullname');
        $form->select('doctor_id', __('svr-data-lang::data.application.doctor_name'))
            ->required()
            ->rules('required')
            ->help(__('svr-data-lang::data.application.doctor_id'))
            ->options(function(){
                $user = SystemUsers::where('user_id', $this->toArray()['doctor_id'])->get()[0];

                return [$user['user_id'] => $user['user_last'].' '.$user['user_first'].' '.$user['user_middle']];
            })
            ->ajax('/admin/api_users/users_list', 'user_id', 'user_fullname');
        $form->date('application_date_create', __('svr-data-lang::data.application.application_date_create'))
            ->required()
            ->rules('required')
            ->help(__('svr-data-lang::data.application.application_date_create'));
        $form->date('application_date_horriot', __('svr-data-lang::data.application.application_date_horriot'))
            ->help(__('svr-data-lang::data.application.application_date_horriot'));
        $form->date('application_date_complete', __('svr-data-lang::data.application.application_date_complete'))
            ->help(__('svr-data-lang::data.application.application_date_complete'));
        $form->select('application_status', __('svr-data-lang::data.application.application_status'))
            ->options(ApplicationStatusEnum::get_option_list())
            ->default('enabled')->rules('required');

        $form->hidden('created_at', __('created_at'));
        $form->hidden('updated_at', __('updated_at'));

        return $form;
    }
}
