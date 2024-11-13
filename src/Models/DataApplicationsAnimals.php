<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataApplicationsAnimals extends Model
{
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
		'application_animal_status'						=> 'added',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'application_animal_id',					//* ID животного в заявке */
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
	 * Преобразование полей при чтении/записи
	 * @return array
	 */
	protected function casts(): array
	{
		return [
//			'update_at'								=> 'timestamp'
		];
	}
}
