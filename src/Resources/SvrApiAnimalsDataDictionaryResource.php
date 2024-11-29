<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Svr\Core\Resources\SvrApiUserCompaniesLocationsListResource;
use Svr\Core\Resources\SvrApiUserDistrictsListResource;
use Svr\Core\Resources\SvrApiUserRegionsListResource;
use Svr\Core\Resources\SvrApiUserRolesListResource;


class SvrApiAnimalsDataDictionaryResource extends JsonResource
{
    private array $mapping_resources = [
        'countries_list' => 'Svr\Data\Resources\SvrApiCountriesListResource',
        'species_list' => 'Svr\Data\Resources\SvrApiSpeciesListResource',
        'breeds_list' => 'Svr\Data\Resources\SvrApiBreedsListResource',
        'genders_list' => 'Svr\Data\Resources\SvrApiGendersListResource',
        'companies_list' => 'Svr\Data\Resources\SvrApiCompaniesListResource',
        'keeping_types_list' => 'Svr\Data\Resources\SvrApiKeepingTypesListResource',
        'keeping_purposes_list' => 'Svr\Data\Resources\SvrApiKeepingPurposesListResource',
        'out_types_list' => 'Svr\Data\Resources\SvrApiOutTypesListResource',
        'out_basises_list' => 'Svr\Data\Resources\SvrApiOutBasisesListResource',
        'companies_objects_list' => 'Svr\Data\Resources\SvrApiCompaniesObjectsListResource',
        'mark_tools_locations_list' => 'Svr\Data\Resources\SvrApiToolsLocationsListResource',
        'mark_tool_types_list' => 'Svr\Data\Resources\SvrApiToolTypesListResource',
        'mark_statuses_list' => 'Svr\Data\Resources\SvrApiMarkStatusesListResource',
        'mark_types_list' => 'Svr\Data\Resources\SvrApiMarkTypesListResource',
    ];

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];

        foreach ($this->resource['list_directories'] as $dictionary_name => $dictionary_value_list)
        {
            $returned_data[$dictionary_name] = new $this->mapping_resources[$dictionary_name]($dictionary_value_list);
        }

        return $returned_data;
    }
}
