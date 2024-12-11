<?php

namespace Svr\Data\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Svr\Core\Enums\AnimalRegisterStatusEnum;
use Svr\Core\Enums\ApplicationAnimalStatusEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Exceptions\CustomException;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataAnimalsCodes;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataCompaniesObjects;
use Svr\Data\Resources\SvrApiAnimalsDataDictionaryResource;
use Svr\Data\Resources\SvrApiAnimalsDataResource;
use Svr\Data\Resources\SvrApiAnimalsListMarkResource;
use Svr\Data\Resources\SvrApiAnimalsListResource;
use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryAnimalsSpecies;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;
use Svr\Directories\Models\DirectoryKeepingPurposes;
use Svr\Directories\Models\DirectoryKeepingTypes;
use Svr\Directories\Models\DirectoryMarkStatuses;
use Svr\Directories\Models\DirectoryMarkToolTypes;
use Svr\Directories\Models\DirectoryMarkTypes;
use Svr\Directories\Models\DirectoryToolsLocations;

class ApiAnimalsController extends Controller
{
    /**
     * Информация по животному
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws Exception
     */
    public function animalsData(Request $request): SvrApiResponseResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'animal_id' => ['required', 'integer', Rule::exists('Svr\Data\Models\DataAnimals', 'animal_id')],
            'application_id' => ['array'],
            'data_sections' => ['array', Rule::in(['main','gen','base','mark','genealogy','vib','registration','history'])]
        ],
        [
            'animal_id' => trans('svr-core-lang::validation'),
            'application_id' => trans('svr-core-lang::validation'),
            'data_sections' => trans('svr-core-lang::validation'),
        ]);

        $valid_data = $validator->validated();

        //Если забыли передать секцию, назначаем по умолчанию
        if (!isset($valid_data['data_sections'])) {
            $valid_data['data_sections'] = ['main'];
        }

        //Получаем данные по животному
        $animal_data = DataAnimals::animalData($valid_data['animal_id'], $valid_data['application_id'] ?? false);

        //Если животное не нашлось - выкидываем эксепшон
        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }

        //если запросили секцию с метками животного - запрашиваем по ним данные
        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animalMarkData($valid_data['animal_id']);

        //получаем информацию по текущему пользователю
        $user = auth()->user();

        //складываем все в коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'animal_data' => $animal_data,
            'mark_data' => $mark_data,
            'data_sections' => $valid_data['data_sections'],
            'list_directories' => DataAnimals::getDirectoriesForAnimalData($animal_data, $mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsDataResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
        ]);

        //отдаем ресурс с ответом
        return new SvrApiResponseResource($data);
    }

    /**
     * Список животных
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws ValidationException|CustomException
     */
    public function animalsList(Request $request): SvrApiResponseResource|JsonResponse
    {
        $validator = Validator::make($request->all(),
        [
            'search' 				                        => ['string', 'max:255'],
            'data_sections' 		                        => ['array', Rule::in(['main','gen','base','mark','genealogy','vib','registration','history','animals_id'])],
            'company_location_id' 	                        => ['int', Rule::exists(DataCompaniesLocations::class, 'company_location_id')],
            'company_region_id'		                        => ['int', Rule::exists(DirectoryCountriesRegion::class, 'region_id')],
            'company_district_id'	                        => ['int', Rule::exists(DirectoryCountriesRegionsDistrict::class, 'district_id')],
            'filter'                                        => ['array'],
            'filter.register_status'                        => ['string', Rule::in(AnimalRegisterStatusEnum::get_option_list())],
            'filter.animal_sex'                             => ['string', Rule::in(['male','female','MALE','FEMALE'])],
            'filter.specie_id'                              => ['int', Rule::exists(DirectoryAnimalsSpecies::class, 'specie_id')],
            'filter.animal_date_birth_min' 	                => ['date'],
            'filter.animal_date_birth_max' 	                => ['date'],
            'filter.breeds_id'                              => ['int', Rule::exists(DirectoryAnimalsBreeds::class, 'breed_id')],
            'filter.application_id'                         => ['int', Rule::exists(DataApplications::class, 'application_id')],
            'filter.animal_status'                          => ['string', Rule::in(SystemStatusEnum::get_option_list())],
            'filter.animal_date_create_record_svr_min' 	    => ['date'],
            'filter.animal_date_create_record_svr_max' 	    => ['date'],
            'filter.animal_date_create_record_herriot_min'  => ['date'],
            'filter.animal_date_create_record_herriot_max'  => ['date'],
            'filter.application_animal_status'              => ['string', Rule::in(ApplicationAnimalStatusEnum::get_option_list())],
            'filter.search_inv'                             => ['string', 'max:20'],
            'filter.search_unsm'                            => ['string', 'max:11'],
            'filter.search_horriot_number'                  => ['string', 'max:14'],
        ],
        [
            'search' => trans('svr-core-lang::validation'),
            'data_sections' => trans('svr-core-lang::validation'),
            'company_location_id' => trans('svr-core-lang::validation'),
            'company_region_id' => trans('svr-core-lang::validation'),
            'company_district_id' => trans('svr-core-lang::validation'),
            'filter' => trans('svr-core-lang::validation'),
            'filter.register_status' => trans('svr-core-lang::validation'),
            'filter.animal_sex' => trans('svr-core-lang::validation'),
            'filter.specie_id' => trans('svr-core-lang::validation'),
            'filter.animal_date_birth_min' => trans('svr-core-lang::validation'),
            'filter.animal_date_birth_max' => trans('svr-core-lang::validation'),
            'filter.breeds_id' => trans('svr-core-lang::validation'),
            'filter.application_id' => trans('svr-core-lang::validation'),
            'filter.animal_status' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_svr_min' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_svr_max' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_herriot_min' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_herriot_max' => trans('svr-core-lang::validation'),
            'filter.application_animal_status' => trans('svr-core-lang::validation'),
            'filter.search_inv' => trans('svr-core-lang::validation'),
            'filter.search_unsm' => trans('svr-core-lang::validation'),
            'filter.search_horriot_number' => trans('svr-core-lang::validation'),
        ]);

        $valid_data = $validator->validated();
        if (!isset($valid_data['filter'])) $valid_data['filter'] = [];

        $user = auth()->user();

        //получаем список животных по желаемым фильтрам с пагинацией и количество животных
        $animals_list = DataAnimals::animalsList(Config::get('per_page'), Config::get('cur_page'), false, $valid_data['filter'], $valid_data);

        //если список пустой - выкидываем эксепшон
        if ($animals_list === false)
        {
            throw new CustomException('Животные не найдены', 200);
        }

        //если забыли передать секции - ставим по умолчанию
        if (!isset($valid_data['data_sections'])) {
            $valid_data['data_sections'] = ['main'];
        }

        //если запросили секцию с метками животных, запрашиваем данные о метках
        $all_mark_data = [];
        if (in_array('mark', $valid_data['data_sections']))
        {
            foreach ($animals_list as &$animal)
            {
                $mark_data = DataAnimalsCodes::animalMarkData($animal['animal_id']);
                $animal['mark_data'] = $mark_data;
                $all_mark_data[] = $mark_data;
            }
        }

        //собираем коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'animals_list' => $animals_list,
            'data_sections' => $valid_data['data_sections'],
            'list_directories' => DataAnimals::getDirectoriesForAnimalsList($animals_list, $all_mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
        ]);

        //возвращаем ресурс
        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование маркирования животного
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws ValidationException|CustomException
     */
    public function animalsMarkEdit(Request $request): SvrApiResponseResource|JsonResponse
    {
        $validator = Validator::make($request->all(),
        [
            'mark_id' 				=> ['required', 'int', Rule::exists(DataAnimalsCodes::class, 'code_id')],
            'mark_status'			=> ['required', 'int', Rule::exists(DirectoryMarkStatuses::class, 'mark_status_id')],
            'mark_tool_type' 		=> ['required', 'int', Rule::exists(DirectoryMarkToolTypes::class, 'mark_tool_type_id')],
            'mark_tool_location'	=> ['required', 'int', Rule::exists(DirectoryToolsLocations::class, 'tool_location_id')],
            'description'			=> ['required', 'string', 'max:255'],
            'mark_date_set' 		=> ['required', 'date'],
            'mark_date_out' 		=> ['date'],
        ],
        [
            'mark_id'               => trans('svr-core-lang::validation'),
            'mark_status'           => trans('svr-core-lang::validation'),
            'mark_tool_type'        => trans('svr-core-lang::validation'),
            'mark_tool_location'    => trans('svr-core-lang::validation'),
            'description'           => trans('svr-core-lang::validation'),
            'mark_date_set'         => trans('svr-core-lang::validation'),
            'mark_date_out'         => trans('svr-core-lang::validation'),
        ]);

        $valid_data = $validator->validated();

        //получаем данные о маркировании по id
        $mark_data = DataAnimalsCodes::markData($valid_data['mark_id']);
        //получаем животное по id
        $animal_data = DataAnimals::animalData($mark_data['animal_id']);
        //если животное не нашлось - выкидываем эксепшон
        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }
        //собираем данные для обновления
        $data_for_update = [
            'code_description'		=> $valid_data['description'],
            'code_status_id'		=> $valid_data['mark_status'],
            'code_tool_type_id'		=> $valid_data['mark_tool_type'],
            'code_tool_location_id'	=> $valid_data['mark_tool_location'],
            'code_tool_date_set'	=> date('Y-m-d', strtotime($valid_data['mark_date_set']))
        ];
        //если передали дату выбытия маркирования - приводим ее к нормальному виду
        if (isset($valid_data['mark_date_out']) && strlen((string)$valid_data['mark_date_out']) > 0)
        {
            $data_for_update['code_tool_date_out'] = date('Y-m-d', strtotime($valid_data['mark_date_out']));
        }
        //получаем данные маркирования и обновляем его
        $new_mark_data = DataAnimalsCodes::find($valid_data['mark_id']);
        if ($new_mark_data) {
            $new_mark_data->update($data_for_update);
        }
        //получаем обновленные данные для вывода ответа
        $new_mark_data = DataAnimalsCodes::markData($valid_data['mark_id'])->toArray();

        //получаем инфо о текущем юзере
        $user = auth()->user();
        //формируем коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'mark_data' => [$new_mark_data],
            'animal_id' => $new_mark_data['animal_id'],
            'list_directories' => DataAnimalsCodes::getDirectories($new_mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListMarkResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
        ]);
        //передаем все в ресурс для ответа
        return new SvrApiResponseResource($data);
    }

    /**
     * Групповое редактирование маркирования животного
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws ValidationException
     */
    public function animalsMarkEditGroup(Request $request): SvrApiResponseResource|JsonResponse
    {
        $validator = Validator::make($request->all(),
        [
            'search' 				                        => ['string', 'max:255'],
            'company_location_id' 	                        => ['int', Rule::exists(DataCompaniesLocations::class, 'company_location_id')],
            'company_region_id'		                        => ['int', Rule::exists(DirectoryCountriesRegion::class, 'region_id')],
            'company_district_id'	                        => ['int', Rule::exists(DirectoryCountriesRegionsDistrict::class, 'district_id')],
            'animal_id' 			                        => ['array'],
            'updates' 				                        => ['required', 'array'],
            'filter'                                        => ['array'],
            'filter.register_status'                        => ['string', Rule::in(AnimalRegisterStatusEnum::get_option_list())],
            'filter.animal_sex'                             => ['string', Rule::in(['male','female','MALE','FEMALE'])],
            'filter.specie_id'                              => ['int', Rule::exists(DirectoryAnimalsSpecies::class, 'specie_id')],
            'filter.animal_date_birth_min' 	                => ['date'],
            'filter.animal_date_birth_max' 	                => ['date'],
            'filter.breeds_id'                              => ['int', Rule::exists(DirectoryAnimalsBreeds::class, 'breed_id')],
            'filter.application_id'                         => ['int', Rule::exists(DataApplications::class, 'application_id')],
            'filter.animal_status'                          => ['string', Rule::in(SystemStatusEnum::get_option_list())],
            'filter.animal_date_create_record_svr_min' 	    => ['date'],
            'filter.animal_date_create_record_svr_max' 	    => ['date'],
            'filter.animal_date_create_record_herriot_min'  => ['date'],
            'filter.animal_date_create_record_herriot_max'  => ['date'],
            'filter.application_animal_status'              => ['string', Rule::in(ApplicationAnimalStatusEnum::get_option_list())],
            'filter.search_inv'                             => ['string', 'max:20'],
            'filter.search_unsm'                            => ['string', 'max:11'],
            'filter.search_horriot_number'                  => ['string', 'max:14'],
        ],
        [
            'search' => trans('svr-core-lang::validation'),
            'data_sections' => trans('svr-core-lang::validation'),
            'company_location_id' => trans('svr-core-lang::validation'),
            'company_region_id' => trans('svr-core-lang::validation'),
            'company_district_id' => trans('svr-core-lang::validation'),
            'filter' => trans('svr-core-lang::validation'),
            'filter.register_status' => trans('svr-core-lang::validation'),
            'filter.animal_sex' => trans('svr-core-lang::validation'),
            'filter.specie_id' => trans('svr-core-lang::validation'),
            'filter.animal_date_birth_min' => trans('svr-core-lang::validation'),
            'filter.animal_date_birth_max' => trans('svr-core-lang::validation'),
            'filter.breeds_id' => trans('svr-core-lang::validation'),
            'filter.application_id' => trans('svr-core-lang::validation'),
            'filter.animal_status' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_svr_min' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_svr_max' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_herriot_min' => trans('svr-core-lang::validation'),
            'filter.animal_date_create_record_herriot_max' => trans('svr-core-lang::validation'),
            'filter.application_animal_status' => trans('svr-core-lang::validation'),
            'filter.search_inv' => trans('svr-core-lang::validation'),
            'filter.search_unsm' => trans('svr-core-lang::validation'),
            'filter.search_horriot_number' => trans('svr-core-lang::validation'),
        ]);

        $valid_data = $validator->validated();

        if (!isset($valid_data['filter'])) $valid_data['filter'] = [];

        //Если пришли фильтры, то находим по ним список айдишников животных, если не пришли, то айдишники животных уже есть в запросе
        if((isset($valid_data['animal_id']) && count($valid_data['animal_id']) == 0) || !isset($valid_data['animal_id']))
        {
            $dataAnimalsModel = new DataAnimals();
            $animals_list = $dataAnimalsModel->animalsList(9999999, 1, false, $valid_data['filter'], $valid_data);

            if($animals_list && count($animals_list) > 0)
            {
                $valid_data['animal_id']			= array_column($animals_list, 'animal_id');
            }
        }

        //если у нас есть список айдишников животных и данные, которые нужно обновить - готовим данные для обновления
        if(isset($valid_data['animal_id']) && count($valid_data['animal_id']) > 0 && isset($valid_data['updates']) && count($valid_data['updates']) > 0)
        {
            foreach($valid_data['updates'] as $item)
            {
                //будущий массив данных для обновления
                $data_for_update = [];

                //если не указан вид маркирования (чип, тату, рсхн), то пропускаем
                if (isset($item['mark_type_id']))
                {
                    $mark_type = DirectoryMarkTypes::find($item['mark_type_id']);

                    if ($mark_type)
                    {
                        $code_type_id = $item['mark_type_id'];
                    }else {
                        continue;
                    }
                }else {
                    continue;
                }
                //проверяем есть ли у нас такой статус маркирования
                if (isset($item['mark_status']))
                {
                    $mark_status = DirectoryMarkStatuses::find($item['mark_status']);
                    if ($mark_status)
                    {
                        $data_for_update['code_status_id'] = $item['mark_status'];
                    }
                }
                //проверяем есть ли у нас такое средство маркирования
                if (isset($item['mark_tool_type']))
                {
                    $mark_tool_type = DirectoryMarkToolTypes::find($item['mark_tool_type']);
                    if ($mark_tool_type)
                    {
                        $data_for_update['code_tool_type_id'] = $item['mark_tool_type'];
                    }
                }
                //проверяем есть ли у нас такое место нанесения маркирования
                if (isset($item['mark_tool_location']))
                {
                    $mark_tool_location = DirectoryToolsLocations::find($item['mark_tool_location']);
                    if ($mark_tool_location)
                    {
                        $data_for_update['code_tool_location_id'] = $item['mark_tool_location'];
                    }
                }
                //проверяем есть ли у нас описание
                if (isset($item['description']))
                {
                    $data_for_update['code_description'] = $item['description'];
                }
                //если массив данных для обновления сформировался - обновляем животных
                if (count($data_for_update) > 0)
                {
                    DataAnimalsCodes::updateMarkGroup($data_for_update, $code_type_id, $valid_data['animal_id']);
                }
            }
        }
        //получаем инфу по пользователю
        $user = auth()->user();
        //собираем коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Данные успешно обновлены',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
        ]);
        //передаем все ресурсу ответа
        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирования фотографии средства маркирования животного
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws ValidationException
    */
    public function animalsMarkPhotoEdit(Request $request): SvrApiResponseResource|JsonResponse
    {
        $validator = Validator::make($request->all(),
            [
                'mark_id' 				=> ['required', 'int', Rule::exists(DataAnimalsCodes::class, 'code_id')],
                'mark_photo'            => ['required', 'file', 'nullable', 'mimes:jpeg,jpg,png,gif', 'max:10000'],
            ],
            [
                'mark_id'               => trans('svr-core-lang::validation'),
                'mark_photo'            => trans('svr-core-lang::validation'),
            ]);

        $valid_data = $validator->validated();

        $mark_model = new DataAnimalsCodes();
        //Обновляем запись в базе (и внутри метода addFileMarkPhoto делаем необходимые действия с файлами)
        DataAnimalsCodes::where('code_id', $valid_data['mark_id'])
            ->update(['code_tool_photo' => $mark_model->addFileMarkPhoto($request)]);
        //получаем обновленную информацию по маркированию
        $new_mark_data = DataAnimalsCodes::markData($valid_data['mark_id'])->toArray();

        //получаем данные о пользователе
        $user = auth()->user();
        //собираем коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'mark_data' => [$new_mark_data],
            'animal_id' => $new_mark_data['animal_id'],
            'list_directories' => DataAnimalsCodes::getDirectories($new_mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListMarkResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
        ]);
        //передаем данные в ресурс
        return new SvrApiResponseResource($data);
    }

    /**
     * Удаление фотографии средства маркирования животного
     * @param Request $request
     * @param $mark_id
     * @return JsonResponse|SvrApiResponseResource
     * @throws ValidationException
     */
    public function animalsMarkPhotoDelete(Request $request, $mark_id): SvrApiResponseResource|JsonResponse
    {
        $request->merge(['mark_id' => $mark_id]);
        $validator = Validator::make($request->all(),
            [
                'mark_id' 				=> ['required', 'int', Rule::exists(DataAnimalsCodes::class, 'code_id')]
            ],
            [
                'mark_id'               => trans('svr-core-lang::validation')
            ]);

        $valid_data = $validator->validated();

        $mark_model = new DataAnimalsCodes();

        //физически удаляем фото
        $mark_model->deleteMarkPhoto($request);
        //обнуляем запись в базе
        DataAnimalsCodes::where('code_id', $valid_data['mark_id'])
            ->update(['code_tool_photo' => '']);
        //получаем обновленные данные маркирования
        $new_mark_data = DataAnimalsCodes::markData($valid_data['mark_id'])->toArray();

        //получаем информацию по текущему пользователю
        $user = auth()->user();
        //формируем коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'mark_data' => [$new_mark_data],
            'animal_id' => $new_mark_data['animal_id'],
            'list_directories' => DataAnimalsCodes::getDirectories($new_mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListMarkResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
        ]);
        //передаем все в ресурс для формирования ответа
        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование объекта содержания животного
     * @param Request $request
     * @return SvrApiResponseResource
     * @throws CustomException
     * @throws ValidationException
     */
    public function animalsKeepingObjectEdit(Request $request): SvrApiResponseResource
    {
        $validator = Validator::make($request->all(),
            [
                'animal_id' 			=> ['required', 'int', Rule::exists(DataAnimals::class, 'animal_id')],
                'company_object_id'     => ['required', 'int', Rule::exists(DataCompaniesObjects::class, 'company_object_id')],
            ],
            [
                'animal_id'             => trans('svr-core-lang::validation'),
                'company_object_id'     => trans('svr-core-lang::validation'),
            ]);

        $valid_data = $validator->validated();

        $animal_data = DataAnimals::animalData($valid_data['animal_id']);
        //если животное не найдено - выкидываем эксепшон
        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }
        //устанавливаем объект содержания животному
        DataAnimals::setAnimalKeepingCompanyObject($valid_data['animal_id'], $valid_data['company_object_id']);
        //получаем информацию о пользователе
        $user = auth()->user();
        //собираем коллекцию для ответа
        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Поднадзорный объект успешно установлен',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
        ]);
        //передаем данные в ресурс для формирования ответа
        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование объекта рождения животного
     * @param Request $request
     * @return SvrApiResponseResource
     * @throws CustomException
     * @throws ValidationException
     */
    public function animalsBirthObjectEdit(Request $request): SvrApiResponseResource
    {
        $validator = Validator::make($request->all(),
            [
                'animal_id' 			=> ['required', 'int', Rule::exists(DataAnimals::class, 'animal_id')],
                'company_object_id'     => ['required', 'int', Rule::exists(DataCompaniesObjects::class, 'company_object_id')],
            ],
            [
                'animal_id'             => trans('svr-core-lang::validation'),
                'company_object_id'     => trans('svr-core-lang::validation'),
            ]);

        $valid_data = $validator->validated();

        $animal_data = DataAnimals::animalData($valid_data['animal_id']);
        //если животное не найдено - выкидываем эксепшон
        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }
        //устанавливаем объект рождения животному
        DataAnimals::setAnimalBirthCompanyObject($valid_data['animal_id'], $valid_data['company_object_id']);
        //получаем информацию о пользователе
        $user = auth()->user();
        //собираем коллекцию для ответа
        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Поднадзорный объект успешно установлен',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
        ]);
        //передаем данные в ресурс для формирования ответа
        return new SvrApiResponseResource($data);
    }

    /**
     * Групповое редактирование места рождения, содержания, типа и цели содержания группы грязных животных
     * @param Request $request
     * @return SvrApiResponseResource
     * @throws ValidationException
     */
    public function animalsObjectEditGroup(Request $request): SvrApiResponseResource
    {
        $validator = Validator::make($request->all(),
            [
                'search' 				                        => ['string', 'max:255'],
                'company_location_id' 	                        => ['int', Rule::exists(DataCompaniesLocations::class, 'company_location_id')],
                'company_region_id'		                        => ['int', Rule::exists(DirectoryCountriesRegion::class, 'region_id')],
                'company_district_id'	                        => ['int', Rule::exists(DirectoryCountriesRegionsDistrict::class, 'district_id')],
                'animal_id' 			                        => ['array'],
                'keeping_object' 			                    => ['int'],
                'birth_object' 			                        => ['int'],
                'keeping_type' 			                        => ['int'],
                'keeping_purpose' 			                    => ['int'],
                'filter'                                        => ['array'],
                'filter.register_status'                        => ['string', Rule::in(AnimalRegisterStatusEnum::get_option_list())],
                'filter.animal_sex'                             => ['string', Rule::in(['male','female','MALE','FEMALE'])],
                'filter.specie_id'                              => ['int', Rule::exists(DirectoryAnimalsSpecies::class, 'specie_id')],
                'filter.animal_date_birth_min' 	                => ['date'],
                'filter.animal_date_birth_max' 	                => ['date'],
                'filter.breeds_id'                              => ['int', Rule::exists(DirectoryAnimalsBreeds::class, 'breed_id')],
                'filter.application_id'                         => ['int', Rule::exists(DataApplications::class, 'application_id')],
                'filter.animal_status'                          => ['string', Rule::in(SystemStatusEnum::get_option_list())],
                'filter.animal_date_create_record_svr_min' 	    => ['date'],
                'filter.animal_date_create_record_svr_max' 	    => ['date'],
                'filter.animal_date_create_record_herriot_min'  => ['date'],
                'filter.animal_date_create_record_herriot_max'  => ['date'],
                'filter.application_animal_status'              => ['string', Rule::in(ApplicationAnimalStatusEnum::get_option_list())],
                'filter.search_inv'                             => ['string', 'max:20'],
                'filter.search_unsm'                            => ['string', 'max:11'],
                'filter.search_horriot_number'                  => ['string', 'max:14'],
            ],
            [
                'search' => trans('svr-core-lang::validation'),
                'company_location_id' => trans('svr-core-lang::validation'),
                'company_region_id' => trans('svr-core-lang::validation'),
                'company_district_id' => trans('svr-core-lang::validation'),
                'animal_id' => trans('svr-core-lang::validation'),
                'keeping_object' => trans('svr-core-lang::validation'),
                'birth_object' => trans('svr-core-lang::validation'),
                'keeping_type' => trans('svr-core-lang::validation'),
                'keeping_purpose' => trans('svr-core-lang::validation'),
                'filter' => trans('svr-core-lang::validation'),
                'filter.register_status' => trans('svr-core-lang::validation'),
                'filter.animal_sex' => trans('svr-core-lang::validation'),
                'filter.specie_id' => trans('svr-core-lang::validation'),
                'filter.animal_date_birth_min' => trans('svr-core-lang::validation'),
                'filter.animal_date_birth_max' => trans('svr-core-lang::validation'),
                'filter.breeds_id' => trans('svr-core-lang::validation'),
                'filter.application_id' => trans('svr-core-lang::validation'),
                'filter.animal_status' => trans('svr-core-lang::validation'),
                'filter.animal_date_create_record_svr_min' => trans('svr-core-lang::validation'),
                'filter.animal_date_create_record_svr_max' => trans('svr-core-lang::validation'),
                'filter.animal_date_create_record_herriot_min' => trans('svr-core-lang::validation'),
                'filter.animal_date_create_record_herriot_max' => trans('svr-core-lang::validation'),
                'filter.application_animal_status' => trans('svr-core-lang::validation'),
                'filter.search_inv' => trans('svr-core-lang::validation'),
                'filter.search_unsm' => trans('svr-core-lang::validation'),
                'filter.search_horriot_number' => trans('svr-core-lang::validation'),
            ]);

        $valid_data = $validator->validated();

        if (!isset($valid_data['filter'])) $valid_data['filter'] = [];

        //Если пришли фильтры, то находим по ним список айдишников животных, если не пришли, то айдишники животных уже есть в запросе
        if((isset($valid_data['animal_id']) && count($valid_data['animal_id']) == 0) || !isset($valid_data['animal_id']))
        {
            $dataAnimalsModel = new DataAnimals();
            $animals_list = $dataAnimalsModel->animalsList(9999999, 1, false, $valid_data['filter'], $valid_data);

            if($animals_list && count($animals_list) > 0)
            {
                $valid_data['animal_id']			= array_column($animals_list, 'animal_id');
            }
        }
        //если у нас есть список айдишников животных - продолжаем
        if(isset($valid_data['animal_id']) && count($valid_data['animal_id']) > 0)
        {
            // готовим данные для обновления в зависимости от того, что именно пришло
            $data_for_update = [];
            //объект содержания
            if (isset($valid_data['keeping_object']) && $valid_data['keeping_object'] > 0)
            {
                $company_object_data = DataCompaniesObjects::find($valid_data['keeping_object']);

                if ($company_object_data)
                {
                    $data_for_update['animal_object_of_keeping_id'] = $valid_data['keeping_object'];
                }
            }
            //объект рождения
            if (isset($valid_data['birth_object']) && $valid_data['birth_object'] > 0)
            {
                $company_object_data = DataCompaniesObjects::find($valid_data['birth_object']);

                if ($company_object_data)
                {
                    $data_for_update['animal_object_of_birth_id'] = $valid_data['birth_object'];
                }
            }
            //тип содержания
            if (isset($valid_data['keeping_type']) && $valid_data['keeping_type'] > 0)
            {
                $keeping_types_list = DirectoryKeepingTypes::find($valid_data['keeping_type']);

                if ($keeping_types_list)
                {
                    $data_for_update['animal_type_of_keeping_id'] = $valid_data['keeping_type'];
                }
            }
            //причина содержания
            if (isset($valid_data['keeping_purpose']) && $valid_data['keeping_purpose'] > 0)
            {
                $keeping_types_list = DirectoryKeepingPurposes::find($valid_data['keeping_purpose']);

                if ($keeping_types_list)
                {
                    $data_for_update['animal_purpose_of_keeping_id'] = $valid_data['keeping_purpose'];
                }
            }
            //если массив данных для обновления сформировался - обновляем животных
            if (count($data_for_update) > 0)
            {
                DataAnimals::updateAnimalsGroup($data_for_update, $valid_data['animal_id']);
            }
        }
        //получаем инфу по пользователю
        $user = auth()->user();
        //собираем коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Данные успешно сохранены',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
        ]);
        //передаем все ресурсу ответа
        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование вида содержания животного
     * @param Request $request
     * @return SvrApiResponseResource
     * @throws CustomException
     * @throws ValidationException
     */
    public function animalKeepingTypeEdit(Request $request): SvrApiResponseResource
    {
        $validator = Validator::make($request->all(),
            [
                'animal_id' 			=> ['required', 'int', Rule::exists(DataAnimals::class, 'animal_id')],
                'keeping_type'          => ['required', 'int', Rule::exists(DirectoryKeepingTypes::class, 'keeping_type_id')],
            ],
            [
                'animal_id'             => trans('svr-core-lang::validation'),
                'keeping_type'          => trans('svr-core-lang::validation'),
            ]);

        $valid_data = $validator->validated();

        $animal_data = DataAnimals::animalData($valid_data['animal_id']);
        //если животное не нашлось - выкидываем эксепшон
        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }
        //устанавливаем вид содержания животному
        DataAnimals::setAnimalKeepingType($valid_data['animal_id'], $valid_data['keeping_type']);
        //получаем новые данные животного
        $animal_data = DataAnimals::animalData($valid_data['animal_id']);
        //заполняем секции для виджетов
        $valid_data['data_sections'] = ['main','gen','base','mark','genealogy','vib','registration','history'];
        //получаем данные средств маркирования
        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animalMarkData($valid_data['animal_id']);
        //получаем информацию о пользователе
        $user = auth()->user();
        //собираем коллекцию для ответа
        $data = collect([
            'user_id' => $user['user_id'],
            'animal_data' => $animal_data,
            'mark_data' => $mark_data,
            'data_sections' => $valid_data['data_sections'],
            'list_directories' => DataAnimals::getDirectoriesForAnimalData($animal_data, $mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsDataResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
        ]);
        //передаем данные в ресурс для формирования ответа
        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование причины содержания животного
     * @param Request $request
     * @return SvrApiResponseResource
     * @throws CustomException
     * @throws ValidationException
     */
    public function animalKeepingPurposeEdit(Request $request): SvrApiResponseResource
    {
        $validator = Validator::make($request->all(),
            [
                'animal_id' 			=> ['required', 'int', Rule::exists(DataAnimals::class, 'animal_id')],
                'keeping_purpose'       => ['required', 'int', Rule::exists(DirectoryKeepingPurposes::class, 'keeping_purpose_id')],
            ],
            [
                'animal_id'             => trans('svr-core-lang::validation'),
                'keeping_purpose'       => trans('svr-core-lang::validation'),
            ]);

        $valid_data = $validator->validated();

        $animal_data = DataAnimals::animalData($valid_data['animal_id']);
        //если животное не найдено - выкидываем эксепшон
        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }
        //устанавливаем причину содержания животному
        DataAnimals::setAnimalKeepingPurpose($valid_data['animal_id'], $valid_data['keeping_purpose']);
        //получаем новые данные животного
        $animal_data = DataAnimals::animalData($valid_data['animal_id']);
        //заполняем секции для виджетов
        $valid_data['data_sections'] = ['main','gen','base','mark','genealogy','vib','registration','history'];
        //получаем данные средств маркирования
        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animalMarkData($valid_data['animal_id']);
        //получаем информацию о пользователе
        $user = auth()->user();
        //собираем коллекцию для ответа
        $data = collect([
            'user_id' => $user['user_id'],
            'animal_data' => $animal_data,
            'mark_data' => $mark_data,
            'data_sections' => $valid_data['data_sections'],
            'list_directories' => DataAnimals::getDirectoriesForAnimalData($animal_data, $mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsDataResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
        ]);

        return new SvrApiResponseResource($data);
    }
}
