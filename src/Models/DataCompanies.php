<?php

namespace Svr\Data\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }

    /**
     * Обновить запись
     * @param $request
     *
     * @return void
     */
    public function companyUpdate($request): void
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
            'company_base_index' => 'string|max:7',
            'company_guid_vetis' => 'string|max:128',
            'company_guid' => 'string|max:36',
            'company_name_short' => 'string|max:100',
            'company_name_full' => 'string|max:255',
            'company_address' => 'string|max:255',
            'company_inn' => 'string|max:12',
            'company_kpp' => 'string|max:12',
            'company_status' => ['required', Rule::in(SystemStatusEnum::get_option_list())],
            'company_status_horriot' => ['required', Rule::in(SystemStatusEnum::get_option_list())],
            'company_status_delete' => ['required', Rule::in(SystemStatusDeleteEnum::get_option_list())],
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
            'company_base_index.string' => trans('svr-core-lang::validation'),
            'company_guid_vetis.string' => trans('svr-core-lang::validation'),
            'company_guid.string' => trans('svr-core-lang::validation'),
            'company_name_short.string' => trans('svr-core-lang::validation'),
            'company_name_full.string' => trans('svr-core-lang::validation'),
            'company_address.string' => trans('svr-core-lang::validation'),
            'company_inn.string' => trans('svr-core-lang::validation'),
            'company_kpp.string' => trans('svr-core-lang::validation'),
            'company_status.required' => trans('svr-core-lang::validation'),
            'company_status_horriot.required' => trans('svr-core-lang::validation'),
            'company_status_delete.required' => trans('svr-core-lang::validation'),
        ];
    }
}
