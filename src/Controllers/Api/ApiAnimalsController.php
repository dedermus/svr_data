<?php

namespace Svr\Data\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\AnimalRegisterStatusEnum;
use Svr\Core\Enums\ApplicationAnimalStatusEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataAnimalsCodes;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataCompaniesObjects;
use Svr\Data\Resources\SvrApiAnimalsDataDictionaryResource;
use Svr\Data\Resources\SvrApiAnimalsDataResource;
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
     */
    public function animalsData(Request $request): SvrApiResponseResource|JsonResponse
    {
        $valid_data = $request->validate([
            'animal_id' => ['required', 'integer', Rule::exists('Svr\Data\Models\DataAnimals', 'animal_id')],
            'application_id' => ['array'],
            'data_sections' => ['array', Rule::in(['main','gen','base','mark','genealogy','vib','registration','history'])]
        ]);

        if (!isset($valid_data['data_sections'])) {
            $valid_data['data_sections'] = ['main'];
        }

        $animal_data = DataAnimals::animal_data($valid_data['animal_id'], $valid_data['application_id'] ?? false);

        if (empty($animal_data))
        {
            return response()->json(['message' => 'Животное не найдено', 'status' => false], 200);
        }

        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animal_mark_data($valid_data['animal_id']);

        $list_directories = [];

        $countries_ids = array_filter([$animal_data['animal_country_nameport_id']]);
        if (count($countries_ids) > 0) {
            $list_directories['countries_list'] = DirectoryCountries::find($countries_ids);
        }

        $species_ids = array_filter([$animal_data['animal_specie_id']]);
        if (count($species_ids) > 0) {
            $list_directories['species_list'] = DirectoryAnimalsSpecies::find($species_ids);
        }

        $breeds_ids = array_filter([$animal_data['animal_breed_id'], $animal_data['animal_father_breed_id'], $animal_data['animal_mother_breed_id']]);
        if (count($breeds_ids) > 0) {
            $list_directories['breeds_list'] = DirectoryAnimalsBreeds::find($breeds_ids);
        }

        $genders_ids = array_filter([$animal_data['animal_gender_id']]);
        if (count($genders_ids) > 0) {
            $list_directories['genders_list'] = DirectoryGenders::find($genders_ids);
        }

        $companies_ids = array_filter([$animal_data['animal_owner_company_id'], $animal_data['animal_keeping_company_id'], $animal_data['animal_birth_company_id']]);
        if (count($companies_ids) > 0) {
            $list_directories['companies_list'] = DataCompaniesLocations::companyLocationDataByCompanyId($companies_ids);
        }

        $keeping_types_ids = array_filter([$animal_data['animal_type_of_keeping_id']]);
        if (count($keeping_types_ids) > 0) {
            $list_directories['keeping_types_list'] = DirectoryKeepingTypes::find($keeping_types_ids);
        }

        $keeping_purposes_ids = array_filter([$animal_data['animal_keeping_purpose_id']]);
        if (count($keeping_purposes_ids) > 0) {
            $list_directories['keeping_purposes_list'] = DirectoryKeepingPurposes::find($keeping_purposes_ids);
        }

        $out_types_ids = array_filter([$animal_data['animal_out_type_id']]);
        if (count($out_types_ids) > 0) {
            $list_directories['out_types_list'] = DirectoryOutTypes::find($out_types_ids);
        }

        $out_basises_ids = array_filter([$animal_data['animal_out_basis_id']]);
        if (count($out_basises_ids) > 0) {
            $list_directories['out_basises_list'] = DirectoryOutBasises::find($out_basises_ids);
        }

        $companies_objects_ids = array_filter([$animal_data['animal_object_of_keeping_id'], $animal_data['animal_object_of_birth_id']]);
        if (count($companies_objects_ids) > 0) {
            $list_directories['companies_objects_list'] = DataCompaniesObjects::find($companies_objects_ids);
        }

        if ($mark_data)
        {
            $mark_types_ids = array_filter(array_column($mark_data, 'mark_type_id'));
            if (count($mark_types_ids) > 0) {
                $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
            }

            $mark_statuses_ids = array_filter(array_column($mark_data,'mark_status_id'));
            if (count($mark_statuses_ids) > 0)
            {
                $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
            }

            $mark_tool_types_ids = array_filter(array_column($mark_data, 'mark_tool_type_id'));
            if (count($mark_tool_types_ids) > 0)
            {
                $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
            }

            $mark_tools_locations_ids = array_filter(array_column($mark_data, 'tool_location_id'));
            if (count($mark_tools_locations_ids) > 0)
            {
                $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
            }
        }

        $user = auth()->user();

        $data = collect([
            'user_id' => $user['user_id'],
            'animal_data' => $animal_data,
            'mark_data' => $mark_data,
            'data_sections' => $valid_data['data_sections'],
            'list_directories' => $list_directories,
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
     * @return SvrApiResponseResource
     */
    public function animalsList(Request $request)
    {
        $valid_data = $request->validate(
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
        ]);
        //TODO: Тут сейчас будет ерунда, надо будет переделать когда появится осознание
        if (!isset($valid_data['filter'])) $valid_data['filter'] = [];
        if (!isset($valid_data['filter']['specie_id']))
        {
            $valid_data['filter']['specie_id'] = [];
        } else {
            if (!is_array($valid_data['filter']['specie_id']))
            {
                $valid_data['filter']['specie_id'] = [$valid_data['filter']['specie_id']];
            }
        }
        if (!isset($valid_data['filter']['breeds_id']))
        {
            $valid_data['filter']['breeds_id'] = [];
        } else {
            if (!is_array($valid_data['filter']['breeds_id']))
            {
                $valid_data['filter']['breeds_id'] = [$valid_data['filter']['breeds_id']];
            }
        }
        if (!isset($valid_data['filter']['application_id']))
        {
            $valid_data['filter']['application_id'] = [];
        } else {
            if (!is_array($valid_data['filter']['application_id']))
            {
                $valid_data['filter']['application_id'] = [$valid_data['filter']['application_id']];
            }
        }

        $user = auth()->user();

        $dataAnimalsModel = new DataAnimals();
        $animals_list = $dataAnimalsModel->animals_list($user['pagination_per_page'], $user['pagination_cur_page'], false, $valid_data['filter'], $valid_data);
        $animals_count = $dataAnimalsModel->animals_count;

        if ($animals_list === false)
        {
            return response()->json(['message' => 'Животные не найдены', 'status' => false], 200);
        }

        if (!isset($valid_data['data_sections'])) {
            $valid_data['data_sections'] = ['main'];
        }

        $list_directories = [];

        $countries_ids = array_filter(array_column($animals_list, 'animal_country_nameport_id'));
        if (count($countries_ids) > 0) {
            $list_directories['countries_list'] = DirectoryCountries::find($countries_ids);;
        }

        $species_ids = array_filter(array_column($animals_list, 'animal_specie_id'));
        if (count($species_ids) > 0) {
            $list_directories['species_list'] = DirectoryAnimalsSpecies::find($species_ids);
        }

        $breeds_ids = array_filter(array_merge(array_column($animals_list, 'animal_breed_id'), array_column($animals_list, 'animal_father_breed_id'), array_column($animals_list, 'animal_mother_breed_id')));
        if (count($breeds_ids) > 0) {
            $list_directories['breeds_list'] = DirectoryAnimalsBreeds::find($breeds_ids);
        }

        $genders_ids = array_filter(array_column($animals_list, 'animal_gender_id'));
        if (count($genders_ids) > 0) {
            $list_directories['genders_list'] = DirectoryGenders::find($genders_ids);
        }

        $companies_ids = array_filter(array_merge(array_column($animals_list, 'animal_owner_company_id'), array_column($animals_list, 'animal_keeping_company_id'), array_column($animals_list, 'animal_birth_company_id')));
        if (count($companies_ids) > 0) {
            $list_directories['companies_list'] = DataCompaniesLocations::companyLocationDataByCompanyId($companies_ids);
        }

        $keeping_types_ids = array_filter(array_column($animals_list, 'animal_type_of_keeping_id'));
        if (count($keeping_types_ids) > 0) {
            $list_directories['keeping_types_list'] = DirectoryKeepingTypes::find($keeping_types_ids);
        }

        $keeping_purposes_ids = array_filter(array_column($animals_list, 'animal_keeping_purpose_id'));
        if (count($keeping_purposes_ids) > 0) {
            $list_directories['keeping_purposes_list'] = DirectoryKeepingPurposes::find($keeping_purposes_ids);
        }

        $out_types_ids = array_filter(array_column($animals_list, 'animal_out_type_id'));
        if (count($out_types_ids) > 0) {
            $list_directories['out_types_list'] = DirectoryOutTypes::find($out_types_ids);
        }

        $out_basises_ids = array_filter(array_column($animals_list, 'animal_out_basis_id'));
        if (count($out_basises_ids) > 0) {
            $list_directories['out_basises_list'] = DirectoryOutBasises::find($out_basises_ids);
        }

        $companies_objects_ids = array_filter(array_merge(array_column($animals_list, 'animal_object_of_keeping_id'), array_column($animals_list, 'animal_object_of_birth_id')));
        if (count($companies_objects_ids) > 0)
        {
            $list_directories['companies_objects_list'] = DataCompaniesObjects::find($companies_objects_ids);
        }

        if (in_array('mark', $valid_data['data_sections']))
        {
            $all_mark_data = [];
            foreach ($animals_list as &$animal)
            {
                $mark_data = DataAnimalsCodes::animal_mark_data($animal['animal_id']);
                $animal['mark_data'] = $mark_data;
                $all_mark_data[] = $mark_data;
            }

            $mark_types_ids = array_filter(array_column($all_mark_data, 'mark_type_id'));
            if (count($mark_types_ids) > 0)
            {
                $list_directories['mark_types_list'] = DirectoryMarkTypes::find($mark_types_ids);
            }

            $mark_statuses_ids = array_filter(array_column($all_mark_data,'mark_status_id'));
            if (count($mark_statuses_ids) > 0)
            {
                $list_directories['mark_statuses_list'] = DirectoryMarkStatuses::find($mark_statuses_ids);
            }

            $mark_tool_types_ids = array_filter(array_column($all_mark_data, 'mark_tool_type_id'));
            if (count($mark_tool_types_ids) > 0)
            {
                $list_directories['mark_tool_types_list'] = DirectoryMarkToolTypes::find($mark_tool_types_ids);
            }

            $mark_tools_locations_ids = array_filter(array_column($all_mark_data, 'tool_location_id'));
            if (count($mark_tools_locations_ids) > 0)
            {
                $list_directories['mark_tools_locations_list'] = DirectoryToolsLocations::find($mark_tools_locations_ids);
            }
        }

        $data = collect([
            'user_id' => $user['user_id'],
            'animals_list' => $animals_list,
            'data_sections' => $valid_data['data_sections'],
            'list_directories' => $list_directories,
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
}
