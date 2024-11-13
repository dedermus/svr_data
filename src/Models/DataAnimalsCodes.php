<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Svr\Directories\Models\DirectoryMarkTypes;

class DataAnimalsCodes extends Model
{
    use HasFactory;


	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'data.data_animals_codes';


	/**
	 * Первичный ключ таблицы (автоинкремент)
	 * @var string
	 */
	protected $primaryKey							= 'code_id';


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
		'code_id',									//* ID кода (автоинкремент) */
		'animal_id',								//* ID животного */
		'code_type_id',								//* вид номера */
		'code_value',								//* значение */
		'code_description',							//* Описание */
		'code_status_id',							//* вид маркировки животного */
		'code_tool_type_id',						//* тип средства маркировки животного */
		'code_tool_location_id',					//* id места нанесения маркировки животного */
		'code_tool_date_set',						//* дата нанесения маркировки животного */
		'code_tool_date_out',						//* дата выбытия маркировки животного */
		'code_tool_photo',							//* фото средства маркирования */
		'code_status_delete',						//* статус удаления
		'created_at',							//* дата создания в СВР
        'updated_at',								//* дата последнего изменения строки записи */
	];


	/**
	 * Поля, которые нельзя менять сразу массивом
	 * @var array
	 */
	protected $guarded								= [
		'code_id',
		'animal_id',
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
//			'code_created_at'						=> 'timestamp',
		];
	}

	public function mark_type()
	{
		return $this->hasMany(DirectoryMarkTypes::class, 'mark_type_id', 'code_type_id');
	}
}
