<?php

namespace Svr\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Traits\GetEnums;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;
use Svr\Directories\Models\DirectoryMarkStatuses;
use Svr\Directories\Models\DirectoryMarkToolTypes;
use Svr\Directories\Models\DirectoryMarkTypes;
use Svr\Directories\Models\DirectoryToolsLocations;
use Zebra_Image;

class DataAnimalsCodes extends Model
{
    use GetTableName;
    use GetEnums;
    use HasFactory;
    use GetValidationRules;


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
		'code_status_delete'							=> SystemStatusDeleteEnum::ACTIVE->value,
	];

	/**
	 * Поля, которые можно менять сразу массивом
	 * @var array
	 */
	protected $fillable								= [
		'animal_id',								// ID животного
		'code_type_id',								// вид номера
		'code_value',								// значение
		'code_description',							// Описание
		'code_status_id',							// вид маркировки животного
		'code_tool_type_id',						// тип средства маркировки животного
		'code_tool_location_id',					// id места нанесения маркировки животного
		'code_tool_date_set',						// дата нанесения маркировки животного
		'code_tool_date_out',						// дата выбытия маркировки животного
		'code_tool_photo',							// фото средства маркирования
		'code_status_delete',						// статус удален
		'created_at',							    // дата создания в С
        'updated_at',								// дата последнего изменения строки записи
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
     * Путь до папки с фото средств маркирования
     * @var string
     */
    protected string $pathMarkPhoto = 'images/mark_photo/';

    /**
     * Расширение фото средства маркирования
     * @var string
     */
    protected string $markPhotoExp = 'jpg';

    /**
     * Диск хранения
     * @var string
     */
    protected string $diskMarkPhoto = 'local';


	/**
	 * Реляция типов средств маркирования
	 */
	public function mark_type()
	{
		return $this->hasMany(DirectoryMarkTypes::class, 'mark_type_id', 'code_type_id');
	}


    /**
     * Создать запись
     *
     * @param Request $request
     *
     */
    public function animalCodeCreate(Request $request)
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
        $animalCode = $this->find($this->getKey());
        return $animalCode->code_id;
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
            'animal_id' => 'required|int|exists:.data.data_animals,animal_id',
            'code_type_id' => 'nullable|int|exists:.directories.mark_types,mark_type_id',
            'code_value' => 'nullable|string|max:64',
            'code_description' => 'nullable|string|max:255',
            'code_status_id' => 'nullable|int|exists:.directories.mark_statuses,mark_status_id',
            'code_tool_type_id' => 'nullable|int|exists:.directories.mark_tool_types,mark_tool_type_id',
            'code_tool_location_id' => 'nullable|int|exists:.directories.tools_locations,tool_location_id',
            'code_tool_date_set' => 'nullable|date',
            'code_tool_date_out' => 'nullable|date',
            'code_tool_photo' => 'nullable|string|max:255',
            'code_status_delete' => [
                'required',
                Rule::enum(SystemStatusDeleteEnum::class)
            ],
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

    /**
     * Получаем данные о средствах маркирования животного
     * @param $animal_id
     * @return mixed[]
     */
    public static function animalMarkData($animal_id): array
    {
        return DB::table(self::getTableName() . ' AS t_animal_codes')
            ->leftJoin(DirectoryMarkTypes::getTableName() .' AS t_mark_type', 't_mark_type.mark_type_id', '=', 't_animal_codes.code_type_id')
            ->leftJoin(DirectoryMarkStatuses::getTableName() . ' AS t_mark_status', 't_mark_status.mark_status_id', '=','t_animal_codes.code_status_id')
            ->leftJoin(DirectoryMarkToolTypes::getTableName() . ' AS t_mark_tool_type', 't_mark_tool_type.mark_tool_type_id', '=','t_animal_codes.code_tool_type_id')
            ->leftJoin(DirectoryToolsLocations::getTableName() . ' AS t_mark_location', 't_mark_location.tool_location_id', '=', 't_animal_codes.code_tool_location_id')
            ->select('t_animal_codes.*',
						't_mark_type.mark_type_name',
						't_mark_type.mark_type_id',
						't_mark_type.mark_type_value_horriot',
						't_mark_status.mark_status_name',
						't_mark_status.mark_status_id',
						't_mark_status.mark_status_value_horriot',
						't_mark_tool_type.mark_tool_type_name',
						't_mark_tool_type.mark_tool_type_id',
						't_mark_tool_type.mark_tool_type_value_horriot',
						't_mark_location.tool_location_name',
						't_mark_location.tool_location_id',
						't_mark_location.tool_location_guid_horriot')
            ->where('t_animal_codes.animal_id', '=', $animal_id)
            ->get()->toArray();
    }

    /**
     * Получаем данные о средстве маркирования
     * @param $code_id
     * @return object|null
     */
    public static function markData($code_id): ?object
    {
        $mark_data = DB::table(self::getTableName() . ' AS t_animal_codes')
            ->leftJoin(DirectoryMarkTypes::getTableName() . ' AS t_mark_type', 't_mark_type.mark_type_id',  '=', 't_animal_codes.code_type_id')
            ->leftJoin(DirectoryMarkStatuses::getTableName() . ' AS t_mark_status', 't_mark_status.mark_status_id', '=' ,'t_animal_codes.code_status_id')
			->leftJoin(DirectoryMarkToolTypes::getTableName() . ' AS t_mark_tool_type', 't_mark_tool_type.mark_tool_type_id', '=', 't_animal_codes.code_tool_type_id')
			->leftJoin(DirectoryToolsLocations::getTableName() . ' AS t_mark_location', 't_mark_location.tool_location_id', '=', 't_animal_codes.code_tool_location_id')
            ->select('t_animal_codes.*',
						't_mark_type.mark_type_name',
						't_mark_type.mark_type_id',
						't_mark_status.mark_status_name',
						't_mark_status.mark_status_id',
						't_mark_tool_type.mark_tool_type_name',
						't_mark_tool_type.mark_tool_type_id',
						't_mark_location.tool_location_name',
						't_mark_location.tool_location_id')
            ->where('t_animal_codes.code_id', '=', $code_id)
            ->first();
        return collect($mark_data);
    }

    /**
     * Обновляем данные средств маркирования группе животных
     * @param $data_for_update
     * @param $code_type_id
     * @param $animals_id
     * @return int
     */
    public static function updateMarkGroup($data_for_update, $code_type_id, $animals_id): int
    {
        return DB::table(self::getTableName())
            ->where('code_type_id', '=', $code_type_id)
            ->whereIn('animal_id', $animals_id)
            ->update($data_for_update);
    }

    /**
     * Подготовка удаления фото с диска
     * @param $request
     *
     * @return bool
     */
    public function deleteMarkPhoto($request): bool
    {
        $data = $request->all();
        $id = $data['mark_id'] ?? null;
        $res = $id ? self::findOrFail($id)->toArray() : [];
        return $this->eraseMarkPhoto($res['code_tool_photo']);
    }

    /**
     * Удаление фото с диска
     * @param $photo
     *
     * @return bool
     */
    public function eraseMarkPhoto($photo): bool
    {
        if (empty(trim($photo))) return false;

        $path = $this->pathMarkPhoto .$photo.'_resized.'.$this->markPhotoExp;
        if (Storage::exists( $path)) {
            Storage::delete( $path);
        }
        return true;
    }

    /**
     * Изменяет размер изображения на указанную ширину и высоту.
     *
     * @param string $original_image_name Название исходного файла изображения.
     * @param string $new_message_name    Название измененного файла изображения.
     * @param string $image_path          Путь к файлам изображения.
     * @param int    $width               Новая ширина изображения.
     * @param int    $height              Новая высота изображения.
     */
    public function imageResize(string $original_image_name, string $new_message_name, string $image_path, int $width, int $height): bool|string
    {
        $image = new Zebra_Image();
        $image->source_path = Storage::disk($this->diskMarkPhoto)->path($this->pathMarkPhoto.$original_image_name);
        $image->target_path = Storage::disk($this->diskMarkPhoto)->path($this->pathMarkPhoto.$new_message_name);
        if (!$image->resize($width, $height, ZEBRA_IMAGE_NOT_BOXED)) {
            switch ($image->error) {
                case 1:
                    return 'Файл не существует';
                    break;
                case 2:
                    return 'Файл не является изображением';
                    break;
                case 3:
                    return 'Не удалось сохранить изображение';
                    break;
                case 4:
                    return 'Неподдерживаемый тип исходного изображения';
                    break;
                case 5:
                    return 'Неподдерживаемый тип изменяемого изображения';
                    break;
                case 6:
                    return 'Библиотека GD не поддерживает тип изображения';
                    break;
                case 7:
                    return 'Библиотека GD не установлена';
                    break;
                case 8:
                    return 'Команда "chmod" отключена в конфигурации PHP';
                    break;
                case 9:
                    return 'Функция "exif_read_data" недоступна';
                    break;
            }

            return false;
        }

        return true;
    }

    /**
     * Добавляем фото средства маркирования
     * @param $request
     * @return array|string|string[]
     */
    public function addFileMarkPhoto($request): array|string
    {
        $this->deleteMarkPhoto($request);

        $filenameWithExt = $request->file('mark_photo')->getClientOriginalName();
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $extension = $request->file('mark_photo')->getClientOriginalExtension();
        $filenamebild = $filename . "_" . time() . "." . $extension;
        $fileNameToStore = $this->pathMarkPhoto . $filenamebild;
        $request->file('mark_photo')->storeAs($fileNameToStore);

        $image_name = str_replace('.' . $extension, '_resized.'.$this->markPhotoExp, $filenamebild);

        $this->imageResize($filenamebild, $image_name, $this->pathMarkPhoto, 800, 800);

        if (Storage::exists($this->pathMarkPhoto . $filenamebild) && !is_null($filenamebild)) {
            Storage::delete($this->pathMarkPhoto . $filenamebild);
        }

        return str_replace('.' . $extension, '', $filenamebild);
    }

    /**
     * Формируем массив справочников для средств маркирования
     * @param $mark_data
     * @return array
     */
    public static function getDirectories($mark_data): array
    {
        $list_directories = [];
        $mark_types_ids = array_filter([$mark_data['mark_type_id']]);
        if (count($mark_types_ids) > 0) {
            $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
        }

        $mark_statuses_ids = array_filter([$mark_data['mark_status_id']]);
        if (count($mark_statuses_ids) > 0)
        {
            $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
        }

        $mark_tool_types_ids = array_filter([$mark_data['mark_tool_type_id']]);
        if (count($mark_tool_types_ids) > 0)
        {
            $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
        }

        $mark_tools_locations_ids = array_filter([$mark_data['tool_location_id']]);
        if (count($mark_tools_locations_ids) > 0)
        {
            $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
        }
        return $list_directories;
    }
}
