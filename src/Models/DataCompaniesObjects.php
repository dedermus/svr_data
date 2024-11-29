<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Traits\GetTableName;

class DataCompaniesObjects extends Model
{
    use GetTableName;
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
		'code_status_delete'							=> 'active',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
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
	protected $hidden								= [];


	/**
	 * Реляция хозяйства
	 */
    public function company()
    {
        return $this->hasOne(DataCompanies::class, 'company_id', 'company_id');
    }


	/**
	 * Список объектов хозяйства
	 *
	 * @param $company_id
	 * @return array
	 */
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
     * @param Request $request
     *
     * @return void
     */
    public function companyObjectCreate(Request $request): void
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }


    /**
     * Обновить запись
     * @param Request $request
     *
     * @return void
     */
    public function companyObjectUpdate(Request $request): void
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
            'company_object_guid_self' => 'required|string|max:128',
            'company_object_guid_horriot' => 'required|string|max:128',
            'company_object_approval_number' => 'required|string|max:64',
            'company_object_address_view' => 'required|string|max:512',
            'company_object_is_favorite' => 'required|boolean',
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
            'company_object_guid_self' => trans('svr-core-lang::validation'),
            'company_object_guid_horriot' => trans('svr-core-lang::validation'),
            'company_object_approval_number' => trans('svr-core-lang::validation'),
            'company_object_address_view' => trans('svr-core-lang::validation'),
            'company_object_is_favorite' => trans('svr-core-lang::validation'),
        ];
    }
}
