<?php

namespace Svr\Data\Models;

use Illuminate\Support\Facades\DB;
use Svr\Core\Traits\GetTableName;
use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryAnimalsSpecies;
use Svr\Directories\Models\DirectoryGenders;
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
    use GetTableName;
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
		'created_at',								//* дата создания животного в СВР */
        'updated_at'								//* дата последнего изменения строки записи */
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
	 * Обновление данных из запроса
	 */
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


	/**
	 * Непосредственное обновление в базе
	 */
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


	/**
	 * Правила проверки входящих данных
	 */
	private function rules($request):void
	{
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


	/**
	 * Реляция айдишников животных
	 */
	public function animal_codes()
	{
		return $this->hasMany(DataAnimalsCodes::class, 'animal_id', 'animal_id');
	}


	/**
	 * Реляция пород животных
	 */
	public function breed()
	{
		return $this->belongsTo(DirectoryAnimalsBreeds::class, 'breed_id', 'breed_id');
	}


	/**
	 * Реляция пород животных (мать)
	 */
	public function animal_mother_breed()
	{
		return $this->belongsTo(DirectoryAnimalsBreeds::class, 'animal_mother_breed_id', 'breed_id');
	}


	/**
	 * Реляция пород животных (батяня)
	 */
	public function animal_father_breed()
	{
		return $this->belongsTo(DirectoryAnimalsBreeds::class, 'animal_father_breed_id', 'breed_id');
	}


	/**
	 * Реляция кодов животных (чип)
	 */
	public function animal_code_chip()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_chip_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (левый номер)
	 */
	public function animal_code_left()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_left_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (правый номер)
	 */
	public function animal_code_right()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_right_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (УНСМ)
	 */
	public function animal_code_rshn()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_rshn_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (инвентарный номер)
	 */
	public function animal_code_inv()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_inv_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (номер в оборудовании)
	 */
	public function animal_code_device()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_device_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (тату)
	 */
	public function animal_code_tattoo()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_tattoo_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (импортный код)
	 */
	public function animal_code_import()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_import_id', 'code_id');
	}


	/**
	 * Реляция кодов животных (Кличка)
	 */
	public function animal_code_name()
	{
		return $this->belongsTo(DataAnimalsCodes::class, 'animal_code_name_id', 'code_id');
	}


	/**
	 * Реляция тип содержания
	 */
	public function animal_type_of_keeping()
	{
		return $this->belongsTo(DirectoryKeepingTypes::class, 'animal_type_of_keeping_id', 'keeping_type_id');
	}


	/**
	 * Реляция цель содержания
	 */
	public function animal_purpose_of_keeping()
	{
		return $this->belongsTo(DirectoryKeepingPurposes::class, 'animal_purpose_of_keeping_id', 'keeping_purpose_id');
	}


	/**
	 * Реляция страна ввоза
	 */
	public function animal_country_nameport()
	{
		return $this->belongsTo(DirectoryCountries::class, 'animal_country_nameport_id', 'country_id');
	}


	/**
	 * Реляция основание выбытия
	 */
	public function animal_out_basis()
	{
		return $this->belongsTo(DirectoryOutBasises::class, 'animal_out_basis_id', 'out_basis_id');
	}


	/**
	 * Реляция причина выбытия
	 */
	public function animal_out_type()
	{
		return $this->belongsTo(DirectoryOutTypes::class, 'animal_out_type_id', 'out_type_id');
	}


	/**
	 * Реляция объект содержания
	 */
	public function animal_object_of_keeping()
	{
		return $this->belongsTo(DataCompaniesObjects::class, 'animal_object_of_keeping_id', 'company_object_id');
	}


	/**
	 * Реляция место рождения
	 */
	public function animal_object_of_birth()
	{
		return $this->belongsTo(DataCompaniesObjects::class, 'animal_object_of_birth_id', 'company_object_id');
	}


	/**
	 * Реляция место содержания
	 */
	public function animal_place_of_keeping()
	{
		return $this->belongsTo(DataCompanies::class, 'animal_place_of_keeping_id', 'company_id');
	}


	/**
	 * Реляция место рождения
	 */
	public function animal_place_of_birth()
	{
		return $this->belongsTo(DataCompanies::class, 'animal_place_of_birth_id', 'company_id');
	}

    /**
     * Данные по животным
     * @param $animal_id
     * @param false $application_id
     * @return array
     */
    public static function animal_data($animal_id, $application_id = false): array
    {
        $where = '';//self::create_filter_restrictions([]);

        if ($application_id !== false && count($application_id) > 0) {
            $application_left_join =
                ' LEFT JOIN ' .DataApplicationsAnimals::getTableName(). ' t_application_animal ON
                    t_application_animal.animal_id = t_animal.animal_id AND
                    t_application_animal.application_id IN (' . implode(',', $application_id) . ')';
        }
        else
        {
            $application_left_join =
                ' LEFT JOIN
                    (
                        SELECT MAX(application_id) AS application_id, animal_id
                        FROM ' .DataApplicationsAnimals::getTableName(). ' t_application_animal_temp GROUP BY animal_id
                    ) t_application_animal_temp ON t_application_animal_temp.animal_id = t_animal.animal_id
                LEFT JOIN ' .DataApplicationsAnimals::getTableName(). ' t_application_animal ON t_application_animal.animal_id = t_animal.animal_id
                    AND t_application_animal.application_id = t_application_animal_temp.application_id';
        }

        $query = 'SELECT
    				t_animal.*,
    				t_application_animal.application_animal_status,
    				t_application_animal.application_id,
    				t_application_animal.application_animal_id,
    				t_application_animal.application_animal_date_add,
    				t_application_animal.application_animal_date_horriot,
    				t_application_animal.application_animal_date_response,
					t_application_animal.application_herriot_send_text_error,
					t_application_animal.application_herriot_check_text_error,
					t_animal_breed.breed_name as animal_breed_name,
					t_animal_breed.breed_id as animal_breed_id,
					t_animal_breed.breed_guid_horriot as animal_breed_guid_horriot,
					t_animal_specie.specie_name as animal_specie_name,
					t_animal_specie.specie_id as animal_specie_id,
					t_animal_specie.specie_guid_horriot as animal_specie_guid_horriot,
					t_animal_chip.code_value as animal_chip_value,
					t_animal_chip.code_tool_type_id as animal_chip_tool_type,
					t_animal_chip.code_tool_date_set as animal_chip_tool_date,
					t_animal_left.code_value as animal_left_value,
					t_animal_left.code_tool_type_id as animal_left_tool_type,
					t_animal_left.code_tool_date_set as animal_left_tool_date,
					t_animal_right.code_value as animal_right_value,
					t_animal_right.code_tool_type_id as animal_right_tool_type,
					t_animal_right.code_tool_date_set as animal_right_tool_date,
					t_animal_rshn.code_value as animal_rshn_value,
					t_animal_rshn.code_tool_type_id as animal_rshn_tool_type,
					t_animal_rshn.code_tool_date_set as animal_rshn_tool_date,
					t_animal_inv.code_value as animal_inv_value,
					t_animal_inv.code_tool_type_id as animal_inv_tool_type,
					t_animal_inv.code_tool_date_set as animal_inv_tool_date,
					t_animal_device.code_value as animal_device_value,
					t_animal_device.code_tool_type_id as animal_device_tool_type,
					t_animal_device.code_tool_date_set as animal_device_tool_date,
					t_animal_tattoo.code_value as animal_tattoo_value,
					t_animal_tattoo.code_tool_type_id as animal_tattoo_tool_type,
					t_animal_tattoo.code_tool_date_set as animal_tattoo_tool_date,
					t_animal_import.code_value as animal_import_value,
					t_animal_import.code_tool_type_id as animal_import_tool_type,
					t_animal_import.code_tool_date_set as animal_import_tool_date,
					t_animal_name.code_value as animal_name_value,
					t_gender.gender_name as animal_gender_name,
					t_gender.gender_id as animal_gender_id,
					t_gender.gender_value_horriot as animal_gender_value_horriot,
					t_animal_owner_company_info.company_name_short as animal_owner_company_name_short,
					t_animal_owner_company_info.company_id as animal_owner_company_id,
					t_animal_owner_company_info.company_guid_vetis as animal_owner_company_guid_vetis,
					t_animal_owner_company.region_id as animal_owner_region_id,
					t_animal_owner_company.district_id as animal_owner_district_id,
					t_animal_keeping_company_info.company_name_short as animal_keeping_company_name_short,
					t_animal_keeping_company_info.company_id as animal_keeping_company_id,
					t_animal_keeping_company_info.company_guid_vetis as animal_keeping_company_guid_vetis,
					t_animal_birth_company_info.company_name_short as animal_birth_company_name_short,
					t_animal_birth_company_info.company_id as animal_birth_company_id,
					t_animal_birth_company_info.company_guid_vetis as animal_birth_company_guid_vetis,
					t_animal_keeping_type.keeping_type_name as animal_keeping_type_name,
					t_animal_keeping_type.keeping_type_id as animal_keeping_type_id,
					t_animal_keeping_type.keeping_type_guid_horriot as animal_keeping_type_guid_horriot,
					t_animal_keeping_purpose.keeping_purpose_name as animal_keeping_purpose_name,
					t_animal_keeping_purpose.keeping_purpose_id as animal_keeping_purpose_id,
					t_animal_keeping_purpose.keeping_purpose_guid_horriot as animal_keeping_purpose_guid_horriot,
					t_animal_country_import.country_name as animal_country_import_name,
					t_animal_country_import.country_id as animal_country_import_id,
					t_animal_out_type.out_type_name as animal_out_type_name,
					t_animal_out_basis.out_basis_name as animal_out_basis_name,
					t_mother_breed.breed_name as animal_mother_breed_name,
					t_father_breed.breed_name as animal_father_breed_name,
					t_birth_company_object.company_object_guid_horriot as birth_object_guid_horriot,
					t_keeping_company_object.company_object_guid_horriot as keeping_object_guid_horriot
    			FROM ' . DataAnimals::getTableName() . ' t_animal
					LEFT JOIN ' . DirectoryAnimalsBreeds::getTableName() . ' 	t_animal_breed ON t_animal_breed.breed_id = t_animal.breed_id
					LEFT JOIN ' . DirectoryAnimalsSpecies::getTableName() . ' 	t_animal_specie ON t_animal_specie.specie_id = t_animal_breed.specie_id
					'.$application_left_join.'
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_chip ON t_animal_chip.code_id = t_animal.animal_code_chip_id AND t_animal.animal_code_chip_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_left ON t_animal_left.code_id = t_animal.animal_code_left_id AND t_animal.animal_code_left_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_right ON t_animal_right.code_id = t_animal.animal_code_right_id AND t_animal.animal_code_right_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_rshn ON t_animal_rshn.code_id = t_animal.animal_code_rshn_id AND t_animal.animal_code_rshn_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_inv ON t_animal_inv.code_id = t_animal.animal_code_inv_id AND t_animal.animal_code_inv_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_device ON t_animal_device.code_id = t_animal.animal_code_device_id AND t_animal.animal_code_device_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_tattoo ON t_animal_tattoo.code_id = t_animal.animal_code_tattoo_id AND t_animal.animal_code_tattoo_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_import ON t_animal_import.code_id = t_animal.animal_code_import_id AND t_animal.animal_code_import_id IS NOT NULL
					LEFT JOIN ' . DataAnimalsCodes::getTableName() . ' 			t_animal_name ON t_animal_name.code_id = t_animal.animal_code_name_id AND t_animal.animal_code_name_id IS NOT NULL
					LEFT JOIN ' . DirectoryGenders::getTableName() . ' 			t_gender ON t_gender.gender_id = t_animal.animal_sex_id
					LEFT JOIN ' . DataCompaniesLocations::getTableName() . ' 		t_animal_owner_company ON t_animal_owner_company.company_location_id = t_animal.company_location_id
					LEFT JOIN ' . DataCompanies::getTableName() . ' 				t_animal_owner_company_info ON t_animal_owner_company_info.company_id = t_animal_owner_company.company_id
					LEFT JOIN ' . DataCompanies::getTableName() . ' 				t_animal_keeping_company_info ON t_animal_keeping_company_info.company_id = t_animal.animal_place_of_keeping_id
					LEFT JOIN ' . DataCompanies::getTableName() . ' 				t_animal_birth_company_info ON t_animal_birth_company_info.company_id = t_animal.animal_place_of_birth_id
					LEFT JOIN ' . DirectoryKeepingTypes::getTableName() . ' 		t_animal_keeping_type ON t_animal_keeping_type.keeping_type_id = t_animal.animal_type_of_keeping_id
					LEFT JOIN ' . DirectoryKeepingPurposes::getTableName() . ' 	t_animal_keeping_purpose ON t_animal_keeping_purpose.keeping_purpose_id = t_animal.animal_purpose_of_keeping_id
					LEFT JOIN ' . DirectoryCountries::getTableName() . ' 			t_animal_country_import ON t_animal_country_import.country_id = t_animal.animal_country_nameport_id
					LEFT JOIN ' . DirectoryOutTypes::getTableName() . '			t_animal_out_type ON t_animal_out_type.out_type_id = t_animal.animal_out_type_id
					LEFT JOIN ' . DirectoryOutBasises::getTableName() . '		t_animal_out_basis ON t_animal_out_basis.out_basis_id = t_animal.animal_out_basis_id
					LEFT JOIN ' . DirectoryAnimalsBreeds::getTableName() . ' 	t_mother_breed ON t_mother_breed.breed_id = t_animal.animal_mother_breed_id
					LEFT JOIN ' . DirectoryAnimalsBreeds::getTableName() . ' 	t_father_breed ON t_father_breed.breed_id = t_animal.animal_father_breed_id
					LEFT JOIN ' . DataCompaniesObjects::getTablename() . ' 		t_birth_company_object ON t_birth_company_object.company_object_id = t_animal.animal_object_of_birth_id
					LEFT JOIN ' . DataCompaniesObjects::getTablename() . ' 		t_keeping_company_object ON t_keeping_company_object.company_object_id = t_animal.animal_object_of_keeping_id
				WHERE t_animal.animal_id = :animal_id '.$where.' LIMIT 1';

        $animal_data = DB::select($query, ['animal_id' => $animal_id]);

        if($animal_data)
        {
            $animal_data = (array)$animal_data[0];
            $animal_data['animal_registration_available'] = self::animal_registration_available($animal_data);
        }

        return $animal_data;
    }


    private static function create_filter_restrictions($valid_data): string
    {
        $where_view = '';
        switch ($this->USER('role_slug'))
        {
            case 'admin':
                if (isset($valid_data['company_location_id']) && system_Filter::is_num($valid_data['company_location_id'])) {
                    $where_view .= ' AND t_animal.company_location_id = ' . (int)$valid_data['company_location_id'];
                }
                if (isset($valid_data['company_region_id']) && system_Filter::is_num($valid_data['company_region_id'])) {
                    $where_view .= ' AND t_animal_owner_company.region_id = ' . (int)$valid_data['company_region_id'];
                }
                if (isset($valid_data['company_district_id']) && system_Filter::is_num($valid_data['company_district_id'])) {
                    $where_view .= ' AND t_animal_owner_company.district_id = ' . (int)$valid_data['company_district_id'];
                }
                break;
            case 'doctor_company':
                $where_view .= ' AND t_animal.company_location_id = ' . (int)$this->USER('company_location_id');
                break;
            case 'doctor_region':
                $where_view .= ' AND t_animal_owner_company.region_id = ' . (int)$this->USER('region_region_id');
                break;
            case 'doctor_district':
                $where_view .= ' AND t_animal_owner_company.district_id = ' . (int)$this->USER('district_district_id');
                break;
        }
        return $where_view;
    }

    /**
     * Проверяет, доступен ли данный животное для регистрации.
     *
     * @param array $animal_data Данные о животном.
     * @return bool true если доступно, или false, если нет.
     */
    public static function animal_registration_available(array $animal_data): bool
    {
        if ($animal_data['animal_status'] == 'disabled' || $animal_data['animal_status_delete'] == 'deleted' || !empty($animal_data['animal_guid_horriot']) || $animal_data['keeping_object_guid_horriot'] == null)
        {
            return false;
        }

        if (!empty($animal_data['application_animal_status']))
        {
            if ($animal_data['application_animal_status'] != 'rejected') return false;
        }

        if (($animal_data['animal_chip_tool_type'] != NULL AND $animal_data['animal_chip_tool_date'] != NULL) OR
            ($animal_data['animal_left_tool_type'] != NULL AND $animal_data['animal_left_tool_date'] != NULL) OR
            ($animal_data['animal_right_tool_type'] != NULL AND $animal_data['animal_right_tool_date'] != NULL) OR
            ($animal_data['animal_rshn_tool_type'] != NULL AND $animal_data['animal_rshn_tool_date'] != NULL) OR
            ($animal_data['animal_inv_tool_type'] != NULL AND $animal_data['animal_inv_tool_date'] != NULL) OR
            ($animal_data['animal_device_tool_type'] != NULL AND $animal_data['animal_device_tool_date'] != NULL) OR
            ($animal_data['animal_tattoo_tool_type'] != NULL AND $animal_data['animal_tattoo_tool_date'] != NULL) OR
            ($animal_data['animal_import_tool_type'] != NULL AND $animal_data['animal_import_tool_date'] != NULL))
        {
            return true;
        }else{
            return false;
        }
    }
}
