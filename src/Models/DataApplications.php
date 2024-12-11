<?php

namespace Svr\Data\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\ApplicationStatusEnum;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

use Svr\Core\Exceptions\CustomException;

class DataApplications extends Model
{
	use GetTableName;
    use HasFactory;
	use GetValidationRules;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_applications';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'application_id';


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
	 * Значения полей по умолчанию
	 * @var array
	 */
	protected $attributes							= [
		'application_status'							=> 'created',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'company_location_id',						//* id компании в районе-регионе */
		'user_id',									//* id пользователя */
		'doctor_id',								//* id пользователя отправившего заявку */
		'application_date_create',					//* дата создания заявки */
		'application_date_horriot',					//* дата отправки в хорриот */
		'application_date_complete',				//* дата получения ответа из хорриот по всем животным */
		'application_status',						//* статус заявки */
		'created_at',								//* дата создания строки */
		'updated_at',								//* дата последнего изменения строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'application_id',
	];


	/**
	 * Реляция компаний-локаций
	 */
	public function company_location()
	{
		return $this->belongsTo(DataCompaniesLocations::class, 'company_location_id', 'company_location_id');
	}


	/**
	 * Реляция пользователей
	 */
	public function user()
	{
		return $this->belongsTo(SystemUsers::class, 'user_id', 'user_id');
	}


