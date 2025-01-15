<?php

namespace Svr\Data\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;
use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryAnimalsSpecies;
use Svr\Directories\Models\DirectoryGenders;
use Svr\Directories\Models\DirectoryKeepingTypes;
use Svr\Directories\Models\DirectoryKeepingPurposes;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryMarkStatuses;
use Svr\Directories\Models\DirectoryMarkToolTypes;
use Svr\Directories\Models\DirectoryMarkTypes;
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
use Svr\Directories\Models\DirectoryToolsLocations;

use Svr\Core\Extensions\System\SystemFilter;

class DataAnimals extends Model
{
    use GetTableName;
	use GetEnums;
    use HasFactory;
	use GetValidationRules;

    // список колонок, участвующих в поиске и сортировке (метод list)
    private static array $columns_list = [
        'animal_rshn' => [
            'column_name' => ['t_animal_rshn.code_value'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_rshn_value'
        ],
        'animal_guid_self' => [
            'column_name' => ['t_animal.animal_guid_self'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_guid_self'
        ],
        'animal_inv' => [
            'column_name' => ['t_animal_inv.code_value'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_inv_value'
        ],
        'animal_specie' => [
            'column_name' => ['t_animal_specie.specie_name'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_specie_name'
        ],
        'animal_sex' => [
            'column_name' => ['t_gender.gender_name'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_gender_name'
        ],
        'animal_date_create_record_herriot' => [
            'column_name' => ['to_char(t_application_animal.application_animal_date_horriot, \'DD.MM.YYYY\')'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'application_animal_date_horriot'
        ],
        'animal_date_birth' => [
            'column_name' => ['to_char(t_animal.animal_date_birth, \'DD.MM.YYYY\')'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_date_birth'
        ],
        'animal_breed' => [
            'column_name' => ['t_animal_breed.breed_name'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_breed_name'
        ],
        'animal_date_create_record_svr' => [
            'column_name' => ['to_char(t_animal.animal_date_create_record, \'DD.MM.YYYY\')'],
            'to_lower' => true,
            'section' => 'where',
            'order_field' => 'animal_date_create_record'
        ],
        'animal_status' => [
            'column_name' => [],
            'to_lower' => false,
            'section' => 'where',
            'order_field' => 'animal_status'
        ],
        'application_animal_status' => [
            'column_name' => ['t_application_animal.application_animal_status::text'],
            'to_lower' => false,
            'section' => 'where',
            'order_field' => 'application_animal_status'
        ],
    ];

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
     * Получить первичный ключ
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

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
	 * Получить правила валидации
	 * @param Request $request
	 *
	 * @return string[]
	 */
	private function getValidationRules(Request $request): array
	{
		return [
			$this->primaryKey => [
				$request->isMethod('put') ? 'required' : '',
				Rule::exists('.'.$this->getTable(), $this->primaryKey),
			]
		];
	}


	/**
	 * Получить сообщения об ошибках валидации
	 * @return array
	 */
	private function getValidationMessages(): array
	{
		return [
			$this->primaryKey => trans('svr-core-lang::validation.required')
		];
	}

    /**
     * Данные по животным
     * @param $animal_id
     * @param $application_id
     * @return array|null
     */
    public static function animalData($animal_id, $application_id = false): ?array
    {
        $where = self::createFilterRestrictions([]);

        if ($application_id !== false && (int)$application_id > 0) {
            $application_left_join =
                ' LEFT JOIN ' .DataApplicationsAnimals::getTableName(). ' t_application_animal ON
                    t_application_animal.animal_id = t_animal.animal_id AND
                    t_application_animal.application_id = '.$application_id;
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
					t_application.doctor_id,
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
					LEFT JOIN ' . DataApplications::getTableName() . '          t_application ON t_application.application_id = t_application_animal.application_id
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

        if(count($animal_data) > 0)
        {
            $animal_data = (array)$animal_data[0];
            $animal_data['animal_registration_available'] = self::animalRegistrationAvailable($animal_data);
        } else {
            return null;
        }

        return $animal_data;
    }

    /**
     * Список животных
     * @param $count_per_page
     * @param $page_number
     * @param bool $only_enabled
     * @param array $filters_list
     * @param string $valid_data
     * @return false|array
     */
    public static function animalsList($count_per_page, $page_number, $only_enabled = true, $filters_list = [], $valid_data = ''): false|array
    {
        //TODO: Тут сейчас будет ерунда, надо будет переделать когда появится сознание
        if (!isset($filters_list)) $filters_list = [];
        if (!isset($filters_list['specie_id']))
        {
            $filters_list['specie_id'] = [];
        } else {
            if (!is_array($filters_list['specie_id']))
            {
                $filters_list['specie_id'] = [$filters_list['specie_id']];
            }
        }
        if (!isset($filters_list['breeds_id']))
        {
            $filters_list['breeds_id'] = [];
        } else {
            if (!is_array($filters_list['breeds_id']))
            {
                $filters_list['breeds_id'] = [$filters_list['breeds_id']];
            }
        }
        if (!isset($filters_list['application_id']))
        {
            $filters_list['application_id'] = [];
        } else {
            if (!is_array($filters_list['application_id']))
            {
                $filters_list['application_id'] = [$filters_list['application_id']];
            }
        }

        $user = auth()->user();

        $where_view = " animal_status_delete = 'active' ";

        $where_view .= self::createFilterRestrictions($valid_data);

        if (count($filters_list['application_id']) > 0) {
            $application_left_join = ' LEFT JOIN ' .DataApplicationsAnimals::getTableName(). ' t_application_animal ON
										t_application_animal.animal_id = t_animal.animal_id AND
										t_application_animal.application_id IN (' . implode(',', $filters_list['application_id']) . ')';
        }
        else
        {
            $application_left_join = ' LEFT JOIN
											(
												SELECT MAX(application_id) AS application_id, animal_id
												FROM ' .DataApplicationsAnimals::getTableName(). ' t_application_animal_temp GROUP BY animal_id
											) t_application_animal_temp ON t_application_animal_temp.animal_id = t_animal.animal_id
										LEFT JOIN ' .DataApplicationsAnimals::getTableName(). ' t_application_animal ON t_application_animal.animal_id = t_animal.animal_id
											AND t_application_animal.application_id = t_application_animal_temp.application_id';
        }

        $where = ' ';

        if (count($filters_list) > 0)
        {
            $where .= self::createFilterSql($filters_list);
        }

        if ($only_enabled) $where .= " AND t_animal.animal_status = 'enabled' ";

        $order_string = '';
        if ($user['order_field'] !== false && array_key_exists($user['order_field'], self::$columns_list))
        {
            $order_field = $user['order_field'];
            if (self::$columns_list[$order_field]['order_field'] !== false) $order_field = self::$columns_list[$order_field]['order_field'];
            $order_string = ' ORDER BY ' . $order_field . ' ' . $user['order_direction'];
        }

        $query = 'SELECT * FROM (SELECT DISTINCT ON (t_animal.animal_id)
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
				WHERE ' . $where_view.$where .') AS temp '. $order_string . ' LIMIT :items_limit OFFSET :items_offset';

        $animals_list = DB::select($query, [
            'items_limit' => (int)$count_per_page,
            'items_offset' => (int)$count_per_page * ((int)$page_number - 1)
        ]);

        if ($animals_list === false || count($animals_list) < 1) {
            return false;
        }

        foreach ($animals_list as &$animal_data)
        {
            $animal_data = (array)$animal_data;
            $animal_data['animal_registration_available'] = self::animalRegistrationAvailable($animal_data);
        }
        $animals_count_query = 'SELECT COUNT(*) AS cnt FROM (SELECT DISTINCT ON (t_animal.animal_id)
    				t_animal.animal_id
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
				WHERE ' . $where_view.$where .') AS temp';

        $animals_count = DB::select($animals_count_query);

        Config::set('total_records', ((array)$animals_count[0])['cnt']);

        return $animals_list;
    }

    /**
     * Добавляет фильтры в запрос в зависимости от входящих фильтров
     * @param $filters_list
     * @return string
     */
    private static function createFilterSql($filters_list): string
    {
        if (isset($filters_list['animal_date_create_record_herriot_min'])) $filters_list['animal_date_create_record_herriot_min'] = date('Y-m-d', strtotime($filters_list['animal_date_create_record_herriot_min']));
        if (isset($filters_list['animal_date_create_record_herriot_max'])) $filters_list['animal_date_create_record_herriot_max'] = date('Y-m-d', strtotime($filters_list['animal_date_create_record_herriot_max']));

        if (isset($filters_list['animal_date_create_record_svr_min'])) $filters_list['animal_date_create_record_svr_min'] = date('Y-m-d', strtotime($filters_list['animal_date_create_record_svr_min']));
        if (isset($filters_list['animal_date_create_record_svr_max'])) $filters_list['animal_date_create_record_svr_max'] = date('Y-m-d', strtotime($filters_list['animal_date_create_record_svr_max']));

        if (isset($filters_list['animal_date_birth_min'])) $filters_list['animal_date_birth_min'] = date('Y-m-d', strtotime($filters_list['animal_date_birth_min']));
        if (isset($filters_list['animal_date_birth_max'])) $filters_list['animal_date_birth_max'] = date('Y-m-d', strtotime($filters_list['animal_date_birth_max']));

        $filters_mapping = [
            'animal_sex' => " AND t_animal.animal_sex = '". strtolower(($filters_list['animal_sex'] ?? false)) . "'",
            'specie_id' => " AND t_animal_breed.specie_id IN (" . implode(',', ($filters_list['specie_id'] ?? [])) . ")",
            'breeds_id' => " AND t_animal.breed_id IN (" . implode(',', ($filters_list['breeds_id'] ?? [])) . ")",
            'application_id' => " AND t_application_animal.application_id IN (" . implode(',', ($filters_list['application_id'] ?? [])) . ")",
            'animal_date_create_record_herriot_min' => " AND t_application_animal.application_animal_date_horriot >= '" . ($filters_list['animal_date_create_record_herriot_min'] ?? false) . "'",
            'animal_date_create_record_herriot_max' => " AND t_application_animal.application_animal_date_horriot <= '" . ($filters_list['animal_date_create_record_herriot_max'] ?? false) . "'",
            'animal_date_create_record_svr_min' => " AND t_animal.animal_date_create_record >= '" . ($filters_list['animal_date_create_record_svr_min'] ?? false) . "'",
            'animal_date_create_record_svr_max' => " AND t_animal.animal_date_create_record <= '" . ($filters_list['animal_date_create_record_svr_max'] ?? false) . "'",
            'animal_date_birth_min' => " AND t_animal.animal_date_birth >= '" . ($filters_list['animal_date_birth_min'] ?? false). "'",
            'animal_date_birth_max' => " AND t_animal.animal_date_birth <= '" . ($filters_list['animal_date_birth_max'] ?? false) . "'",
//            'register_status' => " AND t_application_animal.application_animal_status = '" . ($filters_list['register_status'] ?? false) ."'",
            'animal_status' => " AND t_animal.animal_status = '" . ($filters_list['animal_status'] ?? false) ."'",
            'search_inv' => " AND t_animal.animal_code_inv_value ILIKE '%" . ($filters_list['search_inv'] ?? false) ."%'",
            'search_unsm' => " AND lower(t_animal.animal_code_rshn_value) ILIKE '%" . mb_strtolower(($filters_list['search_unsm'] ?? false)) ."%'",
            'search_horriot_number' => " AND lower(t_animal.animal_number_horriot) ILIKE '%" . mb_strtolower(($filters_list['search_horriot_number'] ?? false)) ."%'",
        ];

        if (isset($filters_list['application_animal_status']) && $filters_list['application_animal_status'] == 'added')
        {
            $filters_mapping['application_animal_status'] = " AND t_application_animal.application_animal_status IS NULL";
        }else {
            $filters_mapping['application_animal_status'] = " AND t_application_animal.application_animal_status = '" . ($filters_list['application_animal_status'] ?? false) ."'";
        }

        $query = '';

        foreach ($filters_list as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $query .= $filters_mapping[$key];
        }

        return ($query);
    }


    /**
     * Добавляет фильтры в зависимости от текущей роли пользователя
     * @param $valid_data
     * @return string
     */
    private static function createFilterRestrictions($valid_data): string
    {
        $user_token_data = auth()->user();
        $user_role_data = SystemRoles::find($user_token_data['role_id'])->toArray();

        $where_view = '';
        switch ($user_role_data['role_slug'])
        {
            case 'admin':
                if (isset($valid_data['company_location_id']) && SystemFilter::is_num($valid_data['company_location_id'])) {
                    $where_view .= ' AND t_animal.company_location_id = ' . (int)$valid_data['company_location_id'];
                }
                if (isset($valid_data['company_region_id']) && SystemFilter::is_num($valid_data['company_region_id'])) {
                    $where_view .= ' AND t_animal_owner_company.region_id = ' . (int)$valid_data['company_region_id'];
                }
                if (isset($valid_data['company_district_id']) && SystemFilter::is_num($valid_data['company_district_id'])) {
                    $where_view .= ' AND t_animal_owner_company.district_id = ' . (int)$valid_data['company_district_id'];
                }
                break;
            case 'doctor_company':
                $where_view .= ' AND t_animal.company_location_id = ' . (int)$user_token_data['company_location_id'];
                break;
            case 'doctor_region':
                $where_view .= ' AND t_animal_owner_company.region_id = ' . (int)$user_token_data['region_region_id'];
                break;
            case 'doctor_district':
                $where_view .= ' AND t_animal_owner_company.district_id = ' . (int)$user_token_data['district_district_id'];
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
    public static function animalRegistrationAvailable(array $animal_data): bool
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

    /**
     * Редактирование объекта содержания животного
     * @param $animal_id
     * @param $company_object_id
     * @return mixed
     */
    public static function setAnimalKeepingCompanyObject($animal_id, $company_object_id): mixed
    {
        return self::find($animal_id)->update(['animal_object_of_keeping_id' => $company_object_id]);
    }

    /**
     * Редактирование объекта рождения животного
     * @param $animal_id
     * @param $company_object_id
     * @return mixed
     */
    public static function setAnimalBirthCompanyObject($animal_id, $company_object_id): mixed
    {
        return self::find($animal_id)->update(['animal_object_of_birth_id' => $company_object_id]);
    }

    /**
     * Групповое обновление данных животных
     * @param $data_for_update
     * @param $animals_id
     * @return int
     */
    public static function updateAnimalsGroup($data_for_update, $animals_id): int
    {
        return DB::table(self::getTableName())->whereIn('animal_id', $animals_id)->update($data_for_update);
    }

    /**
     * Обновление вида содержания животного
     * @param $animal_id
     * @param $keeping_type_id
     * @return int
     */
    public static function setAnimalKeepingType($animal_id, $keeping_type_id): int
    {
        return DB::table(self::getTableName())
            ->where('animal_id', '=', $animal_id)
            ->update(['animal_type_of_keeping_id' => $keeping_type_id]);
    }

    /**
     * Обновление причины содержания животного
     * @param $animal_id
     * @param $keeping_purpose_id
     * @return int
     */
    public static function setAnimalKeepingPurpose($animal_id, $keeping_purpose_id): int
    {
        return DB::table(self::getTableName())
            ->where('animal_id', '=', $animal_id)
            ->update(['animal_purpose_of_keeping_id' => $keeping_purpose_id]);
    }

    /**
     * Набор справочников для животного
     * @param $animal_data
     * @param $mark_data
     * @return array
     */
    public static function getDirectoriesForAnimalData($animal_data, $mark_data): array
    {
        $list_directories = [];

        $countries_ids = array_filter([$animal_data['animal_country_nameport_id']]);
        if (count($countries_ids) > 0) {
            $list_directories['countries_list'] = DirectoryCountries::find($countries_ids);
        }

        $species_ids = array_filter([$animal_data['animal_specie_id']]);
        if (count($species_ids) > 0) {
            $list_directories['species_list'] = DirectoryAnimalsSpecies::find($species_ids);
        }

        $breeds_ids = array_filter([$animal_data['animal_breed_id'], $animal_data['animal_father_breed_id'], $animal_data['animal_mother_breed_id']]);
        if (count($breeds_ids) > 0) {
            $list_directories['breeds_list'] = DirectoryAnimalsBreeds::find($breeds_ids);
        }

        $genders_ids = array_filter([$animal_data['animal_gender_id']]);
        if (count($genders_ids) > 0) {
            $list_directories['genders_list'] = DirectoryGenders::find($genders_ids);
        }

        $companies_ids = array_filter([$animal_data['animal_owner_company_id'], $animal_data['animal_keeping_company_id'], $animal_data['animal_birth_company_id']]);
        if (count($companies_ids) > 0) {
            $list_directories['companies_list'] = DataCompaniesLocations::companyLocationDataByCompanyId($companies_ids);
        }

        $keeping_types_ids = array_filter([$animal_data['animal_type_of_keeping_id']]);
        if (count($keeping_types_ids) > 0) {
            $list_directories['keeping_types_list'] = DirectoryKeepingTypes::find($keeping_types_ids);
        }

        $keeping_purposes_ids = array_filter([$animal_data['animal_keeping_purpose_id']]);
        if (count($keeping_purposes_ids) > 0) {
            $list_directories['keeping_purposes_list'] = DirectoryKeepingPurposes::find($keeping_purposes_ids);
        }

        $out_types_ids = array_filter([$animal_data['animal_out_type_id']]);
        if (count($out_types_ids) > 0) {
            $list_directories['out_types_list'] = DirectoryOutTypes::find($out_types_ids);
        }

        $out_basises_ids = array_filter([$animal_data['animal_out_basis_id']]);
        if (count($out_basises_ids) > 0) {
            $list_directories['out_basises_list'] = DirectoryOutBasises::find($out_basises_ids);
        }

        $companies_objects_ids = array_filter([$animal_data['animal_object_of_keeping_id'], $animal_data['animal_object_of_birth_id']]);
        if (count($companies_objects_ids) > 0) {
            $list_directories['companies_objects_list'] = DataCompaniesObjects::find($companies_objects_ids);
        }

        if ($mark_data)
        {
            $mark_types_ids = array_filter(array_column($mark_data, 'mark_type_id'));
            if (count($mark_types_ids) > 0) {
                $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
            }

            $mark_statuses_ids = array_filter(array_column($mark_data,'mark_status_id'));
            if (count($mark_statuses_ids) > 0)
            {
                $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
            }

            $mark_tool_types_ids = array_filter(array_column($mark_data, 'mark_tool_type_id'));
            if (count($mark_tool_types_ids) > 0)
            {
                $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
            }

            $mark_tools_locations_ids = array_filter(array_column($mark_data, 'tool_location_id'));
            if (count($mark_tools_locations_ids) > 0)
            {
                $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
            }
        }

        return $list_directories;
    }

    /**
     * Набор справочников для животного
     * @param $animals_list
     * @param $all_mark_data
     * @return array
     */
    public static function getDirectoriesForAnimalsList($animals_list, $all_mark_data): array
    {
        $list_directories = [];

        $countries_ids = array_filter(array_column($animals_list, 'animal_country_nameport_id'));
        if (count($countries_ids) > 0) {
            $list_directories['countries_list'] = DirectoryCountries::find($countries_ids);;
        }

        $species_ids = array_filter(array_column($animals_list, 'animal_specie_id'));
        if (count($species_ids) > 0) {
            $list_directories['species_list'] = DirectoryAnimalsSpecies::find($species_ids);
        }

        $breeds_ids = array_filter(array_merge(array_column($animals_list, 'animal_breed_id'), array_column($animals_list, 'animal_father_breed_id'), array_column($animals_list, 'animal_mother_breed_id')));
        if (count($breeds_ids) > 0) {
            $list_directories['breeds_list'] = DirectoryAnimalsBreeds::find($breeds_ids);
        }

        $genders_ids = array_filter(array_column($animals_list, 'animal_gender_id'));
        if (count($genders_ids) > 0) {
            $list_directories['genders_list'] = DirectoryGenders::find($genders_ids);
        }

        $companies_ids = array_filter(array_merge(array_column($animals_list, 'animal_owner_company_id'), array_column($animals_list, 'animal_keeping_company_id'), array_column($animals_list, 'animal_birth_company_id')));
        if (count($companies_ids) > 0) {
            $list_directories['companies_list'] = DataCompaniesLocations::companyLocationDataByCompanyId($companies_ids);
        }

        $keeping_types_ids = array_filter(array_column($animals_list, 'animal_type_of_keeping_id'));
        if (count($keeping_types_ids) > 0) {
            $list_directories['keeping_types_list'] = DirectoryKeepingTypes::find($keeping_types_ids);
        }

        $keeping_purposes_ids = array_filter(array_column($animals_list, 'animal_keeping_purpose_id'));
        if (count($keeping_purposes_ids) > 0) {
            $list_directories['keeping_purposes_list'] = DirectoryKeepingPurposes::find($keeping_purposes_ids);
        }

        $out_types_ids = array_filter(array_column($animals_list, 'animal_out_type_id'));
        if (count($out_types_ids) > 0) {
            $list_directories['out_types_list'] = DirectoryOutTypes::find($out_types_ids);
        }

        $out_basises_ids = array_filter(array_column($animals_list, 'animal_out_basis_id'));
        if (count($out_basises_ids) > 0) {
            $list_directories['out_basises_list'] = DirectoryOutBasises::find($out_basises_ids);
        }

        $companies_objects_ids = array_filter(array_merge(array_column($animals_list, 'animal_object_of_keeping_id'), array_column($animals_list, 'animal_object_of_birth_id')));
        if (count($companies_objects_ids) > 0)
        {
            $list_directories['companies_objects_list'] = DataCompaniesObjects::find($companies_objects_ids);
        }

        if (count($all_mark_data) > 0)
        {
            $mark_types_ids = array_filter(array_column($all_mark_data, 'mark_type_id'));
            if (count($mark_types_ids) > 0)
            {
                $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
            }

            $mark_statuses_ids = array_filter(array_column($all_mark_data,'mark_status_id'));
            if (count($mark_statuses_ids) > 0)
            {
                $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
            }

            $mark_tool_types_ids = array_filter(array_column($all_mark_data, 'mark_tool_type_id'));
            if (count($mark_tool_types_ids) > 0)
            {
                $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
            }

            $mark_tools_locations_ids = array_filter(array_column($all_mark_data, 'tool_location_id'));
            if (count($mark_tools_locations_ids) > 0)
            {
                $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
            }
        }

        return $list_directories;
    }

    /**
     * Создать запись
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function animalCreate(Request $request): mixed
    {
        DB::statement("SET session_replication_role = 'replica';");
        $this->validateRequest($request);
        $this->fill($request->all())->save();
        DB::statement("SET session_replication_role = 'origin';");
        $animal = $this->find($this->getKey());
        return $animal->animal_id;
    }
}
