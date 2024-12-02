<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\ApplicationAnimalStatusEnum;
use Svr\Core\Enums\HerriotErrorTypesEnum;
use Svr\Core\Traits\GetTableName;

class DataApplicationsAnimals extends Model
{
    use GetTableName;
    use HasFactory;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_applications_animals';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'application_animal_id';


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
		'application_animal_status'						=> 'added',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'application_id',							//* id заявки */
		'animal_id',								//* id животного */
		'application_animal_date_add',				//* дата добавления */
		'application_animal_date_horriot',			//* дата отправки в хорриот */
		'application_animal_date_response',			//* дата получения ответа от хорриот */
		'application_animal_status',				//* статус заявки ('added', 'deleted', 'sent', 'registered', 'rejected', 'finished') */
		'created_at',								//* дата добавления животного в заявку */
		'application_herriot_application_id',		//* ID заявки хорриота */
		'application_request_herriot',				//* данные запроса отправки на регистрацию */
		'application_response_herriot',				//* данные ответа отправки на регистрацию */
		'application_request_application_herriot',	//* данные запроса проверки статуса регистрации */
		'application_response_application_herriot',	//* данные ответа проверки статуса регистрации */
		'application_response_herriot_error_type',	//* Тип ошибки при отправке в Хорриот */
		'application_response_herriot_error_code',	//* Код ошибки при отправке в Хорриот */
		'application_response_application_herriot_error_type',	//* Тип ошибки при ответе из Хорриот */
		'application_response_application_herriot_error_code',	//* Код ошибки при ответе из Хорриот */
		'application_animal_date_last_update',		//* Дата последнего запроса к хорриоту */
		'application_animal_date_sent',				//* Дата нажатия кнопки отправки животного на регистрацию */
		'application_herriot_send_text_error',		//* Текст ошибки при отправке в Хорриот */
		'application_herriot_check_text_error',		//* Текст ошибки при проверке статуса регистрации в Хорриот */
		'updated_at',								//* дата последнего изменения строки записи */
		'created_at',								//* дата создания строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'application_animal_id',
	];


	/**
	 * Массив системных скрытых полей
	 * @var array
	 */
	protected $hidden								= [];


    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function applicationAnimalCreate($request): void
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
    public function applicationAnimalUpdate($request): void
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
            'animal_id' => 'required|int|exists:.data.data_animals,animal_id',
            'application_animal_date_add' => 'required|date',
            'application_animal_date_sent' => 'date',
            'application_animal_date_horriot' => 'date',
            'application_animal_date_response' => 'date',
            'application_animal_status' => ['required', Rule::enum(ApplicationAnimalStatusEnum::class)],
            'application_animal_date_last_update' => 'required|date',
            'application_response_herriot_error_type' => ['required', Rule::enum(HerriotErrorTypesEnum::class)],
            'application_response_herriot_error_code' => 'string|max:64',
            'application_response_application_herriot_error_type' => ['required', Rule::enum(HerriotErrorTypesEnum::class)],
            'application_response_application_herriot_error_code' => 'string|max:64',
            'application_herriot_application_id' => 'string|max:64',
            'application_herriot_send_text_error' => 'string|max:1000',
            'application_herriot_check_text_error' => 'string|max:1000',
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
            'animal_id' => trans('svr-core-lang::validation'),
            'application_animal_date_add' => trans('svr-core-lang::validation'),
            'application_animal_date_sent' => trans('svr-core-lang::validation'),
            'application_animal_date_horriot' => trans('svr-core-lang::validation'),
            'application_animal_date_response' => trans('svr-core-lang::validation'),
            'application_animal_status' => trans('svr-core-lang::validation'),
            'application_animal_date_last_update' => trans('svr-core-lang::validation'),
            'application_response_herriot_error_type' => trans('svr-core-lang::validation'),
            'application_response_herriot_error_code' => trans('svr-core-lang::validation'),
            'application_response_application_herriot_error_type' => trans('svr-core-lang::validation'),
            'application_response_application_herriot_error_code' => trans('svr-core-lang::validation'),
            'application_herriot_application_id' => trans('svr-core-lang::validation'),
            'application_herriot_send_text_error' => trans('svr-core-lang::validation'),
            'application_herriot_check_text_error' => trans('svr-core-lang::validation'),
        ];
    }
}
