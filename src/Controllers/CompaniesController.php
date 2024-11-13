<?php

namespace Svr\Data\Controllers;


use Svr\Data\Actions\CompanyLocationsList;
use Svr\Data\Actions\CompanyObjectsList;

use Svr\Data\Models\DataCompanies;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Grid\Displayers\Actions\DropdownActions;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Widgets\Table;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;

class CompaniesController extends AdminController
{
    protected string $model;
    protected mixed $model_obj;
    protected $title;
    protected string $trans;
    protected array $all_columns_obj;

    public function __construct()
    {
        $this->model = DataCompanies::class;
        $this->model_obj = new $this->model;                                                // Модель
        $this->trans = 'svr-data-lang::data.';                                                          // Переводы
        $this->title = trans($this->trans . 'companies');                              // Заголовок
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
            $content->description(trans('admin.description'));
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
            $content->description(trans('admin.create'));
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
            ->description(trans('admin.edit'))
            ->row($this->form($id)->edit($id));
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
            ->description(trans('admin.show'))

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

        $grid->setActionClass(DropdownActions::class);

        $grid->actions(function ($actions) {
            $actions->add(new CompanyObjectsList());
            $actions->add(new CompanyLocationsList());
        });

        // Настройки фильтров
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('company_id', 'company_id');
            $filter->ilike('company_base_index', 'company_base_index');
            $filter->ilike('company_name_short', 'company_name_short');
            $filter->ilike('company_name_full', 'company_name_full');
            $filter->ilike('company_inn', 'company_inn');
            $filter->ilike('company_kpp', 'company_kpp');
            $filter->ilike('company_guid', 'company_guid_self');

            $filter->equal('company_status', 'company_status')
                ->select(function () {
                    return SystemStatusEnum::get_option_list();
                });

            $filter->equal('company_status_delete', 'company_status_delete')
                ->select(function () {
                    return SystemStatusDeleteEnum::get_option_list();
                });
        });

        foreach ($this->all_columns_obj as $key => $value) {
            $value_name = $value['name'];
            $value_label = $value_name;
            $trans = trans($this->trans . $value_name);

            match ($value_name) {
                // Индивидуальные настройки для отображения колонок:company_created_at, update_at, company_id
                'company_id' => $grid->column($value_name, 'ID')->sortable(),
                $this->model_obj->getCreatedAtColumn(), $this->model_obj->getUpdatedAtColumn() => $grid
                    ->column($value_name, $value_label)
                    ->display(function ($value) {return Carbon::parse($value);})
                    ->xx_datetime()
                    ->help($trans),

                // Отображение остальных колонок
                default => $grid->column($value_name, $value_label)->help($trans),
            };
        }

        $grid->column('objects', __($this->trans.'modal_company_objects'))->display(function ($objects) {
            $count_object = count($objects);
            return "Количество ПО: {$count_object}";
        })->modal('Поднадзорные объекты', function ($model) {
            $objects = $model->objects()->get()->map(function ($object) {
                return $object->only(['company_object_id', 'company_object_approval_number', 'company_object_address_view']);
            });

            return new Table(['company_object_id', 'company_object_approval_number', 'company_object_address_view'], $objects->toArray());
        });

        $grid->column('locations', __($this->trans.'modal_company_locations'))->display(function ($locations) {
            $count_location = count($locations);
            return "Количество локаций: {$count_location}";
        })->modal('Локации компаний', function ($model) {
            $locations = $model->locations()->get()->map(function ($location) {
                $returned_location = $location->only(['company_location_id', 'region_id', 'district_id']);
                $returned_location['region_name'] = $location->region()->get('region_name')->pluck('region_name')[0];
                $returned_location['district_name'] = isset($location->district()->get('district_name')->pluck('district_name')[0])
                    ? $location->district()->get('district_name')->pluck('district_name')[0]
                    : '';
                return $returned_location;
            });

            return new Table(['company_location_id', 'region_id', 'district_id', 'region_name', 'district_name'], $locations->toArray());
        });

        /*$grid->column('link_company_objects', __($this->trans.'link_company_objects'))->display(function () {
            return "<a href='svr_companies_objects?company_id=".$this->company_id."' target='_blank'>Открыть список</a>";
        });*/

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
            $trans = trans(strtolower($this->trans . $value_name));
            match ($value_name) {
                // Индивидуальные настройки для отображения полей:created_at, update_at
                $this->model_obj->getCreatedAtColumn(), $this->model_obj->getUpdatedAtColumn(), 'company_date update objects' => $show
                    ->field($value_name, $value_label)
                    ->xx_datetime(),

                'company_id' => $show->field($value_name, $value_label)
                    ->xx_help(msg:$trans),

                // Отображение остальных полей
                default => $show->field($value_name, $value_label)
                    ->xx_help(msg:$trans),
            };
        }
        $show->field('link_company_objects',  __($this->trans.'link_company_objects'))->unescape()->as(function (){
            return "<a href='/admin/data/svr_companies_objects?company_id=".$this->company_id."' target='_blank'>Открыть список ПО</a>";
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @param bool $id
     * @return Form
     */
    protected function form($id = false): Form
    {
        $form = new Form($this->model_obj);

        $form->display('company_id', __('company_id'))
            ->help(trans(strtolower($this->trans . 'company_id')));

        $form->text('company_base_index', __('company_base_index'))
            ->help(trans(strtolower($this->trans . 'company_base_index')));

        $form->text('company_guid_vetis', __('company_guid_vetis'))
            ->help(trans(strtolower($this->trans . 'company_guid_vetis')));

        $form->text('company_guid', __('company_guid'))
            ->help(trans(strtolower($this->trans . 'company_guid')));

        $form->text('company_name_short', __('company_name_short'))
            ->help(trans(strtolower($this->trans . 'company_name_short')));

        $form->text('company_name_full', __('company_name_full'))
            ->help(trans(strtolower($this->trans . 'company_name_full')));

        $form->text('company_address', __('company_address'))
            ->help(trans(strtolower($this->trans . 'company_address')));

        $form->text('company_inn', __('company_inn'))
            ->help(trans(strtolower($this->trans . 'company_inn')));

        $form->text('company_kpp', __('company_kpp'))
            ->help(trans(strtolower($this->trans . 'company_kpp')));

        /*$form->multipleSelect('company_objects', __('company_objects'))
            ->options(function () {
                $returned_data = [];
                $company_objects = DataCompaniesObjects::where('company_id', $this->toArray()['company_id'])->get();
                foreach ($company_objects as $company_object)
                {
                    $returned_data[$company_object['company_object_id']] = $company_object['company_object_approval_number'];
                }
                return $returned_data;})
            ->ajax('/admin/svr_companies_objects_exp/companyObjects', 'company_object_id', 'company_object_approval_number')
            ->value(DataCompaniesObjects::companyObjectsGetByCompanyId($id))
            ->help(__($this->trans.'companies_objects'));*/

        $form->select('company_status', __('company_status'))
            ->options(SystemStatusEnum::get_option_list())
            ->help(trans(strtolower($this->trans . 'company_status')))
            ->default('enabled');

        $form->select('company_status_horriot', __('company_status_horriot'))
            ->options(SystemStatusEnum::get_option_list())
            ->help(trans(strtolower($this->trans . 'company_status_horriot')))
            ->default('enabled');

        $form->select('company_status_delete', __('company_status_delete'))
            ->options(SystemStatusDeleteEnum::get_option_list())
            ->help(trans(strtolower($this->trans . 'company_status_delete')))
            ->default('active');

        $form->display('company_date_update_objects', 'company_date_update_objects')
            ->help(trans('svr.updated_at'));

        $form->display('created_at', 'created_at')
            ->help(trans('svr.created_at'));

        $form->display('updated_at', 'updated_at')
            ->help(trans('svr.updated_at'));

        // обработка формы
        $form->saving(function (Form $form)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                $this->model_obj->companyCreate(request());
            } else
                // обновляется текущая страница формы.
                if ($form->isEditing())
                {
                    $success = new MessageBag([
                        'title'   => 'Saved',
                        'message' => 'Sweet this is saved',
                    ]);
                    return back()->with(compact('success'));
                }
        });

        return $form;
    }
}
