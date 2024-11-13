<?php

namespace Svr\Data\Models;

use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;

class DataCompaniesLocations extends Model
{
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
		'company_location_id',						//* Инкремент
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
	protected $hidden								= [

	];


	/**
	 * Преобразование полей при чтении/записи
	 * @return array
	 */
	protected function casts(): array
	{
		return [
//			'update_at'								=> 'timestamp',
//			'location_created_at'					=> 'timestamp',
		];
	}



    public function applications()
    {
        $this->hasMany(DataApplications::class, 'company_location_id', 'company_location_id');
    }


    public function company()
    {
        return $this->belongsTo(DataCompanies::class, 'company_id', 'company_id');
    }

    public function region()
    {
        return $this->hasOne(DirectoryCountriesRegion::class, 'region_id', 'region_id');
    }

    public function district()
    {
        return $this->hasOne(DirectoryCountriesRegionsDistrict::class, 'district_id', 'district_id');
    }

    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function companyLocationCreate($request): void
    {
        $this->rules($request);
        $this->fill($request->all());
        $this->save();
    }

    /**
     * Обновить запись
     * @param $request
     *
     * @return void
     */
    public function companyLocationUpdate($request): void
    {
        // валидация
        $this->rules($request);
        // получаем массив полей и значений и з формы
        $data = $request->all();
        if (!isset($data[$this->primaryKey])) return;
        // получаем id
        $id = $data[$this->primaryKey];
        // готовим сущность для обновления
        $modules_data = $this->find($id);
        // обновляем запись
        $modules_data->update($data);
    }

    /**
     * Валидация входных данных
     * @param $request
     *
     * @return void
     */
    private function rules($request): void
    {
        // получаем поля со значениями
        $data = $request->all();

        // получаем значение первичного ключа
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;

        // id - Первичный ключ
        if (!is_null($id)) {
            $request->validate(
                [$this->primaryKey => 'required|exists:.' . $this->getTable() . ',' . $this->primaryKey],
                [$this->primaryKey => trans('svr-core-lang::validation.required')],
            );
        }

        // company_id - идентификатор компании
        $request->validate(
            ['company_id' => 'required|exists:.data.data_companies,company_id'],
            ['company_id' => trans('svr-core-lang::validation')],
        );

        // region_id - идентификатор области
        $request->validate(
            ['region_id' => 'required|exists:.directories.countries_regions,region_id'],
            ['region_id' => trans('svr-core-lang::validation')],
        );

        // district_id - идентификатор района
        $request->validate(
            ['district_id' => 'required|exists:.directories.countries_regions_districts,district_id'],
            ['district_id' => trans('svr-core-lang::validation')],
        );

        // company_status - Статус компании
        $request->validate(
            ['location_status' => ['required', Rule::in(SystemStatusEnum::get_option_list())]],
            ['location_status' => trans('svr-core-lang::validation')],
        );

        // company_status_horriot - Статус компании Хорриот
        $request->validate(
            ['location_status_delete' => ['required', Rule::in(SystemStatusDeleteEnum::get_option_list())]],
            ['location_status_delete' => trans('svr-core-lang::validation')],
        );
    }
}