	/**
	 * Реляция пользователей (доктор)
	 */
	public function doctor()
	{
		return $this->belongsTo(SystemUsers::class, 'doctor_id', 'user_id');
	}


    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function applicationCreate($request): void
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }


    /**
     * Обновить запись
     * @param $request
     *
     * @return void
     */
    public function applicationUpdate($request): void
    {
        $this->validateRequest($request);
        $data = $request->all();
        $id = $data[$this->primaryKey] ?? null;

        if ($id) {
            $setting = $this->find($id);
            if ($setting) {
                $setting->update($data);
            }
        }
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
            ],
            'company_location_id' => 'required|int|exists:.data.data_companies_locations,company_location_id',
            'user_id' => 'required|int|exists:.system.system_users,user_id',
            'doctor_id' => 'int|exists:.system.system_users,user_id',
            'application_date_create' => 'required|date',
            'application_date_horriot' => 'date',
            'application_date_complete' => 'date',
            'application_status' => ['required', Rule::enum(ApplicationStatusEnum::class)],
        ];
    }


    /**
     * Получить сообщения об ошибках валидации
     * @return array
     */
    private function getValidationMessages(): array
    {
        return [
            $this->primaryKey => trans('svr-core-lang::validation.required'),
            'company_location_id' => trans('svr-core-lang::validation'),
            'user_id' => trans('svr-core-lang::validation'),
            'doctor_id' => trans('svr-core-lang::validation'),
            'application_date_create' => trans('svr-core-lang::validation'),
            'application_date_horriot' => trans('svr-core-lang::validation'),
            'application_date_complete' => trans('svr-core-lang::validation'),
            'application_status' => trans('svr-core-lang::validation'),
        ];
    }


	public static function applicationData($application_id = false, $current = false, $create_new = true)
	{
		$user				= auth()->user();
		$where_data			= [];

		if($current)
		{
			$where_data[]	= ['a.company_location_id', '=', $user['company_location_id']];
			$where_data[]	= ['a.application_status', '=', 'created'];
		}else{
			$where_data[]	= ['application_id', '=', $application_id];
		}

		$application_data	= DB::table(DataApplications::GetTableName().' as a')
			->select(
				'a.*',
				'user.user_id',
				'user.user_last',
				'user.user_first',
				'user.user_middle',
				'user.user_avatar',
				'user.user_status',
				'user.user_date_created',
				'user.user_date_block',
				'user.user_phone',
				'user.user_email',
				'doctor.user_herriot_login',
				'doctor.user_herriot_password',
				'doctor.user_herriot_web_login',
				'doctor.user_herriot_apikey',
				'doctor.user_herriot_issuerid',
				'doctor.user_herriot_serviceid',
				'c.company_name_short',
				'r.region_name',
				'rd.district_name'
			)
			->leftJoin(systemUsers::GetTableName().' as user', 'a.user_id', '=', 'user.user_id')
			->leftJoin(systemUsers::GetTableName().' as doctor', 'a.doctor_id', '=', 'doctor.user_id')
			->leftJoin(DataCompaniesLocations::GetTableName().' as cl', 'a.company_location_id', '=', 'cl.company_location_id')
			->leftJoin(DataCompanies::GetTableName().' as c', 'cl.company_id', '=', 'c.company_id')
			->leftJoin(DirectoryCountriesRegion::GetTableName().' as r', 'cl.region_id', '=', 'r.region_id')
			->leftJoin(DirectoryCountriesRegionsDistrict::GetTableName().' as rd', 'cl.district_id', '=', 'rd.district_id')
			->whereRaw(DataApplications::appicationsFilterRestrictions())
			->where($where_data)
			->first();

		if(!is_null($application_data))
		{
			return $application_data;
		}else{
			if($create_new)
			{
				$application_id = DB::table(DataApplications::GetTableName())->insertGetId([
					'company_location_id'		=> $user['company_location_id'],
					'user_id'					=> $user['user_id']
				], 'application_id');

				return DataApplications::find($application_id);
			}else{
				return false;
			}
		}
	}


	public static function appicationsFilterRestrictions()
	{
		$user_token_data	= auth()->user();
		$user_role_data		= SystemRoles::find($user_token_data['role_id'])->toArray();

		switch ($user_role_data['role_slug'])
		{
			case 'doctor_company':
				return 'a.company_location_id = '.(int)$user_token_data['company_location_id'];
			break;
			case 'doctor_region':
				return 'cl.region_id = '.(int)$user_token_data['region_region_id'];
			break;
			case 'doctor_district':
				return 'cl.district_id = '.(int)$user_token_data['district_district_id'];
			break;
		}
	}



	public static function applicationsAnimalAdd($animal_id)
	{
		$animal_data				= DataAnimals::animalData($animal_id);

		if(is_null($animal_data))
		{
			throw new CustomException('Животное не найдено', 200);
		}

		if(!empty($animal_data['animal_guid_horriot']))
		{
			throw new CustomException('Животное уже имеет GUID', 200);
		}

		if($animal_data['animal_status'] == 'disabled')
		{
			throw new CustomException('Животное не активно', 200);
		}

		if($animal_data['animal_status_delete'] !== 'active')
		{
			throw new CustomException('Животное удалено', 200);
		}

		if($animal_data['animal_registration_available'] === false)
		{
			throw new CustomException('Животное не подготовлено к регистрации', 200);
		}

		if(!empty($animal_data['application_animal_status']))
		{
			switch($animal_data['application_animal_status'])
			{
				case 'added':
				case 'in_application':
				case 'sent':
					throw new CustomException('Животное уже находится в заявке', 200);
				break;
				case 'registered':
				case 'finished':
					throw new CustomException('Животное уже зарегистрировано', 200);
				break;
				case 'rejected':
					// можно добавлять в заявку
				break;
			}
		}

		$application_data			= DataApplications::applicationData(false, true, true);

		if(is_null($application_data))
		{
			throw new CustomException('Заявка не найдена', 200);
		}

		DB::table(DataApplicationsAnimals::GetTableName())->insert([
			'application_id'					=> $application_data->application_id,
			'animal_id'							=> $animal_data['animal_id'],
			'application_animal_status'			=> 'in_application'
		]);

		return true;
	}








	public static function applicationslist($search_string)
	{
		$params_data = explode(' ', $search_string);

		// список колонок, участвующих в поиске и сортировке
		$columns_list = [
			'user_id' => [
				'column_name' => ['u.user_id::text'],
				'to_lower' => false,
				'section' => 'where',
				'order_field' => 'user_id'
			],
			'user_full_name'	    =>  [
				'column_name' 	=> ['CONCAT(u.user_first, \' \', u.user_middle, \' \', u.user_last)'],
				'to_lower'		=> true,
				'section'		=> 'where',
				'order_field'	=>  'CONCAT(user_last, \' \', user_middle, \' \', user_first)',
			],
			'company_district_name'	    =>  [
				'column_name'	=> ['rd.district_name'],
				'to_lower'		=> true,
				'section'		=> 'where',
				'order_field'	=> 'district_name',
			],
			'herriot_requisites' => false,
			'application_date_create' => [
				'column_name'	=> ['to_char(a.application_date_create, \'DD.MM.YYYY\')'],
				'to_lower'		=> false,
				'section'		=> 'where',
				'order_field'	=> 'application_date_create',
			],
			'application_date_registration' => [
				'column_name'	=> ['to_char(a.application_date_complete, \'DD.MM.YYYY\')'],
				'to_lower'		=> false,
				'section'		=> 'where',
				'order_field'	=> 'application_date_complete',
			],
			'role' => false,
			'application_status' => [
				'column_name'	=> ['a.application_status::text'],
				'to_lower'		=> true,
				'section'		=> 'where',
				'order_field'	=> 'application_status',
			],
			'company_name' => [
				'column_name' 	=> ['c.company_name_full', 'c.company_name_short'],
				'to_lower' 		=> true,
				'section'		=> 'where',
				'order_field' 	=> 'company_name_short'
			],
		];

		$where_view = '';
		foreach($params_data as $param)
		{
			$where_view .= ' AND (';

			$sub_data	= ['where' => [], 'having' => []];

			foreach($columns_list as $column)
			{
				if ($column === false) continue;

				foreach ($column['column_name'] as $item)
				{
					if ($column['section'] === 'where')
					{
						if ($column['to_lower'] === true)
						{
							$sub_data[$column['section']][]	= 'lower('.$item.') ILIKE \'%'.mb_strtolower($param).'%\'';
						}else {
							$sub_data[$column['section']][]	= $item.' ILIKE \'%'.mb_strtolower($param).'%\'';
						}
					}else{
						//if (!empty($param)) $sub_data[$column['section']][]	= $item.' > 0 ';
					}
				}
			}

			$where_view .= implode(' OR ', $sub_data['where']);
			$where_view .= ')';
		}

		$query = 'SELECT
							a.*,
							u.user_last,
							u.user_first,
							u.user_middle,
							u.user_avatar,
							u.user_status,
							u.user_date_created,
							u.user_date_block,
							u.user_phone,
							u.user_email,
							d.user_herriot_login,
							d.user_herriot_password,
							d.user_herriot_web_login,
							d.user_herriot_apikey,
							d.user_herriot_issuerid,
							d.user_herriot_serviceid,
							CONCAT(u.user_first, \' \', u.user_middle, \' \', u.user_last) AS user_full_name,
							c.company_name_short,
							c.company_name_full,
							c.company_id,
							r.region_name,
							rd.district_name,
							rd.district_id
						FROM '.SCHEMA_DATA.'.'.TBL_APPLICATIONS.' AS a
							LEFT JOIN '.SCHEMA_SYSTEM.'.'.TBL_USERS.' 					AS u ON u.user_id = a.user_id
							LEFT JOIN '.SCHEMA_SYSTEM.'.'.TBL_USERS.' 					AS d ON d.user_id = a.doctor_id
							LEFT JOIN '.SCHEMA_DATA.'.'.TBL_COMPANIES_LOCATIONS.' 		AS cl ON cl.company_location_id = a.company_location_id
							LEFT JOIN '.SCHEMA_DATA.'.'.TBL_COMPANIES.' 				AS c ON c.company_id = cl.company_id
							LEFT JOIN '.SCHEMA_DIRECTORIES.'.'.TBL_COUNTRIES_REGIONS.' AS r ON r.region_id = cl.region_id
							LEFT JOIN '.SCHEMA_DIRECTORIES.'.'.TBL_COUNTRIES_REGIONS_DISTRICTS.' AS rd ON rd.district_id = cl.district_id
						WHERE 1=1 '.$this->create_filter_restrictions([]).$where_view;

		if (isset($sub_data) && count($sub_data['having']) > 0)
		{
			$query .= ' HAVING ' . implode(' AND ', $sub_data['having']);
		}

		$this->get_data(DB_MAIN, $query, null, 'rows');

		$where = ' WHERE 1=1 ';

		if(count($filters_list) > 0)
		{
			$where .= $this->create_filter_sql($filters_list);
		}

		$order_string = '';
		if ($this->REQUEST('order_field') !== false && array_key_exists($this->REQUEST('order_field'), $columns_list))
		{
			$order_field = $this->REQUEST('order_field');
			if ($columns_list[$order_field]['order_field'] !== false) $order_field = $columns_list[$order_field]['order_field'];
			$order_string = ' ORDER BY '.$order_field.' '.$this->REQUEST('order_direction');
		}

		$query = 'SELECT * FROM (SELECT DISTINCT ON (application_id) * FROM '.$view_table_name.' '.$where.') AS temp '.$order_string. ' LIMIT :items_limit OFFSET :items_offset';

		$applications_list = $this->get_data(DB_MAIN, $query, [
			'items_limit' => (int)$count_per_page,
			'items_offset' => (int)$count_per_page * ((int)$page_number - 1)
		], 'rows', 'application_id');

		if ($applications_list === false || count($applications_list) < 1)
		{
			return false;
		}

		$applications_count_query = 'SELECT DISTINCT(application_id) FROM '.$view_table_name.$where;
		$applications_count = $this->get_data(DB_MAIN, $applications_count_query, null, 'rows');

		$this->response_pagination(count($applications_count));

		$this->exec(DB_MAIN, 'DROP view IF EXISTS '.$view_table_name);

		DB::table(DataApplicationsAnimals::GetTableName())->insert([
			'application_id'					=> $application_data->application_id,
			'animal_id'							=> $animal_data['animal_id'],
			'application_animal_status'			=> 'in_application'
		]);

		return true;
	}


	private function create_filter_sql($filters_list)
	{
		if (isset($filters_list['application_date_create_min'])) $filters_list['application_date_create_min'] = date('Y-m-d', strtotime($filters_list['application_date_create_min']));
		if (isset($filters_list['application_date_create_max'])) $filters_list['application_date_create_max'] = date('Y-m-d', strtotime($filters_list['application_date_create_max']));
		if (isset($filters_list['application_date_registration_min'])) $filters_list['application_date_registration_min'] = date('Y-m-d', strtotime($filters_list['application_date_registration_min']));
		if (isset($filters_list['application_date_registration_max'])) $filters_list['application_date_registration_max'] = date('Y-m-d', strtotime($filters_list['application_date_registration_max']));

		$filters_mapping = [
			'application_id' 					=> " AND application_id = " . $filters_list['application_id'],
			'user_full_name' 					=> " AND lower(user_full_name) ILIKE '%" . (mb_strtolower($filters_list['user_full_name'])) . "%'",
			'user_id' 							=> " AND user_id = " . $filters_list['user_id'],
			//'company_district_id' 				=> ' AND district_id IN (' . implode(',', $filters_list['company_district_id']) . ')',
			'company_id' 						=> ' AND company_id IN (' . implode(',', $filters_list['company_id']) . ')',
			'district_id' 						=> ' AND district_id IN (' . implode(',', $filters_list['district_id']) . ')',
			'application_date_create_min' 		=> " AND application_date_create >= '" . $filters_list['application_date_create_min'] . "'",
			'application_date_create_max' 		=> " AND application_date_create <= '" . $filters_list['application_date_create_max'] . "'",
			'application_date_registration_min' => " AND application_date_complete >= '" . $filters_list['application_date_registration_min'] . "'",
			'application_date_registration_max' => " AND application_date_complete <= '" . $filters_list['application_date_registration_max'] . "'",
			'application_status' 				=> ' AND application_status = \'' . $filters_list['application_status']. '\'',
		];

		$query = '';

		foreach ($filters_list as $key => $value) {
			if (empty($value)) {
				continue;
			}

			$query .= $filters_mapping[$key];
		}
		return ($query);
	}
}
