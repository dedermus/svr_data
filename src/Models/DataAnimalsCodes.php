<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemStatusDeleteEnum;
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

    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function animalCodeCreate($request): void
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
    public function animalCodeUpdate($request): void
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
            'code_type_id' => 'int|exist:.directories.mark_types',
            'code_value' => 'string|max:64',
            'code_description' => 'string|max:255',
            'code_status_id' => 'int|exist:.directories.mark_statuses',
            'code_tool_type_id' => 'int|exist:.directories.mark_tool_types',
            'code_tool_location_id' => 'int|exist:.directories.tools_locations',
            'code_tool_date_set' => 'date',
            'code_tool_date_out' => 'date',
            'code_tool_photo' => 'string|max:255',
            'code_status_delete' => ['required', Rule::in(SystemStatusDeleteEnum::get_option_list())],
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
            'code_type_id' => trans('svr-core-lang::validation'),
            'code_value' => trans('svr-core-lang::validation'),
            'code_description' => trans('svr-core-lang::validation'),
            'code_status_id' => trans('svr-core-lang::validation'),
            'code_tool_type_id' => trans('svr-core-lang::validation'),
            'code_tool_location_id' => trans('svr-core-lang::validation'),
            'code_tool_date_set' => trans('svr-core-lang::validation'),
            'code_tool_date_out' => trans('svr-core-lang::validation'),
            'code_tool_photo' => trans('svr-core-lang::validation'),
            'code_status_delete' => trans('svr-core-lang::validation'),
        ];
    }
}
