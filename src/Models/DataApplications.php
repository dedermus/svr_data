<?php

namespace Svr\Data\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\ApplicationStatusEnum;
use Svr\Core\Models\SystemUsers;

class DataApplications extends Model
{
    use HasFactory;


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
}
