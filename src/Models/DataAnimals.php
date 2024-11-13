<?php

namespace Svr\Data\Models;

use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryKeepingTypes;
use Svr\Directories\Models\DirectoryKeepingPurposes;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryOutBasises;
use Svr\Directories\Models\DirectoryOutTypes;

use Svr\Core\Enums\SystemBreedingValueEnum;
use Svr\Core\Enums\SystemSexEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Traits\GetEnums;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class DataAnimals extends Model
{
	use GetEnums;
    use HasFactory;


	private $validator								= false;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_animals';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'animal_id';


	/**
	 * Флаг наличия автообновляемых полей
	 * @var string
	 */
//	public $timestamps								= false;


	/**
	 * Поле даты создания строки
	 * @var string
	 */
	const CREATED_AT								= 'created_at';


	/**
	 * Поле даты обновления строки
	 * @var string
	 */
	const UPDATED_AT								= 'updated_at';


	/**
	 * На случай, если потребуется указать специфичное подключение для таблицы
	 * @var string
	 */
//	protected $connection							= 'mysql';


	/**
	 * Значения полей по умолчанию при создании нового животного
	 * @var array
	 */
	protected $attributes							= [
		'animal_status'									=> 'enabled',
		'animal_status_delete'							=> 'active',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'company_location_id',						//* COMPANY_LOCATION_ID локации животного в таблице DATA.DATA_COMPANIES_LOCATIONS */
		'polovoz_id',								//* ID половозрастной группы животного */
		'breed_id',									//* BREED_ID породы животного в таблице DIRECTORIES.ANIMALS_BREEDS */
		'animal_task',								//* код задачи берется из таблицы TASKS.NTASK (1 – молоко / 6- мясо / 4 - овцы) */
		'animal_guid_self',							//* гуид животного, который генерирует СВР в момент создания RAW записи */
		'animal_guid_horriot',						//* гуид уникального регистрационного номера их Хорриот */
		'animal_number_horriot',					//* гуид уникального регистрационного номера их Хорриот */
		'animal_nanimal',							//* животное - НЕ!!! уникальный идентификатор */
		'animal_nanimal_time',						//* животное - уникальный идентификатор (наверное...) */
		'animal_code_chip_id',						//* CODE_ID чипа животного  в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_left_id',						//* CODE_ID левого номера животного в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_right_id',						//* CODE_ID правого номера животного в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_rshn_id',						//* CODE_ID номера РСХН в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_inv_id',						//* CODE_ID инвентарного номера животного в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_device_id',					//* CODE_ID номера в оборудовании животного в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_tattoo_id',					//* CODE_ID тату животного в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_import_id',					//* CODE_ID импортного номера животного в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_name_id',						//* CODE_ID клички животного в таблице DATA.DATA_ANIMALS_CODES */
		'animal_code_inv_value',					//* Значение инвентарного номера животного */
		'animal_code_rshn_value',					//* Значение РСХН (УНСМ) номера животного */
		'animal_date_create_record',				//* Дата создания записи в формате YYYY-mm-dd */
		'animal_date_birth',						//* дата рождения животного в формате YYYY-mm-dd */
		'animal_date_import',						//* дата ввоза животного в формате YYYY-mm-dd */
		'animal_date_income',						//* дата поступления животного в формате YYYY-mm-dd */
		'animal_sex_id',							//* GENDER_ID пол животного в таблице DIRECTORIES.GENDERS */
		'animal_sex',								//* Пол животного enumом */
		'animal_breeding_value',					//* племенная ценность животного */
		'animal_colour',							//* масть (окрас) животного */
		'animal_place_of_keeping_id',				//* COMPANY_ID места содержания животного */
		'animal_object_of_keeping_id',				//* company_object_id места содержания животного в таблице data.data_companies_objects */
		'animal_place_of_birth_id',					//* COMPANY_ID места рождения животного в таблице DATA.DATA_COMPANIES */
		'animal_object_of_birth_id',				//* company_object_id места рождения животного в таблице  в таблице data.data_companies_objects */
		'animal_type_of_keeping_id',				//* KEEPING_TYPE_ID типа содержания животного в таблице DIRECTORIES.KEEPING_TYPES */
		'animal_purpose_of_keeping_id',				//* KEEPING_PURPOSE_ID цели содержания животного в таблице DIRECTORIES.KEEPING_PURPOSES */
		'animal_country_nameport_id',				//* COUNTRY_ID страны ввоза животного в таблице DIRECTORIES.COUNTRIES */
		'animal_description',						//* описание животного */
		'animal_photo',								//* фото животного */
		'animal_out_date',							//* дата выбытия животного в формате YYYY-mm-dd */
		'animal_out_reason',						//* причина выбытия животного */
		'animal_out_rashod',						//* расход животного */
		'animal_out_type_id',						//* OUT_TYPE_ID типа выбытия животного в таблице DIRECTORIES.OUT_TYPES */
		'animal_out_basis_id',						//* OUT_BASIS_ID основания выбытия животного в таблице DIRECTORIES.OUT_BASISES */
		'animal_out_weight',						//* живая масса (кг) животного при выбытии */
		'animal_mother_num',						//* уникальный номер матери животного */
		'animal_mother_rshn',						//* рсхн номер матери животного */
		'animal_mother_inv',						//* инвентарный номер матери животного */
		'animal_mother_date_birth',					//* дата рождения матери в формате YYYY-mm-dd */
		'animal_mother_breed_id',					//* BREED_ID породы матери в таблице DIRECTORIES.ANIMALS_BREEDS */
		'animal_father_num',						//* уникальный номер отца животного */
		'animal_father_rshn',						//* рсхн номер отца животного */
		'animal_father_inv',						//* инвентарный номер отца животного */
		'animal_father_date_birth',					//* дата рождения отца в формате YYYY-mm-dd */
		'animal_father_breed_id',					//* BREED_ID породы отца в таблице DIRECTORIES.ANIMALS_BREEDS */
		'animal_status',							//* статус животного */
		'animal_status_delete',						//* статус удаления животного */
		'animal_repair_status',						//* Флаг починки животного */
		'created_at',						//* дата создания животного в СВР */
        'updated_at'									//* дата последнего изменения строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'animal_id',
	];


	/**
	 * Массив системных скрытых полей
	 * @var array
	 */
	protected $hidden								= [
		'created_at',
	];


	/**
	 * Преобразование полей при чтении/записи
	 * @return array
	 */
	protected function casts(): array
	{
		return [
//			'update_at'								=> 'timestamp',
//			'animal_created_at'						=> 'timestamp',
		];
	}


	public function animalUpdate($animal_id, $request)
	{
		if((int)$animal_id < 1)
		{
			return response(['status'  => false, 'message' => __('svr-data-lang::data.animal.message_animal_not_found')]);
		}

		$this->rules($request);

		if($this->validator->fails())
		{
			$errors = array_values($this->validator->errors()->toArray());

			return response(['status'  => false, 'message' => $errors[0][0]]);
		}else{
			if($this->animalUpdateRaw($animal_id, $this->validator->validated()))
			{
				return response(['status'  => true, 'message' => __('svr-data-lang::data.animal.message_animal_edit_success')]);
			}else{
				return response(['status'  => false, 'message' => __('svr-data-lang::data.animal.message_animal_not_found')]);
			}
		}
	}


	public function animalUpdateRaw($animal_id, $data)
	{
		$animal_data					= $this->find($animal_id);

		if($animal_data)
		{
			return $animal_data->update($data);
		}else{
			return false;
		}
	}


	private function rules($request):void
	{
		// получаем поля со значениями
		$data							= $request->all();
		$data_rules						= [];
		$fields_rules					= [
			'breed_id'						=> ['required', 'integer', Rule::exists('Svr\Directories\Models\DirectoryAnimalsBreeds', 'breed_id')],
			'animal_guid_self'				=> ['required', 'min:8', 'max:64'],
			'animal_guid_horriot'			=> ['min:8', 'max:64'],
			'animal_number_horriot'			=> ['min:8', 'max:64'],
			'animal_nanimal'				=> ['required', 'min:10', 'max:128'],
			'animal_nanimal_time'			=> ['required', 'min:10', 'max:128'],
			'animal_date_create_record'		=> ['required', 'date'],
			'animal_date_birth'				=> ['date'],
			'animal_date_import'			=> ['date'],
			'animal_date_income'			=> ['date'],
			'animal_out_date'				=> ['date'],
			'animal_colour'					=> ['min:0', 'max:100'],
			'animal_description'			=> ['min:0', 'max:100'],
			'animal_type_of_keeping_id'		=> ['required', 'integer', Rule::exists('Svr\Directories\Models\DirectoryKeepingTypes', 'keeping_type_id')],
			'animal_purpose_of_keeping_id'	=> ['required', 'integer', Rule::exists('Svr\Directories\Models\DirectoryKeepingPurposes', 'keeping_purpose_id')],
			'animal_country_nameport_id'	=> ['integer', Rule::exists('Svr\Directories\Models\DirectoryCountries', 'country_id')],
			'animal_out_type_id'			=> ['integer', Rule::exists('Svr\Directories\Models\DirectoryOutTypes', 'out_type_id')],
			'animal_out_basis_id'			=> ['integer', Rule::exists('Svr\Directories\Models\DirectoryOutBasises', 'out_basis_id')],
			'animal_out_reason'				=> ['min:0', 'max:255'],
			'animal_out_rashod'				=> ['min:0', 'max:255'],
			'animal_out_weight'				=> ['integer'],
			'animal_mother_num'				=> ['min:0', 'max:64'],
			'animal_mother_rshn'			=> ['min:0', 'max:64'],
			'animal_mother_inv'				=> ['min:0', 'max:64'],
			'animal_mother_date_birth'		=> ['date'],
			'animal_mother_breed_id'		=> ['integer', Rule::exists('Svr\Directories\Models\DirectoryAnimalsBreeds', 'breed_id')],
			'animal_father_num'				=> ['min:0', 'max:64'],
			'animal_father_rshn'			=> ['min:0', 'max:64'],
			'animal_father_inv'				=> ['min:0', 'max:64'],
			'animal_father_date_birth'		=> ['date'],
			'animal_father_breed_id'		=> ['integer', Rule::exists('Svr\Directories\Models\DirectoryAnimalsBreeds', 'breed_id')],
			'animal_breeding_value'			=> ['required', Rule::in(SystemBreedingValueEnum::get_option_list())],
			'animal_sex'					=> ['required', Rule::in(SystemSexEnum::get_option_list())],
			'animal_status'					=> ['required', Rule::in(SystemStatusEnum::get_option_list())],
			'animal_repair_status'			=> ['required', Rule::in(SystemStatusEnum::get_option_list())],
			'animal_status_delete'			=> ['required', Rule::in(SystemStatusDeleteEnum::get_option_list())]
		];

		if($data && count($data) > 0)
		{
			foreach($data as $field => $value)
			{
				if(in_array($field, array_keys($fields_rules)))
				{
					$data_rules[$field] = $fields_rules[$field];
				}
			}

			$this->validator			= Validator::make($data, $data_rules);
		}
	}


	public function animal_codes()
	{
		return $this->hasMany(DataAnimalsCodes::class, 'animal_id', 'animal_id');
	}

	public function breed()
	{
		return $this->belongsTo(DirectoryAnimalsBreeds::class, 'breed_id', 'breed_id');
	}

	public function animal_mother_breed()
	{
		return $this->belongsTo(DirectoryAnimalsBreeds::class, 'animal_mother_breed_id', 'breed_id');
	}

	public function animal_father_breed()
	{
		return $this->belongsTo(DirectoryAnimalsBreeds::class, 'animal_father_breed_id', 'breed_id');
	}

	public function animal_code_chip()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_chip_id', 'code_id');
	}

	public function animal_code_left()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_left_id', 'code_id');
	}

	public function animal_code_right()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_right_id', 'code_id');
	}

	public function animal_code_rshn()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_rshn_id', 'code_id');
	}

	public function animal_code_inv()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_inv_id', 'code_id');
	}

	public function animal_code_device()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_device_id', 'code_id');
	}

	public function animal_code_tattoo()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_tattoo_id', 'code_id');
	}

	public function animal_code_import()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_import_id', 'code_id');
	}

	public function animal_code_name()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_name_id', 'code_id');
	}

	public function animal_type_of_keeping()
	{
		return $this->belongsTo(DirectoryKeepingTypes::class, 'animal_type_of_keeping_id', 'keeping_type_id');
	}

	public function animal_purpose_of_keeping()
	{
		return $this->belongsTo(DirectoryKeepingPurposes::class, 'animal_purpose_of_keeping_id', 'keeping_purpose_id');
	}

	public function animal_country_nameport()
	{
		return $this->belongsTo(DirectoryCountries::class, 'animal_country_nameport_id', 'country_id');
	}

	public function animal_out_basis()
	{
		return $this->belongsTo(DirectoryOutBasises::class, 'animal_out_basis_id', 'out_basis_id');
	}

	public function animal_out_type()
	{
		return $this->belongsTo(DirectoryOutTypes::class, 'animal_out_type_id', 'out_type_id');
	}

	public function animal_object_of_keeping()
	{
		return $this->belongsTo(DataCompaniesObjects::class, 'animal_object_of_keeping_id', 'company_object_id');
	}

	public function animal_object_of_birth()
	{
		return $this->belongsTo(DataCompaniesObjects::class, 'animal_object_of_birth_id', 'company_object_id');
	}

	public function animal_place_of_keeping()
	{
		return $this->belongsTo(DataCompanies::class, 'animal_place_of_keeping_id', 'company_id');
	}

	public function animal_place_of_birth()
	{
		return $this->belongsTo(DataCompanies::class, 'animal_place_of_birth_id', 'company_id');
	}
}
