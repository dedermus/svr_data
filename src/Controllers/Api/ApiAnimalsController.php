<?php

namespace Svr\Data\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataAnimalsCodes;
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataCompaniesObjects;
use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryAnimalsSpecies;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryGenders;
use Svr\Directories\Models\DirectoryKeepingPurposes;
use Svr\Directories\Models\DirectoryKeepingTypes;
use Svr\Directories\Models\DirectoryOutBasises;
use Svr\Directories\Models\DirectoryOutTypes;

class ApiAnimalsController extends Controller
{
    public function animalsData(Request $request)
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

        $mark_data = false;
        if (in_array('mark', $valid_data['data_sections'])) $mark_data = DataAnimalsCodes::animal_mark_data($valid_data['animal_id']);

        $list_directories = [];

        $countries_ids = array_filter([$animal_data['animal_country_nameport_id']]);
        if (count($countries_ids) > 0) {
            $list_directories['countries_list'] =  DirectoryCountries::find($countries_ids);
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
        if (count($companies_objects_ids) > 0)
        {
            $list_directories['companies_objects_list'] = DataCompaniesObjects::find($companies_objects_ids);
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
            'response_resource_data' => 'Svr\Data\Resources\SvrApiAnimalsDataResource',
            'response_resource_dictionary' => 'Svr\Data\Resources\SvrApiAnimalsDataDictionaryResource',
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }
}
