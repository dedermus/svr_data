<?php

namespace Svr\Data\Controllers;


use Svr\Data\Actions\CompanyData;
use Svr\Data\Models\DataCompaniesObjects;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Grid\Displayers\Actions\DropdownActions;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;

class CompaniesObjectsController extends AdminController
{
    protected string $model;
    protected mixed $model_obj;
    protected $title;
    protected string $trans;
    protected array $all_columns_obj;

    public function __construct()
    {
        $this->model = DataCompaniesObjects::class;
        $this->model_obj = new $this->model;                                                // Модель
        $this->trans = 'svr-data-lang::data.';                                                          // Переводы
        $this->title = __($this->trans . 'companies_objects');                      // Заголовок
        $this->all_columns_obj = Schema::getColumns($this->model_obj->getTable());          // Все столбцы
    }

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content): Content
    {
        return Admin::content(function (Content $content) {
            $content->header($this->title);
            $content->description(__('admin.description'));
            $content->body($this->grid());
        });
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content): Content
    {
        return Admin::content(function (Content $content) {
            $content->header($this->title);
            $content->description(__('admin.create'));
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
    public function edit($id, Content $content): Content
    {
        return $content
            ->title($this->title)
            ->description(__('admin.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Show interface.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content): Content
    {
        return $content
            ->title($this->title)
            ->description(__('admin.show'))

            // Оформление подсказок (xx_help)
            ->css('.row .help-block {
                font-size: .9rem;
                color: #72777b
            }')
            ->body($this->detail($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid($this->model_obj);

        $grid->disableCreateButton();

        $grid->fixColumns(-1);

        // Настройки фильтров
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('company_object_id', 'company_object_id');
            $filter->equal('company_id', 'company_id');

            $filter->ilike('company_object_guid_self', 'company_object_guid_self');
            $filter->ilike('company_object_approval_number', 'company_object_approval_number');

            $filter->where(function ($query)
            {
                $query->whereRaw("company_id IN (SELECT company_id FROM data.data_companies WHERE company_name_short ILIKE '%{$this->input}%')");
            }, 'company_name_short', 'company_name_short');
        });

        $grid->setActionClass(DropdownActions::class);

        $grid->actions(function ($actions) {
            $actions->add(new CompanyData());
        });

        foreach ($this->all_columns_obj as $key => $value) {
            $value_name = $value['name'];
            $value_label = $value_name;
            $trans = __($this->trans . $value_name);

            switch ($value_name) {
                // Индивидуальные настройки для отображения колонок:company_created_at, update_at, company_id
                case 'company_object_id':
                    $grid->column($value_name, 'ID')->sortable();
                break;
                case $this->model_obj->getCreatedAtColumn():
                case $this->model_obj->getUpdatedAtColumn():
                    $grid->column($value_name, $value_label)
                    ->display(function ($value) {return Carbon::parse($value);})
                    ->xx_datetime()
                    ->help($trans);
                break;
                case 'company_id':
                    $grid->column($value_name, $value_label)->help($trans);
                    $grid->column('company', 'company_name_short')->display(function ($company) {
                        return $company['company_name_short'];
                    })->help(__($this->trans.'company_name_short'));
                break;
                // Отображение остальных колонок
                default:
                    $grid->column($value_name, $value_label)->help($trans);
                break;
            };
        }

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail(mixed $id): Show
    {
        $show = new Show($this->model::findOrFail($id));
        foreach ($this->all_columns_obj as $key => $value) {
            $value_name = $value['name'];
            $value_label = $value_name;
            $trans = __($this->trans . $value_name);
            switch ($value_name) {
                // Индивидуальные настройки для отображения полей:created_at, update_at
                case $this->model_obj->getCreatedAtColumn():
                case $this->model_obj->getUpdatedAtColumn():
                    $show->field($value_name, $value_label)
                        ->xx_datetime()
                        ->xx_help(msg:$trans);
                break;
                case 'company_id':
                    $show->field($value_name, $value_label)
                        ->xx_help(msg:$trans);
                    $show->field('company', 'company_name_short')->as(function ($company) {
                        return $company['company_name_short'];
                    })->xx_help(msg:$trans);
                break;
                // Отображение остальных полей
                default: $show->field($value_name, $value_label)
                    ->xx_help(msg:$trans);
                break;
            };
        }

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form($this->model_obj);
        $form->display('company_object_id', 'company_object_id')
            ->help(__($this->trans . 'company_object_id'));

        $form->display('company_id','company_id')
            ->help(__($this->trans . 'company_id'));

        $form->text('company_object_guid_self', 'company_object_guid_self')
            ->help(__($this->trans . 'company_object_guid_self'))
            ->required();

        $form->text('company_object_guid_horriot', 'company_object_guid_horriot')
            ->help(__($this->trans . 'company_object_guid_horriot'))
            ->required();

        $form->text('company_object_approval_number', 'company_object_approval_number')
            ->help(__($this->trans . 'company_object_approval_number'))
            ->required();

        $form->text('company_object_address_view', 'company_object_address_view')
            ->help(__($this->trans . 'company_object_address_view'))
            ->required();

        $form->select('company_object_is_favorite', 'company_object_is_favorite')
            ->options(['false', 'true'])
            ->default('false')
            ->required()
            ->help(__($this->trans . 'company_object_is_favorite'));

        $form->display('created_at', 'created_at')
            ->help(__('svr.created_at'));

        $form->display('updated_at', 'updated_at')
            ->help(__('svr.updated_at'));

        // обработка формы
        $form->saving(function (Form $form)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                $this->model_obj->companyObjectCreate(request());
            } else
                // обновляется текущая страница формы.
                if ($form->isEditing())
                {
                    $this->model_obj->companyObjectUpdate(request());
                }
        });

        return $form;
    }

    public function companyObjects()
    {
        $company_objects = [];
        $request = Request::instance();
        $search_string = $request->request->get('query');
        if (!empty($search_string))
        {
            $search_array = array_map(function($item) {
                return '%'.$item.'%';
            }, explode(' ', $search_string));
            $company_objects = DataCompaniesObjects::whereAny(['company_object_approval_number', 'company_object_address_view'],
                'ilike', ['%'.$search_array[0].'%'])->limit(10)->get(['company_object_id', 'company_object_approval_number']);
        }
        return $company_objects;
    }


}
