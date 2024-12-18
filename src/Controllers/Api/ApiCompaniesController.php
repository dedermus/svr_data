<?php

namespace Svr\Data\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Svr\Core\Exceptions\CustomException;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataAnimalsCodes;
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesObjects;
use Svr\Data\Resources\SvrApiCompaniesObjectsListDictionaryResource;
use Svr\Data\Resources\SvrApiCompaniesObjectsListResource;
use Svr\Data\Resources\SvrApiAnimalsDataResource;

class ApiCompaniesController extends Controller
{
    /**
     * Информация по животному
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     * @throws Exception
     */
    public function companyObjectList (Request $request, $company_id): SvrApiResponseResource|JsonResponse
    {
		$request->merge(['company_id' => $company_id]);

        $validator = Validator::make($request->all(), [
            'company_id' => ['required', 'integer', Rule::exists('Svr\Data\Models\DataCompanies', 'company_id')]
        ],
        [
            'company_id' => trans('svr-core-lang::validation')
        ]);

        $valid_data = $validator->validated();

		$user = auth()->user();

        $objects_list = DataCompaniesObjects::where('company_id', $valid_data['company_id'])->orderBy('company_object_is_favorite', 'DESC')->get();

        if (!$objects_list || count($objects_list) < 1)
        {
            throw new CustomException('Поднадзорные объекты не найдены', 200);
        }

		Config::set('total_records', count($objects_list));

		$companies_list		= DataCompanies::find($objects_list->pluck('company_id'));

//		dd($companies_list);

        //складываем все в коллекцию
        $data = collect([
            'user_id' => $user['user_id'],
            'objects_list' => $objects_list,
			'without_keys' => true,
			'companies_list' => $companies_list,
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiCompaniesObjectsListResource::class,
            'response_resource_dictionary' => SvrApiCompaniesObjectsListDictionaryResource::class,
        ]);

        //отдаем ресурс с ответом
        return new SvrApiResponseResource($data);
    }
}
