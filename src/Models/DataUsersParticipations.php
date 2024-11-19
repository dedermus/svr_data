<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemParticipationsTypesEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemRoles;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;
use Symfony\Component\VarDumper\Cloner\Data;

class DataUsersParticipations extends Model
{
    use HasFactory;


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
        'user_id',									//* ID пользователя в таблице SYSTEM.SYSTEM_USERS
		'participation_item_type',					//* Тип привязки (компания/регион/район)
		'participation_item_id',					//* ID привязки (company_location_id/region_id/district_id)
		'role_id',									//* ID роли в таблице SYSTEM.SYSTEM_ROLES
		'participation_status',						//* Статус связки
		'created_at',					            //* Дата и время создания
		'updated_at',								//* дата последнего изменения строки записи */
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
    private function validateRequest(Request $request)
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
        $id = $request->input($this->primaryKey);

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
     * @param $user_id - пользователь или массив пользователей
     *
     * @return \Illuminate\Support\Collection
     */
    public static function userCompaniesLocationsList($user_id)
    {
        $user_id = is_array($user_id) ? $user_id : [$user_id];

        $items_list = DB::table((new DataUsersParticipations())->table.' as up')
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
                ['up.participation_item_type', '=', 'company'],
                ['c.company_status', '=', 'enabled'],
                ['r.role_status', '=', 'enabled'],
                ['up.participation_status', '=', 'enabled'],
            ])
            ->whereIn('up.user_id', $user_id)
            ->get();
        return $items_list;
    }

    /**
     * Короткий список по компании
     * @param $userCompaniesLocations - коллекция привязок компаний к пользователю
     *
     * @return array
     */
    public static function userCompaniesLocationsShort($userCompaniesLocations)
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
    public static function userCompaniesLocationsLong($userCompaniesLocations)
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
}
