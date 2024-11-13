<?php

namespace Svr\Data\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;

class DataCompanies extends Model
{
    use HasFactory;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_companies';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'company_id';


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
		'company_status_delete'							=> 'active',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'company_id',								//* id компании */
		'company_base_index',						//* базовый индекс компании
		'company_guid_vetis',						//* Уникальный номер поднадзорного объекта, который есть в ВЕТИС
		'company_guid',								//* GUID СВР
		'company_name_short',						//* Название хозяйства - короткое
		'company_name_full',						//* Название хозяйства - полное
		'company_address',							//* Адрес хозяйства
		'company_inn',								//* ИНН - индивидуальный налоговый номер
		'company_kpp',								//* КПП - код причины постановки на учет
		'company_date_update_objects',				//* Дата последнего обновления поднадзорных объектов компании
		'company_status_horriot',					//* Статус первоначального нахождения данных о хозяйстве в хорриот
		'company_status',							//* Статус записи (enabled - активна/disabled - не активна)
		'company_status_delete',					//* Статус псевдо-удаленности записи (active - не удалена/deleted - удалена)
		'created_at',								//* Дата и время создания
		'updated_at',								//* дата последнего изменения строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'company_id'
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
//			'company_created_at'					=> 'timestamp',
		];
	}

    public function objects()
    {
        return $this->hasMany(DataCompaniesObjects::class, 'company_id');
    }

    public function locations()
    {
        return $this->hasMany(DataCompaniesLocations::class, 'company_id');
    }

    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function companyCreate($request): void
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
    public function companyUpdate($request): void
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

        // company_base_index - Базовый индекс
        $request->validate(
            ['company_base_index' => 'string|max:7'],
            ['company_base_index' => trans('svr-core-lang::validation')],
        );

        // company_guid_vetis - Гуид в Хорриоте
        $request->validate(
            ['company_guid_vetis' => 'string|max:128'],
            ['company_guid_vetis' => trans('svr-core-lang::validation')],
        );

        // company_guid - Гуид СВР
        $request->validate(
            ['company_guid' => 'string|max:36'],
            ['company_guid' => trans('svr-core-lang::validation')],
        );

        // company_name_short - Короткое название организации
        $request->validate(
            ['company_name_short' => 'string|max:100'],
            ['company_name_short' => trans('svr-core-lang::validation')],
        );

        // company_name_full - Полное название организации
        $request->validate(
            ['company_name_full' => 'string|max:255'],
            ['company_name_full' => trans('svr-core-lang::validation')],
        );

        // company_address - Адрес
        $request->validate(
            ['company_address' => 'string|max:255'],
            ['company_address' => trans('svr-core-lang::validation')],
        );

        // company_inn - ИНН
        $request->validate(
            ['company_inn' => 'string|max:12'],
            ['company_inn' => trans('svr-core-lang::validation')],
        );

        // company_kpp - КПП
        $request->validate(
            ['company_kpp' => 'string|max:12'],
            ['company_kpp' => trans('svr-core-lang::validation')],
        );

        // company_status - Статус компании
        $request->validate(
            ['company_status' => ['required', Rule::in(SystemStatusEnum::get_option_list())]],
            ['company_status' => trans('svr-core-lang::validation')],
        );

        // company_status_horriot - Статус компании Хорриот
        $request->validate(
            ['company_status_horriot' => ['required', Rule::in(SystemStatusEnum::get_option_list())]],
            ['company_status_horriot' => trans('svr-core-lang::validation')],
        );

        // company_status_delete - Статус удаления компании
        $request->validate(
            ['company_status_delete' => ['required', Rule::in(SystemStatusDeleteEnum::get_option_list())]],
            ['company_status_delete' => trans('svr-core-lang::validation')],
        );
    }
}
