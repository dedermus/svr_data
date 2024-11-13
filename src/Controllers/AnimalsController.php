<?php

namespace Svr\Data\Controllers;

use Svr\Data\Models\DataAnimals;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Enums\SystemSexEnum;
use Svr\Core\Enums\SystemBreedingValueEnum;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Widgets\Table;

use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryKeepingTypes;
use Svr\Directories\Models\DirectoryKeepingPurposes;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryOutBasises;
use Svr\Directories\Models\DirectoryOutTypes;


class AnimalsController extends AdminController
{
	/**
	 * Index interface.
	 *
	 * @return Content
	 */
	public function index(Content $content)
	{
		return Admin::content(function (Content $content) {
			$content->header(trans('svr-data-lang::data.animal.title'));
			$content->description(trans('svr-data-lang::data.animal.description'));
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
			$content->header(trans('svr-data-lang::data.animal.title'));
			$content->description(trans('svr-data-lang::data.animal.create'));
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
		return $content
			->title(trans('svr-data-lang::data.animal.title'))
			->description(trans('svr-data-lang::data.animal.edit'))
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
			->title(__('svr-data-lang::data.animal.title'))
			->description(__('svr-data-lang::data.animal.description'))
			->body($this->detail($id));
	}

	/**
	 * Title for current resource.
	 *
	 * @var string
	 */
	protected $title = 'Animals';

	/**
	 * Make a grid builder.
	 *
	 * @return Grid
	 */
	protected function grid(): Grid
	{
		$grid = new Grid(new DataAnimals());

		$grid->column('animal_id', __('svr-data-lang::data.animal.animal_id'))->sortable();
		$grid->column('company_location_id', __('svr-data-lang::data.animal.company_location_id'))->sortable();
		$grid->breed_id(__('svr-data-lang::data.animal.breed_name'))
			->select(DirectoryAnimalsBreeds::All(['breed_name', 'breed_id'])->pluck('breed_name', 'breed_id')->toArray())->sortable();
//		$grid->column('animal_task', __('svr-data-lang::data.animal.animal_task'))->sortable()->text();
		$grid->column('animal_guid_self', __('svr-data-lang::data.animal.animal_guid_self'))->sortable()->text();
		$grid->column('animal_guid_horriot', __('svr-data-lang::data.animal.animal_guid_horriot'))->sortable()->text();
		$grid->column('animal_number_horriot', __('svr-data-lang::data.animal.animal_number_horriot'))->sortable()->text();
		$grid->column('animal_nanimal', __('svr-data-lang::data.animal.animal_nanimal'))->sortable()->text();
		$grid->column('animal_nanimal_time', __('svr-data-lang::data.animal.animal_nanimal_time'))->sortable()->text();
		$grid->column('animal_code_chip.code_value', __('svr-data-lang::data.animal.animal_code_chip'));
		$grid->column('animal_code_left.code_value', __('svr-data-lang::data.animal.animal_code_left'))->sortable();
		$grid->column('animal_code_right.code_value', __('svr-data-lang::data.animal.animal_code_right'))->sortable();
		$grid->column('animal_code_rshn.code_value', __('svr-data-lang::data.animal.animal_code_rshn'))->sortable();
		$grid->column('animal_code_inv.code_value', __('svr-data-lang::data.animal.animal_code_inv'))->sortable();
		$grid->column('animal_code_device.code_value', __('svr-data-lang::data.animal.animal_code_device'))->sortable();
		$grid->column('animal_code_tattoo.code_value', __('svr-data-lang::data.animal.animal_code_tattoo'))->sortable();
		$grid->column('animal_code_import.code_value', __('svr-data-lang::data.animal.animal_code_import'))->sortable();
		$grid->column('animal_code_name.code_value', __('svr-data-lang::data.animal.animal_code_name'))->sortable();
//		$grid->column('animal_code_inv_value', __('svr-data-lang::data.animal.animal_code_inv_value'))->sortable();
//		$grid->column('animal_code_rshn_value', __('svr-data-lang::data.animal.animal_code_rshn_value'))->sortable();
		$grid->column('animal_date_create_record', __('svr-data-lang::data.animal.animal_date_create_record'))->sortable()->date();
		$grid->column('animal_date_birth', __('svr-data-lang::data.animal.animal_date_birth'))->sortable()->date();
		$grid->column('animal_date_import', __('svr-data-lang::data.animal.animal_date_import'))->sortable()->date();
		$grid->column('animal_date_income', __('svr-data-lang::data.animal.animal_date_income'))->sortable()->date();
//		$grid->column('animal_sex_id', __('svr-data-lang::data.animal.animal_sex_id'))->sortable();
		$grid->column('animal_sex', __('svr-data-lang::data.animal.animal_sex'))->sortable()->select(SystemSexEnum::get_option_list());
		$grid->column('animal_breeding_value', __('svr-data-lang::data.animal.animal_breeding_value'))->sortable()->select(SystemBreedingValueEnum::get_option_list());
		$grid->column('animal_colour', __('svr-data-lang::data.animal.animal_colour'))->sortable()->text();
		$grid->column('animal_place_of_keeping.company_name_short', __('svr-data-lang::data.animal.animal_place_of_keeping'))->sortable();
		$grid->column('animal_object_of_keeping', __('svr-data-lang::data.animal.animal_object_of_keeping'))->display(function($animal_object_of_birth_data)
		{
			if($animal_object_of_birth_data)
			{
				return $animal_object_of_birth_data['company_object_address_view'].' ['.(int)$animal_object_of_birth_data['company_object_is_favorite'].']';
			}
		})->sortable();
		$grid->column('animal_place_of_birth.company_name_short', __('svr-data-lang::data.animal.animal_place_of_birth'))->sortable();
		$grid->column('animal_object_of_birth.company_object_address_view', __('svr-data-lang::data.animal.animal_object_of_birth'))->sortable();
		$grid->animal_type_of_keeping_id(__('svr-data-lang::data.animal.animal_type_of_keeping_id'))
			->select(DirectoryKeepingTypes::All(['keeping_type_name', 'keeping_type_id'])->pluck('keeping_type_name', 'keeping_type_id')->toArray())->sortable();
		$grid->animal_purpose_of_keeping_id(__('svr-data-lang::data.animal.animal_purpose_of_keeping_id'))
			->select(DirectoryKeepingPurposes::All(['keeping_purpose_name', 'keeping_purpose_id'])->pluck('keeping_purpose_name', 'keeping_purpose_id')->toArray())->sortable();
		$grid->animal_country_nameport_id(__('svr-data-lang::data.animal.animal_country_nameport'))
			->select(DirectoryCountries::All(['country_id', 'country_name'])->pluck('country_name', 'country_id')->toArray())->sortable();
		$grid->column('animal_description', __('svr-data-lang::data.animal.animal_description'))->sortable()->textarea();
		$grid->column('animal_photo', __('svr-data-lang::data.animal.animal_photo'))->sortable();
		$grid->column('animal_out_date', __('svr-data-lang::data.animal.animal_out_date'))->sortable()->date();
		$grid->column('animal_out_reason', __('svr-data-lang::data.animal.animal_out_reason'))->sortable()->text();
		$grid->column('animal_out_rashod', __('svr-data-lang::data.animal.animal_out_rashod'))->sortable()->text();
		$grid->animal_out_type_id(__('svr-data-lang::data.animal.animal_out_type'))
			->select(DirectoryOutTypes::All(['out_type_name', 'out_type_id'])->pluck('out_type_name', 'out_type_id')->toArray())->sortable();
		$grid->animal_out_basis_id(__('svr-data-lang::data.animal.animal_out_basis'))
			->select(DirectoryOutBasises::All(['out_basis_name', 'out_basis_id'])->pluck('out_basis_name', 'out_basis_id')->toArray())->sortable();
		$grid->column('animal_out_weight', __('svr-data-lang::data.animal.animal_out_weight'))->sortable()->text();
		$grid->column('animal_mother_num', __('svr-data-lang::data.animal.animal_mother_num'))->sortable()->text();
		$grid->column('animal_mother_rshn', __('svr-data-lang::data.animal.animal_mother_rshn'))->sortable()->text();
		$grid->column('animal_mother_inv', __('svr-data-lang::data.animal.animal_mother_inv'))->sortable()->text();
		$grid->column('animal_mother_date_birth', __('svr-data-lang::data.animal.animal_mother_date_birth'))->sortable()->date();
		$grid->animal_mother_breed_id(__('svr-data-lang::data.animal.animal_mother_breed_id'))
			->select(DirectoryAnimalsBreeds::All(['breed_name', 'breed_id'])->pluck('breed_name', 'breed_id')->toArray())->sortable();
		$grid->column('animal_father_num', __('svr-data-lang::data.animal.animal_father_num'))->sortable()->text();
		$grid->column('animal_father_rshn', __('svr-data-lang::data.animal.animal_father_rshn'))->sortable()->text();
		$grid->column('animal_father_inv', __('svr-data-lang::data.animal.animal_father_inv'))->sortable()->text();
		$grid->column('animal_father_date_birth', __('svr-data-lang::data.animal.animal_father_date_birth'))->sortable()->date();
		$grid->animal_father_breed_id(__('svr-data-lang::data.animal.animal_father_breed_id'))
			->select(DirectoryAnimalsBreeds::All(['breed_name', 'breed_id'])->pluck('breed_name', 'breed_id')->toArray())->sortable();
		$grid->column('animal_status', __('svr-data-lang::data.animal.animal_status'))->sortable()->select(SystemStatusEnum::get_option_list());
		$grid->column('animal_status_delete', __('svr-data-lang::data.animal.animal_status_delete'))->sortable()->select(SystemStatusDeleteEnum::get_option_list());
		$grid->column('animal_repair_status', __('svr-data-lang::data.animal.animal_repair_status'))->sortable()->select(SystemStatusEnum::get_option_list());

		$grid->column('animal_codes', __('svr-data-lang::data.animal.animal_codes'))->display(function(){
			return "Идентификаторы";
		})->modal('Идентификаторы животного', function($model)
		{
			$animal_codes = $model->animal_codes()->get()->map(function($animal_code)
			{
				$row_data			= $animal_code->only(['code_id', 'mark_type', 'code_value']);
				$mark_data			= $row_data['mark_type']->toArray();

				if(isset($mark_data[0]))
				{
					return [$row_data['code_id'], $mark_data[0]['mark_type_name'], $row_data['code_value']];
				}else{
					return false;
				}
			});

			return new Table(['ID', 'Тип', 'Значение'], $animal_codes->toArray());
		});

		$grid->actions(function ($actions) {
			$actions->disableView();
			$actions->disableEdit();
		});

		$grid->filter(function ($filter)
		{
			$filter->disableIdFilter();
			$filter->equal('animal_id', __('svr-data-lang::data.animal.animal_id'));
			$filter->equal('breed_id', __('svr-data-lang::data.animals.breed_name'))->select(DirectoryAnimalsBreeds::All(['breed_name', 'breed_id'])->pluck('breed_name', 'breed_id')->toArray());
			$filter->equal('animal_guid_self', __('svr-data-lang::data.animal.animal_guid_self'));
			$filter->equal('animal_guid_horriot', __('svr-data-lang::data.animal.animal_guid_horriot'));
			$filter->equal('animal_number_horriot', __('svr-data-lang::data.animal.animal_number_horriot'));
			$filter->equal('animal_nanimal', __('svr-data-lang::data.animal.animal_nanimal'));
			$filter->equal('animal_nanimal_time', __('svr-data-lang::data.animal.animal_nanimal_time'));
			$filter->equal('animal_sex', __('svr-data-lang::data.animal.animal_sex'))->select(SystemSexEnum::get_option_list());
			$filter->equal('animal_breeding_value', __('svr-data-lang::data.animal.animal_breeding_value'))->select(SystemBreedingValueEnum::get_option_list());

			$filter->where(function ($query)
			{
				$query->whereRaw("animal_id IN (SELECT animal_id FROM data.data_applications_animals WHERE application_id = {$this->input})");
			}, '№ заявки', 'application_id');
		});

		$grid->disableCreateButton();
		$grid->disableExport();
//		$grid->fixHeader();
//		$grid->setActionClass(DropdownActions::class);
//		$grid->fixColumns(-1);

		return $grid;
	}


//	public function store($data)
//	{
//		dd($data);
//	}

	/**
	 * Make a show builder.
	 *
	 * @param mixed $id
	 *
	 * @return Show
	 */
	protected function detail($id)
	{
		$show = new Show(DataAnimals::findOrFail($id));
//		$show->field('animal_id', __('svr-data-lang::data.animal.animal_id'));
		return $show;
	}

	/**
	 * Make a form builder.
	 *
	 * @return Form
	 */
	protected function form()
	{
		$model						= new DataAnimals();
		$form						= new Form(new DataAnimals());

		$form->saving(function (Form $form) use ($model)
		{
			if ($form->isEditing())
			{
				$animal_data	= $form->model->toArray();

				return $model->animalUpdate($animal_data['animal_id'], request());
			}
		});

		return $form;
	}
}
