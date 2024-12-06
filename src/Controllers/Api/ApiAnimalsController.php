<?php

namespace Svr\Data\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;
use Svr\Directories\Models\DirectoryGenders;
use Svr\Directories\Models\DirectoryKeepingPurposes;
use Svr\Directories\Models\DirectoryKeepingTypes;
use Svr\Directories\Models\DirectoryMarkStatuses;
use Svr\Directories\Models\DirectoryMarkToolTypes;
use Svr\Directories\Models\DirectoryMarkTypes;
use Svr\Directories\Models\DirectoryOutBasises;
use Svr\Directories\Models\DirectoryOutTypes;
use Svr\Directories\Models\DirectoryToolsLocations;

class ApiAnimalsController extends Controller
{
    /**
     * Информация по животному
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws \Exception
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

        if (!isset($valid_data['data_sections'])) {
            $valid_data['data_sections'] = ['main'];
        }

        $animal_data = DataAnimals::animal_data($valid_data['animal_id'], $valid_data['application_id'] ?? false);

        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }

        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animal_mark_data($valid_data['animal_id']);

        $user = auth()->user();

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
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Список животных
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws ValidationException
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

        if (!isset($valid_data[''])) $valid_data['filter'] = [];

        $user = auth()->user();

        $dataAnimalsModel = new DataAnimals();
        $animals_list = $dataAnimalsModel->animals_list($user['pagination_per_page'], $user['pagination_cur_page'], false, $valid_data['filter'], $valid_data);
        $animals_count = $dataAnimalsModel->animals_count;

        if ($animals_list === false)
        {
            throw new CustomException('Животные не найдено', 200);
        }

        if (!isset($valid_data['data_sections'])) {
            $valid_data['data_sections'] = ['main'];
        }

        $all_mark_data = [];

        if (in_array('mark', $valid_data['data_sections']))
        {
            foreach ($animals_list as &$animal)
            {
                $mark_data = DataAnimalsCodes::animal_mark_data($animal['animal_id']);
                $animal['mark_data'] = $mark_data;
                $all_mark_data[] = $mark_data;
            }
        }

        $data = collect([
            'user_id' => $user['user_id'],
            'animals_list' => $animals_list,
            'data_sections' => $valid_data['data_sections'],
            'list_directories' => DataAnimals::getDirectoriesForAnimalsList($animals_list, $all_mark_data),
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
            'pagination' => [
                'total_records' => $animals_count,
                'cur_page' => $user['pagination_cur_page'],
                'per_page' => $user['pagination_per_page']
            ],
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование маркирования животного
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws ValidationException
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

        $mark_data = DataAnimalsCodes::mark_data($valid_data['mark_id']);

        $animal_data = DataAnimals::animal_data($mark_data['animal_id']);

        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }

        $data_for_update = [
            'code_description'		=> $valid_data['description'],
            'code_status_id'		=> $valid_data['mark_status'],
            'code_tool_type_id'		=> $valid_data['mark_tool_type'],
            'code_tool_location_id'	=> $valid_data['mark_tool_location'],
            'code_tool_date_set'	=> date('Y-m-d', strtotime($valid_data['mark_date_set']))
        ];

        if (isset($valid_data['mark_date_out']) && strlen((string)$valid_data['mark_date_out']) > 0)
        {
            $data_for_update['code_tool_date_out'] = date('Y-m-d', strtotime($valid_data['mark_date_out']));
        }

        $new_mark_data = DataAnimalsCodes::find($valid_data['mark_id']);
        if ($new_mark_data) {
            $new_mark_data->update($data_for_update);
        }

        $new_mark_data = DataAnimalsCodes::mark_data($valid_data['mark_id'])->toArray();

        $list_directories = [];
        $mark_types_ids = array_filter([$new_mark_data['mark_type_id']]);
        if (count($mark_types_ids) > 0) {
            $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
        }

        $mark_statuses_ids = array_filter([$new_mark_data['mark_status_id']]);
        if (count($mark_statuses_ids) > 0)
        {
            $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
        }

        $mark_tool_types_ids = array_filter([$new_mark_data['mark_tool_type_id']]);
        if (count($mark_tool_types_ids) > 0)
        {
            $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
        }

        $mark_tools_locations_ids = array_filter([$new_mark_data['tool_location_id']]);
        if (count($mark_tools_locations_ids) > 0)
        {
            $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
        }
        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'mark_data' => [$new_mark_data],
            'animal_id' => $new_mark_data['animal_id'],
            'list_directories' => $list_directories,
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListMarkResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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
            $animals_list = $dataAnimalsModel->animals_list(9999999, 1, false, $valid_data['filter'], $valid_data);

            if($animals_list && count($animals_list) > 0)
            {
                $valid_data['animal_id']			= array_column($animals_list, 'animal_id');
            }
        }

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

                if (isset($item['mark_status']))
                {
                    $mark_status = DirectoryMarkStatuses::find($item['mark_status']);
                    if ($mark_status)
                    {
                        $data_for_update['code_status_id'] = $item['mark_status'];
                    }
                }

                if (isset($item['mark_tool_type']))
                {
                    $mark_tool_type = DirectoryMarkToolTypes::find($item['mark_tool_type']);
                    if ($mark_tool_type)
                    {
                        $data_for_update['code_tool_type_id'] = $item['mark_tool_type'];
                    }
                }

                if (isset($item['mark_tool_location']))
                {
                    $mark_tool_location = DirectoryToolsLocations::find($item['mark_tool_location']);
                    if ($mark_tool_location)
                    {
                        $data_for_update['code_tool_location_id'] = $item['mark_tool_location'];
                    }
                }

                if (isset($item['description']))
                {
                    $data_for_update['code_description'] = $item['description'];
                }

                if (count($data_for_update) > 0)
                {
                    DataAnimalsCodes::updateMarkGroup($data_for_update, $code_type_id, $valid_data['animal_id']);
                }
            }
        }

        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Данные успешно обновлены',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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

        DataAnimalsCodes::where('code_id', $valid_data['mark_id'])
            ->update(['code_tool_photo' => $mark_model->addFileMarkPhoto($request)]);

        $new_mark_data = DataAnimalsCodes::mark_data($valid_data['mark_id'])->toArray();

        $list_directories = [];
        $mark_types_ids = array_filter([$new_mark_data['mark_type_id']]);
        if (count($mark_types_ids) > 0) {
            $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
        }

        $mark_statuses_ids = array_filter([$new_mark_data['mark_status_id']]);
        if (count($mark_statuses_ids) > 0)
        {
            $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
        }

        $mark_tool_types_ids = array_filter([$new_mark_data['mark_tool_type_id']]);
        if (count($mark_tool_types_ids) > 0)
        {
            $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
        }

        $mark_tools_locations_ids = array_filter([$new_mark_data['tool_location_id']]);
        if (count($mark_tools_locations_ids) > 0)
        {
            $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
        }
        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'mark_data' => [$new_mark_data],
            'animal_id' => $new_mark_data['animal_id'],
            'list_directories' => $list_directories,
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListMarkResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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

        $mark_model->deleteMarkPhoto($request);

        DataAnimalsCodes::where('code_id', $valid_data['mark_id'])
            ->update(['code_tool_photo' => '']);

        $new_mark_data = DataAnimalsCodes::mark_data($valid_data['mark_id'])->toArray();

        $list_directories = [];
        $mark_types_ids = array_filter([$new_mark_data['mark_type_id']]);
        if (count($mark_types_ids) > 0) {
            $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
        }

        $mark_statuses_ids = array_filter([$new_mark_data['mark_status_id']]);
        if (count($mark_statuses_ids) > 0)
        {
            $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
        }

        $mark_tool_types_ids = array_filter([$new_mark_data['mark_tool_type_id']]);
        if (count($mark_tool_types_ids) > 0)
        {
            $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
        }

        $mark_tools_locations_ids = array_filter([$new_mark_data['tool_location_id']]);
        if (count($mark_tools_locations_ids) > 0)
        {
            $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
        }
        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'mark_data' => [$new_mark_data],
            'animal_id' => $new_mark_data['animal_id'],
            'list_directories' => $list_directories,
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAnimalsListMarkResource::class,
            'response_resource_dictionary' => SvrApiAnimalsDataDictionaryResource::class,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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

        $animal_data = DataAnimals::animal_data($valid_data['animal_id']);

        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }

        DataAnimals::setAnimalKeepingCompanyObject($valid_data['animal_id'], $valid_data['company_object_id']);

        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Поднадзорный объект успешно установлен',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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

        $animal_data = DataAnimals::animal_data($valid_data['animal_id']);

        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }

        DataAnimals::setAnimalBirthCompanyObject($valid_data['animal_id'], $valid_data['company_object_id']);

        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Поднадзорный объект успешно установлен',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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
            $animals_list = $dataAnimalsModel->animals_list(9999999, 1, false, $valid_data['filter'], $valid_data);

            if($animals_list && count($animals_list) > 0)
            {
                $valid_data['animal_id']			= array_column($animals_list, 'animal_id');
            }
        }

        if(isset($valid_data['animal_id']) && count($valid_data['animal_id']) > 0)
        {
            $data_for_update = [];

            if (isset($valid_data['keeping_object']) && $valid_data['keeping_object'] > 0)
            {
                $company_object_data = DataCompaniesObjects::find($valid_data['keeping_object']);

                if ($company_object_data)
                {
                    $data_for_update['animal_object_of_keeping_id'] = $valid_data['keeping_object'];
                }
            }

            if (isset($valid_data['birth_object']) && $valid_data['birth_object'] > 0)
            {
                $company_object_data = DataCompaniesObjects::find($valid_data['birth_object']);

                if ($company_object_data)
                {
                    $data_for_update['animal_object_of_birth_id'] = $valid_data['birth_object'];
                }
            }

            if (isset($valid_data['keeping_type']) && $valid_data['keeping_type'] > 0)
            {
                $keeping_types_list = DirectoryKeepingTypes::find($valid_data['keeping_type']);

                if ($keeping_types_list)
                {
                    $data_for_update['animal_type_of_keeping_id'] = $valid_data['keeping_type'];
                }
            }

            if (isset($valid_data['keeping_purpose']) && $valid_data['keeping_purpose'] > 0)
            {
                $keeping_types_list = DirectoryKeepingPurposes::find($valid_data['keeping_purpose']);

                if ($keeping_types_list)
                {
                    $data_for_update['animal_purpose_of_keeping_id'] = $valid_data['keeping_purpose'];
                }
            }

            if (count($data_for_update) > 0)
            {
                DataAnimals::updateAnimalsGroup($data_for_update, $data_for_update['animal_id']);
            }
        }

        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Данные успешно сохранены',
            'response_resource_data' => false,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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

        $animal_data = DataAnimals::animal_data($valid_data['animal_id']);

        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }

        DataAnimals::setAnimalKeepingType($valid_data['animal_id'], $valid_data['keeping_type']);

        $animal_data = DataAnimals::animal_data($valid_data['animal_id']);

        $valid_data['data_sections'] = ['main','gen','base','mark','genealogy','vib','registration','history'];

        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animal_mark_data($valid_data['animal_id']);

        $user = auth()->user();

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
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

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

        $animal_data = DataAnimals::animal_data($valid_data['animal_id']);

        if (empty($animal_data))
        {
            throw new CustomException('Животное не найдено',200);
        }

        DataAnimals::setAnimalKeepingPurpose($valid_data['animal_id'], $valid_data['keeping_purpose']);

        $animal_data = DataAnimals::animal_data($valid_data['animal_id']);

        $valid_data['data_sections'] = ['main','gen','base','mark','genealogy','vib','registration','history'];

        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animal_mark_data($valid_data['animal_id']);

        $user = auth()->user();

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
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }
}
