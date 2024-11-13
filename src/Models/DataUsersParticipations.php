<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataUsersParticipations extends Model
{
    use HasFactory;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_users_participations';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'participation_id';


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
		'participation_status'							=> 'enabled',
	];


	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'participation_id',							//* Инкремент
        'user_id',									//* ID пользователя в таблице SYSTEM.SYSTEM_USERS
		'participation_item_type',					//* Тип привязки (компания/регион/район)
		'participation_item_id',					//* ID привязки (company_location_id/region_id/district_id)
		'role_id',									//* ID роли в таблице SYSTEM.SYSTEM_ROLES
		'participation_status',						//* Статус связки
		'created_at',					            //* Дата и время создания
		'updated_at',								//* дата последнего изменения строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'participation_id',
	];


	/**
	 * Массив системных скрытых полей
	 * @var array
	 */
	protected $hidden								= [
		'created_at',
	];


	/**
	 * Преобразование полей при чтении/записи
	 * @return array
	 */
	protected function casts(): array
	{
		return [
//			'update_at'								=> 'timestamp',
//			'participation_created_at'				=> 'timestamp',
		];
	}
}
