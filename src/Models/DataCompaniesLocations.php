<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Svr\Core\Traits\GetTableName;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;

use Illuminate\Support\Facades\DB;

class DataCompaniesLocations extends Model
{
    use GetTableName;
    use HasFactory;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_companies_locations';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'company_location_id';


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
		'location_status'								=> 'enabled',
		'location_status_delete'						=> 'active',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'company_id',								//* ID хозяйства из таблицы DATA.DATA_COMPANIES
		'region_id',								//* ID региона из справочника
		'district_id',								//* ID района из справочника
		'location_status',							//* Статус записи (активна/не активна)
		'location_status_delete',					//* Статус псевдо-удаленности записи (активна - не удалена/не активна - удалена)
		'created_at',						        //* Дата и время создания
		'updated_at',								//* дата последнего изменения строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'company_location_id',
	];


	/**
	 * Массив системных скрытых полей
	 * @var array
	 */
	protected $hidden								= [];


	/**
	 * Реляция заявки
	 */
    public function applications()
    {
        $this->hasMany(DataApplications::class, 'company_location_id', 'company_location_id');
    }


	/**
	 * Реляция хозяйства
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(DataCompanies::class, 'company_id', 'company_id');
    }


	/**
	 * Реляция регион
	 */
    /**\
     * @return HasOne
     */
    public function region(): HasOne
    {
        return $this->hasOne(DirectoryCountriesRegion::class, 'region_id', 'region_id');
    }


	/**
	 * Реляция район
     * @return HasOne
     */
    public function district(): HasOne
    {
        return $this->hasOne(DirectoryCountriesRegionsDistrict::class, 'district_id', 'district_id');
    }


	/**
	 * Данные компании-локации
	 *
     * @param $company_location_id    - ID локации компании
     * @param $company_id             - ID компании
     * @param $region_id              - ID региона
     * @param $district_id            - ID района
     * @param $nhoz                   - базовый индекс хозяйства
     *
     * @return array
     */
    public static function companyLocationData($company_location_id = false, $company_id = false, $region_id = false, $district_id = false, $nhoz = false): array
    {
        $query = DB::table('data.data_companies_locations as cl')
            ->select('cl.*', 'c.*')
            ->join('data.data_companies as c', 'cl.company_id', '=', 'c.company_id')
            ->join('directories.countries_regions as cr', 'cr.region_id', '=', 'cl.region_id')
            ->join('directories.countries_regions_districts as crd', 'crd.district_id', '=', 'cl.district_id');
        if ($company_location_id) {
            $query->where('cl.company_location_id', $company_location_id);
        } else {
            if($company_id) {
                $query->where('cl.company_id', $company_id);
            }
            if ($region_id) {
                $query->where('cl.region_id', $region_id);
            }
            if ($district_id) {
                $query->where('cl.district_id', $district_id);
            }
            if ($nhoz) {
                $query->where('c.company_base_index', $nhoz);
            }
        }

        $result = $query->first();
        if ($result === null) {
            return [];
        }

        return $result->toArray();
    }

    /**
     * Данные локаций компании
     *
     * @param $company_ids
     * @return array
     */
    public static function companyLocationDataByCompanyId($company_ids): array
    {
        return DB::table('data.data_companies_locations')
            ->join('data.data_companies', 'data.data_companies_locations.company_id', '=', 'data.data_companies.company_id')
            ->join('directories.countries_regions', 'directories.countries_regions.region_id', '=', 'data.data_companies_locations.region_id')
            ->join('directories.countries_regions_districts', 'directories.countries_regions_districts.district_id', '=', 'data.data_companies_locations.district_id')
            ->whereIn('data.data_companies_locations.company_id', $company_ids)
            ->get()->toArray();
    }


    /**
     * Создать запись
     *
     * @param $request
     * @return void
     */
    public function companyLocationCreate($request): void
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
    public function companyLocationUpdate($request): void
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
            'company_id' => 'required|exists:.data.data_companies,company_id',
            'region_id' => 'required|exists:.directories.countries_regions,region_id',
            'district_id' => 'required|exists:.directories.countries_regions_districts,district_id',
            'location_status' => ['required', Rule::in(SystemStatusEnum::get_option_list())],
            'location_status_delete' => ['required', Rule::in(SystemStatusDeleteEnum::get_option_list())],
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
            'company_id' => trans('svr-core-lang::validation'),
            'region_id' => trans('svr-core-lang::validation'),
            'district_id' => trans('svr-core-lang::validation'),
            'location_status' => trans('svr-core-lang::validation'),
            'location_status_delete' => trans('svr-core-lang::validation'),
        ];
    }
}
