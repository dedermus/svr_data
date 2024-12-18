<?php

namespace Svr\Data\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

use Svr\Core\Resources\SvrApiUsersSimpleListResource;


class SvrApiCompaniesObjectsListDictionaryResource extends JsonResource
{

	/**
	 * Transform the resource collection into an array.
	 *
	 * @param Request|Collection $request
	 * @return array
	 */
	public function toArray(Request|Collection $request): array
	{
		return [
			'companies_list'			=> new SvrApiCompaniesListResource($this->resource['companies_list'])
		];
	}
}
