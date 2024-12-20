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

use Illuminate\Support\Facades\Config;

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


	/**
	 * Получить данные заявки
	 * @return array
	 */
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
				'c.company_id',
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


	/**
	 * Получить параметра запроса
	 * @return array
	 */
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


	/**
	 * Добавление животного в заявку
	 * @return array
	 */
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


	/**
	 * Список заявок по параметрам и с фильтрами
	 * @return array
	 */
	public static function applicationsList($count_per_page, $page_number, $filter, $search_string)
	{
		$user				= auth()->user();
		$params_data		= explode(' ', trim($search_string), -1);

		// список колонок, участвующих в поиске и сортировке
		$columns_list = [
			'user_id' => [
				'column_name' 	=> ['u.user_id::text'],
				'to_lower' 		=> false,
				'order_field' 	=> 'user_id'
			],
			'user_full_name'	    =>  [
				'column_name' 	=> ['CONCAT(u.user_first, \' \', u.user_middle, \' \', u.user_last)'],
				'to_lower'		=> true,
				'order_field'	=>  'CONCAT(user_last, \' \', user_middle, \' \', user_first)',
			],
			'company_district_name'	    =>  [
				'column_name'	=> ['rd.district_name'],
				'to_lower'		=> true,
				'order_field'	=> 'district_name',
			],
			'herriot_requisites' => false,
			'application_date_create' => [
				'column_name'	=> ['to_char(a.application_date_create, \'DD.MM.YYYY\')'],
				'to_lower'		=> false,
				'order_field'	=> 'application_date_create',
			],
			'application_date_registration' => [
				'column_name'	=> ['to_char(a.application_date_complete, \'DD.MM.YYYY\')'],
				'to_lower'		=> false,
				'order_field'	=> 'application_date_complete',
			],
			'role' => false,
			'application_status' => [
				'column_name'	=> ['a.application_status::text'],
				'to_lower'		=> true,
				'order_field'	=> 'application_status',
			],
			'company_name' => [
				'column_name' 	=> ['c.company_name_full', 'c.company_name_short'],
				'to_lower' 		=> true,
				'order_field' 	=> 'company_name_short'
			],
		];

		$where_view				= '1=1 ';

		if($params_data && count($params_data) > 0)
		{
			foreach($params_data as $param)
			{
				$where_view			.= ' AND (';
				$sub_where			= [];

				foreach($columns_list as $column)
				{
					if ($column === false) continue;

					foreach ($column['column_name'] as $item)
					{
						if(!empty($param))
						{
							if ($column['to_lower'] === true)
							{
								$sub_where[]	= 'lower('.$item.') ILIKE \'%'.mb_strtolower($param).'%\'';
							}else{
								$sub_where[]	= $item.' ILIKE \'%'.mb_strtolower($param).'%\'';
							}
						}
					}
				}

				$where_view .= implode(' OR ', $sub_where);
				$where_view .= ')';
			}
		}

		$order_string = 'a.application_id DESC';

		if ($user['order_field'] !== false && array_key_exists($user['order_field'], $columns_list))
		{
			$order_field = $user['order_field'];

			if($columns_list[$order_field]['order_field'] !== false)
			{
				$order_field = $columns_list[$order_field]['order_field'];
			}

			$order_string = $order_field . ' ' . $user['order_direction'];
		}

		$applications_list	= DB::table(DataApplications::GetTableName().' as a')
			->select(
				'a.*',
				'u.user_last',
				'u.user_first',
				'u.user_middle',
				'u.user_avatar',
				'u.user_status',
				'u.user_date_created',
				'u.user_date_block',
				'u.user_phone',
				'u.user_email',
				'd.user_herriot_login',
				'd.user_herriot_password',
				'd.user_herriot_web_login',
				'd.user_herriot_apikey',
				'd.user_herriot_issuerid',
				'd.user_herriot_serviceid',
				'c.company_name_short',
				'c.company_name_full',
				'c.company_id',
				'r.region_name',
				'rd.district_name',
				'rd.district_id'
			)
			->leftJoin(systemUsers::GetTableName().' as u', 'a.user_id', '=', 'u.user_id')
			->leftJoin(systemUsers::GetTableName().' as d', 'a.doctor_id', '=', 'd.user_id')
			->leftJoin(DataCompaniesLocations::GetTableName().' as cl', 'a.company_location_id', '=', 'cl.company_location_id')
			->leftJoin(DataCompanies::GetTableName().' as c', 'cl.company_id', '=', 'c.company_id')
			->leftJoin(DirectoryCountriesRegion::GetTableName().' as r', 'cl.region_id', '=', 'r.region_id')
			->leftJoin(DirectoryCountriesRegionsDistrict::GetTableName().' as rd', 'cl.district_id', '=', 'rd.district_id')
			->whereRaw(DataApplications::appicationsFilterRestrictions())
			->whereRaw($where_view)
			->whereRaw(self::createFilterSql($filter))
			->offset($count_per_page * ($page_number - 1))
			->limit($count_per_page)
			->orderByRaw($order_string)
			->get();

		if($applications_list && !is_null($applications_list))
		{
			$applications_list_count			= DB::table(DataApplications::GetTableName().' as a')
				->select('a.application_id')
				->whereRaw(DataApplications::appicationsFilterRestrictions())
				->whereRaw($where_view)
				->whereRaw(self::createFilterSql($filter))
				->get();

			Config::set('total_records', count($applications_list_count));

			return $applications_list->toArray();
		}else{
			return null;
		}
	}


	/**
	 * Получить куска запроса с учетом фильтров
	 * @return array
	 */
	private static function createFilterSql($filters_list)
	{
		if(isset($filters_list['application_date_create_min'])) $filters_list['application_date_create_min'] = date('Y-m-d', strtotime($filters_list['application_date_create_min']));
		if(isset($filters_list['application_date_create_max'])) $filters_list['application_date_create_max'] = date('Y-m-d', strtotime($filters_list['application_date_create_max']));
		if(isset($filters_list['application_date_registration_min'])) $filters_list['application_date_registration_min'] = date('Y-m-d', strtotime($filters_list['application_date_registration_min']));
		if(isset($filters_list['application_date_registration_max'])) $filters_list['application_date_registration_max'] = date('Y-m-d', strtotime($filters_list['application_date_registration_max']));

		$filters_mapping = [
			'application_id' 					=> isset($filters_list['application_id']) ? " AND application_id = ".$filters_list['application_id'] : '',
			'user_full_name' 					=> isset($filters_list['user_full_name']) ? " AND lower(user_full_name) ILIKE '%".(mb_strtolower($filters_list['user_full_name']))."%'" : '',
			'user_id' 							=> isset($filters_list['user_id']) ? " AND user_id = " . $filters_list['user_id'] : '',
			'company_id' 						=> isset($filters_list['company_id']) ? ' AND company_id IN (' . implode(',', $filters_list['company_id']) . ')' : '',
			'district_id' 						=> isset($filters_list['district_id']) ? ' AND district_id IN (' . implode(',', $filters_list['district_id']) . ')' : '',
			'application_date_create_min' 		=> isset($filters_list['application_date_create_min']) ? " AND application_date_create >= '" . $filters_list['application_date_create_min'] . "'" : '',
			'application_date_create_max' 		=> isset($filters_list['application_date_create_max']) ? " AND application_date_create <= '" . $filters_list['application_date_create_max'] . "'" : '',
			'application_date_registration_min' => isset($filters_list['application_date_registration_min']) ? " AND application_date_complete >= '" . $filters_list['application_date_registration_min'] . "'" : '',
			'application_date_registration_max' => isset($filters_list['application_date_registration_max']) ? " AND application_date_complete <= '" . $filters_list['application_date_registration_max'] . "'" : '',
			'application_status' 				=> isset($filters_list['application_status']) ? ' AND application_status = \'' . $filters_list['application_status']. '\'' : '',
		];

		$query = '';

		foreach($filters_list as $key => $value)
		{
			if(empty($value) || empty ($value))
			{
				continue;
			}

			$query .= $filters_mapping[$key];
		}

		if(empty($query)) $query = " 1=1 ";

		return trim($query, ' AND ');
	}
}
