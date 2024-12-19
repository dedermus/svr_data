<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemParticipationsTypesEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

class DataUsersParticipations extends Model
{
    use HasFactory;
    use GetValidationRules;
    use GetTableName;

	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_users_participations';

	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'participation_id';

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
		'participation_status'							=> 'enabled',
	];

	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
        'user_id',									// ID пользователя в таблице SYSTEM.SYSTEM_USERS
		'participation_item_type',					// Тип привязки (компания/регион/район)
		'participation_item_id',					// ID привязки (company_location_id/region_id/district_id)
		'role_id',									// ID роли в таблице SYSTEM.SYSTEM_ROLES
		'participation_status',						// Статус связки
		'created_at',					            // Дата и время создания
		'updated_at',								// дата последнего изменения строки записи */
	];

	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'participation_id',
	];

	/**
	 * Массив системных скрытых полей
	 * @var array
	 */
	protected $hidden								= [
		'created_at',
	];

    /**
     * Отношение участника к локации
     * @return BelongsTo
     */
    public function companyLocation(): BelongsTo
    {
        return $this->belongsTo(DataCompaniesLocations::class, 'participation_item_id', 'company_location_id');
    }

    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function userParticipationCreate($request): void
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
    public function userParticipationUpdate($request): void
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
     * Валидация запроса
     * @param Request $request
     */
    private function validateRequest(Request $request): void
    {
        $rules = $this->getValidationRules($request);
        $messages = $this->getValidationMessages();
        $request->validate($rules, $messages);
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
            'user_id' => 'required|int|exists:.system.system_users,user_id',
            'participation_item_type' => ['required', Rule::enum(SystemParticipationsTypesEnum::class)],
            'participation_item_id' => 'int',
            'role_id' => 'required|int|exists:.system.system_roles,role_id',
            'participation_status' => ['required', Rule::enum(SystemStatusEnum::class)],
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
            'user_id' => trans('svr-core-lang::validation'),
            'participation_item_type' => trans('svr-core-lang::validation'),
            'participation_item_id' => trans('svr-core-lang::validation'),
            'role_id' => trans('svr-core-lang::validation'),
            'participation_status' => trans('svr-core-lang::validation'),
        ];
    }

    /**
     * Пролучение коллекции привязок компаний к пользователю
     * @param $user_ids - id пользователя или массив id пользователей
     *
     * @return \Illuminate\Support\Collection
     */
    public static function userCompaniesLocationsList($user_ids): Collection
    {
        $user_ids = is_array($user_ids) ? $user_ids : [$user_ids];

        return DB::table((new DataUsersParticipations())->table.' as up')
            ->select('up.*',
    						'cl.company_location_id',
    						'c.company_id',
    						'c.company_name_short',
    						'c.company_name_full',
    						'c.company_status',
    						'country.country_name',
    						'country.country_id',
    						'cr.region_name',
    						'cr.region_id',
    						'crd.district_name',
    						'crd.district_id',
    						'r.role_id',
    						'r.role_name_long',
    						'r.role_name_short',
    						'r.role_slug',
    						'r.role_status' )
            ->leftjoin((new DataCompaniesLocations())->getTable().' AS cl', 'cl.company_location_id', '=', 'up.participation_item_id')
            ->leftjoin((new DataCompanies())->getTable().' AS c', 'c.company_id', '=', 'cl.company_id')
            ->leftjoin((new DirectoryCountriesRegion())->getTable().' AS cr', 'cr.region_id', '=', 'cl.region_id')
            ->leftjoin((new DirectoryCountries())->getTable().' AS country', 'country.country_id', '=', 'cr.country_id')
            ->leftjoin((new DirectoryCountriesRegionsDistrict())->getTable().' AS crd', 'crd.district_id', '=', 'cl.district_id')
            ->leftjoin((new SystemRoles())->getTable().' AS r', 'r.role_id', '=', 'up.role_id')
            ->where([
                ['up.participation_item_type', '=', SystemParticipationsTypesEnum::COMPANY->value], // ['company'],
                ['c.company_status', '=', SystemStatusEnum::ENABLED->value], // ['enabled'],
                ['r.role_status', '=', SystemStatusEnum::ENABLED->value], // ['enabled'],
                ['up.participation_status', '=', SystemStatusEnum::ENABLED->value], // ['enabled'],
            ])
            ->whereIn('up.user_id', $user_ids)
            ->get();
    }

    /**
     * Получение коллекции привязок регионов к пользователю
     * @param $user_id - пользователь или массив пользователей
     *
     * @return \Illuminate\Support\Collection
     */
    public static function userRegionsList($user_id): Collection
    {
        $user_id = is_array($user_id) ? $user_id : [$user_id];

        return DB::table((new DataUsersParticipations())->table.' as up')
            ->select('up.*',
                'country.country_name',
                'country.country_id',
                'cr.region_name',
                'cr.region_id',
                'r.role_id',
                'r.role_name_long',
                'r.role_name_short',
                'r.role_slug',
                'r.role_status' )
            ->leftjoin((new DirectoryCountriesRegion())->getTable().' AS cr', 'cr.region_id', '=', 'up.participation_item_id')
            ->leftjoin((new DirectoryCountries())->getTable().' AS country', 'country.country_id', '=', 'cr.country_id')
            ->leftjoin((new SystemRoles())->getTable().' AS r', 'r.role_id', '=', 'up.role_id')
            ->where([
                ['up.participation_item_type', '=', SystemParticipationsTypesEnum::REGION->value], // 'region'],
                ['r.role_status', '=', SystemStatusEnum::ENABLED->value], // 'enabled'],
                ['up.participation_status', '=', SystemStatusEnum::ENABLED->value], // 'enabled'],
            ])
            ->whereIn('up.user_id', $user_id)
            ->get();
    }

    /**
     * Получение коллекции привязок Районов к пользователю
     * @param $user_id - пользователь или массив пользователей
     *
     * @return \Illuminate\Support\Collection
     */
    public static function userDistrictsList($user_id): Collection
    {
        $user_id = is_array($user_id) ? $user_id : [$user_id];

        return DB::table((new DataUsersParticipations())->table.' as up')
            ->select('up.*',
                'country.country_name',
                'country.country_id',
                'cr.region_name',
                'cr.region_id',
                'crd.district_name',
                'crd.district_id',
                'r.role_id',
                'r.role_name_long',
                'r.role_name_short',
                'r.role_slug',
                'r.role_status' )
            ->leftjoin((new DirectoryCountriesRegionsDistrict())->getTable().' AS crd', 'crd.district_id', '=', 'up.participation_item_id')
            ->leftjoin((new DirectoryCountriesRegion())->getTable().' AS cr', 'cr.region_id', '=', 'crd.region_id')
            ->leftjoin((new DirectoryCountries())->getTable().' AS country', 'country.country_id', '=', 'cr.country_id')
            ->leftjoin((new SystemRoles())->getTable().' AS r', 'r.role_id', '=', 'up.role_id')
            ->where([
                ['up.participation_item_type', '=', SystemParticipationsTypesEnum::DISTRICT->value], // 'region'],
                ['r.role_status', '=', SystemStatusEnum::ENABLED->value], // 'enabled'],
                ['up.participation_status', '=', SystemStatusEnum::ENABLED->value], // 'enabled'],
            ])
            ->whereIn('up.user_id', $user_id)
            ->get();
    }

    /**
     * Короткий список по компании
     * @param $userCompaniesLocations - коллекция привязок компаний к пользователю
     *
     * @return array
     */
    public static function userCompaniesLocationsShort($userCompaniesLocations): array
    {
        $listKey = ['company_location_id'];
        $result = [];
        foreach ($userCompaniesLocations as $item) {
            $filteredItem = array_intersect_key((array)$item, array_flip($listKey));
            $result[] = reset($filteredItem); // Извлекаем первое значение из массива
        }
        return $result;
    }

    /**
     * Полный список полей по компании
     * @param $userCompaniesLocations - коллекция привязок компаний к пользователю
     *
     * @return array
     */
    public static function userCompaniesLocationsLong($userCompaniesLocations): array
    {
        $listKey = [
            'company_location_id',
            'company_id',
            'company_name_short',
            'company_name_full',
            'country_name',
            'country_id',
            'region_name',
            'region_id',
            'district_name',
            'district_id',
            'active',
        ];
        $result = [];

        foreach ($userCompaniesLocations as $item) {
            // Преобразуем объект в массив для фильтрации
            $filteredItem = array_intersect_key((array)$item, array_flip($listKey));

            // Используем -> для доступа к свойству объекта
            $participationId = $item->participation_id ?? null;

            if ($participationId !== null) {
                $result[$participationId] = $filteredItem; // Используем participation_id как ключ
            }
        }

        return $result;
    }

    /**
     * Получает данные о участии пользователя на основе предоставленного идентификатора участия.
     *
     * @param int $participation_id Идентификатор участия для получения данных.
     * @return \Illuminate\Support\Collection Коллекция данных о участии пользователя.
     */
    /*public static function userParticipationData($participation_id)
    {
        return DB::table((new DataUsersParticipations())->table)
            ->where('participation_id', '=', $participation_id)
            ->get();
    }*/

    /**
     * Получает информацию о привязке пользователя на основе предоставленных данных об участии.
     *
     * @param $participation_id - идентификатор
     * @return array Информация о привязке пользователя с 'company_location_id', 'region_id' и 'district_id' и 'role_id'.
     */
    public static function userParticipationInfo($participation_id): array
    {
        $participation_info = [
            'company_location_id'   => false,
            'region_id'             => false,
            'district_id'           => false,
            'role_id'               => false
        ];

        if ((int)$participation_id < 1)
        {
            return $participation_info;
        }

        $participation_data = DataUsersParticipations::find($participation_id)->toArray();

        if (!$participation_data || !is_array($participation_data) || !isset($participation_data['participation_item_type']))
        {
            return $participation_info;
        }

        $participation_info['role_id'] = $participation_data['role_id'];

        switch($participation_data['participation_item_type']){
            case SystemParticipationsTypesEnum::COMPANY->value:
                $participation_info['company_location_id'] = $participation_data['participation_item_id'];
                break;
            case SystemParticipationsTypesEnum::REGION->value:
                $participation_info['region_id'] = $participation_data['participation_item_id'];
                break;
            case SystemParticipationsTypesEnum::DISTRICT->value:
                $participation_info['district_id'] = $participation_data['participation_item_id'];
                break;
        }
        return $participation_info;
    }

    /**
     * Получить общее количество компаний пользователя
     * @param $user_id
     *
     * @return int
     */
    public static function getUsersCompaniesCount($user_id): int
    {
        return DB::table(DataUsersParticipations::getTableName())
            ->where([
            ['user_id', '=', $user_id],
            ['participation_item_type', '=', SystemParticipationsTypesEnum::COMPANY->value]
        ])->get()->count();
    }

    /**
     * @param Request $request
     * @param $user
     *
     * @return object|null
     */
    public static function setUsersParticipations(Request $request, $user): ?object
    {
        // - проверим возможность привязки пользователя
        return DB::table(DataUsersParticipations::getTableName().' as dup')
            ->select('dup.*')
            ->leftjoin(SystemUsers::getTableName().' AS su', 'su.user_id', '=', 'dup.user_id')
            ->leftjoin(SystemUsersToken::getTableName().' AS sut', 'sut.user_id', '=', 'sut.user_id')
            ->where([
                ['su.user_id', '=', $user['user_id']],
                ['sut.token_value', '=', $user['token']],
                ['dup.participation_item_id', '=', $request->query('participation_item_id')],
                ['dup.participation_item_type', '=', $request->query('participation_type')]
            ])
            ->first();
    }
}
