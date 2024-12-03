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
        'countries_list' => SvrApiCountriesListResource::class,
        'species_list' => SvrApiSpeciesListResource::class,
        'breeds_list' => SvrApiBreedsListResource::class,
        'genders_list' => SvrApiGendersListResource::class,
        'companies_list' => SvrApiCompaniesListResource::class,
        'keeping_types_list' => SvrApiKeepingTypesListResource::class,
        'keeping_purposes_list' => SvrApiKeepingPurposesListResource::class,
        'out_types_list' => SvrApiOutTypesListResource::class,
        'out_basises_list' => SvrApiOutBasisesListResource::class,
        'companies_objects_list' => SvrApiCompaniesObjectsListResource::class,
        'mark_tools_locations_list' => SvrApiToolsLocationsListResource::class,
        'mark_tool_types_list' => SvrApiToolTypesListResource::class,
        'mark_statuses_list' => SvrApiMarkStatusesListResource::class,
        'mark_types_list' => SvrApiMarkTypesListResource::class,
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
