<?php

namespace Svr\Data\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Traits\GetTableName;

class DataCompanies extends Model
{
	use GetTableName;
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
		'company_status_delete'							=> 'active',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
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
	protected $hidden								= [];


	/**
	 * Реляция поднадзорные объекты
	 */
    public function objects()
    {
        return $this->hasMany(DataCompaniesObjects::class, 'company_id');
    }


	/**
	 * Реляция локации компаний
	 */
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
        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.'.$this->getTable(), $this->primaryKey),
            ],
            'company_base_index' => 'min:0|max:7',
            'company_guid_vetis' => 'min:0|max:2',
            'company_guid' => 'min:0|max:36',
            'company_name_short' => 'min:0|max:100',
            'company_name_full' => 'min:0|max:255',
            'company_address' => 'min:0|max:255',
            'company_inn' => 'min:0|max:12',
            'company_kpp' => 'min:0|max:12',
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
            'company_base_index' => trans('svr-core-lang::validation'),
            'company_guid_vetis' => trans('svr-core-lang::validation'),
            'company_guid' => trans('svr-core-lang::validation'),
            'company_name_short' => trans('svr-core-lang::validation'),
            'company_name_full' => trans('svr-core-lang::validation'),
            'company_address' => trans('svr-core-lang::validation'),
            'company_inn' => trans('svr-core-lang::validation'),
            'company_kpp' => trans('svr-core-lang::validation'),
            'company_status' => trans('svr-core-lang::validation'),
            'company_status_horriot' => trans('svr-core-lang::validation'),
            'company_status_delete' => trans('svr-core-lang::validation'),
        ];
    }
}
