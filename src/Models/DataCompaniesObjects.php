<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCompaniesObjects extends Model
{
    use HasFactory;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_companies_objects';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'company_object_id';


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
		'code_status_delete'							=> 'active',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'company_object_id',						//* Инкремент
		'company_id',								//* ID компании
		'company_object_guid_self',					//* GUID объекта
		'company_object_guid_horriot',				//* GUID объекта в хорриот
		'company_object_approval_number',			//* Номер
		'company_object_address_view',				//* Адрес
		'company_object_is_favorite',				//* Избранный ПО
		'created_at',				                //* Дата создания
		'updated_at',								//* дата последнего изменения строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'company_object_id',
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
//			'company_object_created_at'				=> 'timestamp',
		];
	}

    public function company()
    {
        return $this->hasOne(DataCompanies::class, 'company_id', 'company_id');
    }

    public static function companyObjectsGetByCompanyId($company_id)
    {
        if (DataCompanies::find($company_id))
        {
            $company_objects = self::where('company_id', $company_id)->get()->pluck('company_object_id');
            return (array_values($company_objects->toArray()));
        }else {
            return [];
        }

    }

    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function companyObjectCreate($request): void
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
    public function companyObjectUpdate($request): void
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

        // company_object_guid_self - гуид СВР объекта
        $request->validate(
            ['company_object_guid_self' => 'required|string|max:128'],
            ['company_object_guid_self' => trans('svr-core-lang::validation')],
        );

        // company_object_guid_horriot - гуид Хорриот объекта
        $request->validate(
            ['company_object_guid_horriot' => 'required|string|max:128'],
            ['company_object_guid_horriot' => trans('svr-core-lang::validation')],
        );

        // company_object_approval_number - номер ПО
        $request->validate(
            ['company_object_approval_number' => 'required|string|max:64'],
            ['company_object_approval_number' => trans('svr-core-lang::validation')],
        );

        // company_object_address_view - адрес ПО
        $request->validate(
            ['company_object_address_view' => 'required|string|max:64'],
            ['company_object_address_view' => trans('svr-core-lang::validation')],
        );

        // company_object_is_favorite - флаг избранного ПО
        $request->validate(
            ['company_object_is_favorite' => 'required|string|max:5'],
            ['company_object_is_favorite' => trans('svr-core-lang::validation')],
        );
    }
}
