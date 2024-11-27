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
    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];
        if (isset($this->resource['list_directories']['countries_list']))
        {
            $returned_data['countries_list'] = new SvrApiCountriesListResource($this->resource['list_directories']['countries_list']);
        }
        if (isset($this->resource['list_directories']['species_list']))
        {
            $returned_data['species_list'] = new SvrApiSpeciesListResource($this->resource['list_directories']['species_list']);
        }
        if (isset($this->resource['list_directories']['breeds_list']))
        {
            $returned_data['breeds_list'] = new SvrApiBreedsListResource($this->resource['list_directories']['breeds_list']);
        }
        if (isset($this->resource['list_directories']['genders_list']))
        {
            $returned_data['genders_list'] = new SvrApiGendersListResource($this->resource['list_directories']['genders_list']);
        }
        if (isset($this->resource['list_directories']['companies_list']))
        {
            $returned_data['companies_list'] = new SvrApiCompaniesListResource($this->resource['list_directories']['companies_list']);
        }
        if (isset($this->resource['list_directories']['keeping_types_list']))
        {
            $returned_data['keeping_types_list'] = new SvrApiKeepingTypesListResource($this->resource['list_directories']['keeping_types_list']);
        }
        if (isset($this->resource['list_directories']['keeping_purposes_list']))
        {
            $returned_data['keeping_purposes_list'] = new SvrApiKeepingPurposesListResource($this->resource['list_directories']['keeping_purposes_list']);
        }
        if (isset($this->resource['list_directories']['out_types_list']))
        {
            $returned_data['out_types_list'] = new SvrApiOutTypesListResource($this->resource['list_directories']['out_types_list']);
        }
        if (isset($this->resource['list_directories']['out_basises_list']))
        {
            $returned_data['out_basises_list'] = new SvrApiOutBasisesListResource($this->resource['list_directories']['out_basises_list']);
        }
        if (isset($this->resource['list_directories']['companies_objects_list']))
        {
            $returned_data['companies_objects_list'] = new SvrApiCompaniesObjectsListResource($this->resource['list_directories']['companies_objects_list']);
        }

        return $returned_data;
    }
}
