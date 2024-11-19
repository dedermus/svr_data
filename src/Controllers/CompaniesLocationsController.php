<?php

namespace Svr\Data\Controllers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Svr\Core\Models\SystemUsers;
use Svr\Data\Actions\CompanyData;
use Svr\Data\Models\DataCompaniesLocations;

use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Grid\Displayers\Actions\DropdownActions;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;

class CompaniesLocationsController extends AdminController
{
    protected string $model;
    protected mixed $model_obj;
    protected $title;
    protected string $trans;
    protected array $all_columns_obj;

    public function __construct()
    {
        $this->model = DataCompaniesLocations::class;
        $this->model_obj = new $this->model;                                                // Модель
        $this->trans = 'svr-data-lang::data.';                                                          // Переводы
        $this->title = __($this->trans . 'companies_locations');                      // Заголовок
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
            $filter->equal('company_location_id', 'company_location_id');
            $filter->equal('company_id', 'company_id');
            $filter->equal('region_id', 'region_id');
            $filter->equal('district_id', 'district_id');

            $filter->where(function ($query)
            {
                $query->whereRaw("company_id IN (SELECT company_id FROM data.data_companies WHERE company_name_short ILIKE '%{$this->input}%')");
            }, 'company_name_short', 'company_name_short');

            $filter->equal('location_status', 'location_status')
                ->select(function () {
                    return SystemStatusEnum::get_option_list();
                });

            $filter->equal('location_status_delete', 'location_status_delete')
                ->select(function () {
                    return SystemStatusDeleteEnum::get_option_list();
                });
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
                case 'company_location_id':
                    $grid->column($value_name, 'ID')->sortable();
                break;
                case 'company_id':
                    $grid->column($value_name, $value_label)->help($trans)->sortable();
                    $grid->column('company', 'company_name_short')->display(function ($company) {
                        return $company['company_name_short'];
                    })->help(__($this->trans.'company_name_short'));
                    break;
                case 'region_id':
                    $grid->column($value_name, $value_label)->help($trans)->sortable();
                    $grid->column('region', 'region_name')->display(function ($region) {
                        return $region['region_name'];
                    })->help(__('directories.region_name'));
                break;
                case 'district_id':
                    $grid->column($value_name, $value_label)->help($trans)->sortable();
                    $grid->column('district', 'district_name')->display(function ($district) {
                        return $district['district_name'] ?? null;
                    })->help(__('directories.district_name'));
                break;
                case $this->model_obj->getCreatedAtColumn():
                case $this->model_obj->getUpdatedAtColumn():
                    $grid->column($value_name, $value_label)
                    ->display(function ($value) {return Carbon::parse($value);})
                    ->xx_datetime()
                    ->help($trans)->sortable();
                break;
                // Отображение остальных колонок
                default:
                    $grid->column($value_name, $value_label)->help($trans)->sortable();
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
                case 'region_id':
                    $show->field($value_name, $value_label)
                        ->xx_help(msg:$trans);
                    $show->field('region', 'region_name')->as(function ($region) {
                        return $region['region_name'];
                    })->xx_help(__('directories.region_name'));
                break;
                case 'district_id':
                    $show->field($value_name, $value_label)
                        ->xx_help(msg:$trans);
                    $show->field('district', 'district_name')->as(function ($district) {
                        return $district['district_name'] ?? null;
                    })->xx_help(__('directories.district_name'));
                break;
                // Отображение остальных полей
                default:
                    $show->field($value_name, $value_label)
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
        $form->text('company_location_id', 'company_location_id')
            ->readonly(true)
            ->help(__($this->trans . 'company_location_id'));

        $form->text('company_id','company_id')
            ->readonly(true)
            ->help(__($this->trans . 'company_id'));

        $form->select('region_id', 'region')
            ->options(DirectoryCountriesRegion::all()->pluck('region_name', 'region_id'))
            ->load('district_id', '/admin/directories/listDistricts', 'district_id', 'district_name')
            ->help(trans(strtolower($this->trans . 'region_id')));

        $form->select('district_id', 'district_id')
            ->options(function () {
                return DirectoryCountriesRegionsDistrict::where('region_id', $this->toArray()['region_id'])->pluck('district_name', 'district_id');
            })
            ->help(trans(strtolower($this->trans . 'district_id')));

        $form->select('location_status', __('location_status'))
            ->options(SystemStatusEnum::get_option_list())
            ->help(trans(strtolower($this->trans . 'location_status')))
            ->default('enabled');

        $form->select('location_status_delete', __('location_status_delete'))
            ->options(SystemStatusDeleteEnum::get_option_list())
            ->help(trans(strtolower($this->trans . 'location_status_delete')))
            ->default('enabled');

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
                $this->model_obj->companyLocationCreate(request());
            } else
                // обновляется текущая страница формы.
                if ($form->isEditing())
                {
                    $this->model_obj->companyLocationUpdate(request());
                }
        });

        return $form;
    }


    /**
     * Возвращает список локаций компаний поисковым запросом.
     *
     * @return array Список локаций компаний с их идентификаторами и названиями.
     */
    public function company_locations_list()
    {
        $request = Request::instance();
        $return_list	= [];
        $search_string	= $request->request->get('query');

        if(!empty($search_string))
        {
            $result = SystemUsers::where('user_last', 'ilike', '%'.$search_string.'%')->limit(10)->get(['user_id', 'user_first', 'user_middle', 'user_last']);

            $result = DB::table('data.data_companies_locations')
                ->join('data.data_companies', 'data.data_companies_locations.company_id', '=', 'data.data_companies.company_id')
                ->join('directories.countries_regions', 'directories.countries_regions.region_id', '=', 'data.data_companies_locations.region_id')
                ->join('directories.countries_regions_districts', 'directories.countries_regions_districts.district_id', '=', 'data.data_companies_locations.district_id')
                ->where('company_name_short', 'ilike', '%'.$search_string.'%')
                ->limit(10)
                ->get(['company_location_id', 'company_name_short', 'region_name', 'district_name'])->toArray();

            if($result && count($result) > 0)
            {
                foreach($result as $company_location)
                {
                    $company_location = (array)$company_location;
                    $return_list[]	= ['company_location_id' => $company_location['company_location_id'],
                        'company_location_name' => $company_location['company_name_short'].' | '
                            .$company_location['region_name'].' | '.$company_location['district_name']];
                }

                return $return_list;
            }else{
                return [];
            }
        }else{
            return [];
        }
    }
}
