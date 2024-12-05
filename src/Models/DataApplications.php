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
		$animal_data				= DataAnimals::animal_data($animal_id);

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
}
