<?php

namespace Svr\Data\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

use Svr\Core\Models\SystemRoles;
use Svr\Core\Traits\GetDictionary;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataApplications;
use Svr\Core\Resources\SvrApiResponseResource;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Svr\Core\Enums\AnimalRegisterStatusEnum;
use Svr\Core\Enums\ApplicationAnimalStatusEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Directories\Models\DirectoryAnimalsBreeds;
use Svr\Directories\Models\DirectoryAnimalsSpecies;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

use Svr\Data\Models\DataApplicationsAnimals;
use Svr\Data\Resources\SvrApiApplicationDataResource;
use Svr\Data\Resources\SvrApiApplicationDataDictionaryResource;

class ApiApplicationsController extends Controller
{
	use GetDictionary;

    /**
     * Получение информации о пользователе.
     *
     * @return SvrApiResponseResource|JsonResponse
     */
    public function applicationsData(Request $request): SvrApiResponseResource|JsonResponse
    {
        /** @var  $user - получим авторизированного пользователя */
        $user				= auth()->user();
		$model				= new DataApplications();
		$filterKeys			= ['application_id'];
		$rules				= $model->getFilterValidationRules($request, $filterKeys);
		$messages			= $model->getFilterValidationMessages($filterKeys);

		$request->validate($rules, $messages);

		$credentials		= $request->only($filterKeys);
		$application_data	= DataApplications::applicationData($credentials['application_id']);

		if(is_null($application_data))
		{
			return response()->json(['error' => 'Запрошенная заявка не найдена'], 404);
		}

        $data				= collect([
			'application_data'				=> $application_data,
			'application_status'			=> self::DictionaryApplicationStatus(),
            'user_id'						=> $user['user_id'],
            'status'						=> true,
            'message'						=> '',
            'response_resource_data'		=> SvrApiApplicationDataResource::class,
            'response_resource_dictionary'	=> SvrApiApplicationDataDictionaryResource::class,
            'pagination'					=> [
                'total_records'					=> 1,
                'cur_page'						=> 1,
                'per_page'						=> 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }



	public function applicationsStatus(Request $request): SvrApiResponseResource|JsonResponse
	{
		/** @var  $user - получим авторизированного пользователя */
		$user				= auth()->user();
		$model				= new DataApplications();
		$filterKeys			= ['application_id', 'application_status'];
		$rules				= $model->getFilterValidationRules($request, $filterKeys);
		$messages			= $model->getFilterValidationMessages($filterKeys);

		$request->validate($rules, $messages);

		$credentials		= $request->only($filterKeys);

		if(!in_array($credentials['application_status'], ['prepared','sent']))
		{
			return response()->json(['error' => 'Нельзя изменить статус этой заявки'], 403);
		}

		$application_data	= (array)DataApplications::applicationData($credentials['application_id']);

		if(is_null($application_data))
		{
			return response()->json(['error' => 'Запрошенная заявка не найдена'], 404);
		}

		$role_data			= SystemRoles::find($user['role_id'])->toArray();

		switch($credentials['application_status'])
		{
			case 'prepared':
				if($role_data['role_slug'] !== 'doctor_company')
				{
					return response()->json(['error' => 'Нельзя завершить заявку с текущей ролью'], 403);
				}

				if($application_data['application_status'] !== 'created')
				{
					return response()->json(['error' => 'Нельзя завершить эту заявку'], 403);
				}

				//TODO: ждем реализацию методов уведомлений
//				(new module_Notifications)->notification_create('application_prepared', $this->USER('company_id'), false, $application_data);

				DataApplications::find($credentials['application_id'])->update(['application_status' => 'prepared']);
			break;

			case 'sent':
				if($application_data['application_status'] !== 'prepared')
				{
					return response()->json(['error' => 'Нельзя отправить эту заявку'], 403);
				}

				if (empty($user['user_herriot_login']) || empty($user['user_herriot_web_login']))
				{
					return response()->json(['error' => 'У пользователя не установлены реквизиты Хорриота.'], 403);
				}

				//TODO: ждем реализацию методов уведомлений
//				(new module_Notifications)->notification_create('application_sent', $this->USER('company_id'), false, $application_data);

				DataApplications::find($credentials['application_id'])->update([
					'application_status' => 'sent',
					'doctor_id' => $user['user_id']
				]);
				DataApplicationsAnimals::where('application_id','=', $credentials['application_id'])->update([
					'application_animal_date_sent' => date('Y-m-d H:i:s')
				]);
			break;
		}

		$data				= collect([
			'application_data'				=> DataApplications::applicationData($credentials['application_id']),
			'application_status'			=> self::DictionaryApplicationStatus(),
			'user_id'						=> $user['user_id'],
			'status'						=> true,
			'message'						=> '',
			'response_resource_data'		=> SvrApiApplicationDataResource::class,
			'response_resource_dictionary'	=> SvrApiApplicationDataDictionaryResource::class,
			'pagination'					=> [
				'total_records'					=> 1,
				'cur_page'						=> 1,
				'per_page'						=> 1
			],
		]);

		return new SvrApiResponseResource($data);
	}


	public function applicationsAnimalAdd(Request $request): SvrApiResponseResource|JsonResponse
	{
		/** @var  $user - получим авторизированного пользователя */
		$user				= auth()->user();
		$model				= new DataAnimals();

		$animal_add_message			= '';
		$animal_add_result			= [
			'success'		=> 0,
			'errors'		=> 0
		];

//		$filterKeys			= ['animal_id'];
//		$rules				= $model->getFilterValidationRules($request, $filterKeys);
//		$messages			= $model->getFilterValidationMessages($filterKeys);
//		$animals			= new DataAnimals();

//		$request->validate($rules, $messages);
//
//		$credentials		= $request->only($filterKeys);
//		$animal_data		= DataAnimals::animal_data($credentials['animal_id']);
//		$application_data	= (array)DataApplications::applicationData($credentials['application_id']);
//
//		if(is_null($application_data))
//		{
//			return response()->json(['error' => 'Запрошенная заявка не найдена'], 404);
//		}
//
//		if(empty($animal_data))
//		{
//			return response()->json(['error' => 'Животное не найдено'], 404);
//		}


		$valid_data = $request->validate(
			[
				'search'                                 => ['string', 'max:255'],
				'company_location_id'                           => ['int', Rule::exists(DataCompaniesLocations::class, 'company_location_id')],
				'company_region_id'                            	=> ['int', Rule::exists(DirectoryCountriesRegion::class, 'region_id')],
				'company_district_id'                          	=> ['int', Rule::exists(DirectoryCountriesRegionsDistrict::class, 'district_id')],
				'animal_id'                               => ['array'],
				'filter'                                        => ['array'],
//				'filter.register_status'                        => ['string', Rule::in(AnimalRegisterStatusEnum::get_option_list())],
				'filter.animal_sex'                             => ['string', Rule::in(['male','female','MALE','FEMALE'])],
				'filter.specie_id'                              => ['int', Rule::exists(DirectoryAnimalsSpecies::class, 'specie_id')],
				'filter.animal_date_birth_min'                  => ['date'],
				'filter.animal_date_birth_max'                  => ['date'],
				'filter.breeds_id'                              => ['int', Rule::exists(DirectoryAnimalsBreeds::class, 'breed_id')],
				'filter.application_id'                         => ['int', Rule::exists(DataApplications::class, 'application_id')],
				'filter.animal_status'                          => ['string', Rule::in(SystemStatusEnum::get_option_list())],
				'filter.animal_date_create_record_svr_min'      => ['date'],
				'filter.animal_date_create_record_svr_max'      => ['date'],
				'filter.animal_date_create_record_herriot_min'  => ['date'],
				'filter.animal_date_create_record_herriot_max'  => ['date'],
				'filter.application_animal_status'              => ['string', Rule::in(ApplicationAnimalStatusEnum::get_option_list())],
				'filter.search_inv'                             => ['string', 'max:20'],
				'filter.search_unsm'                            => ['string', 'max:11'],
				'filter.search_horriot_number'                  => ['string', 'max:14'],
			]);

		if (!isset($valid_data['filter'])) $valid_data['filter'] = [];

		if(isset($valid_data['animal_id']) && count($valid_data['animal_id']) > 0)
		{
			$animals_ids		= $valid_data['animal_id'];
		}else{
			$animals_list		= $model->animalsList(999999999, 1, true, $valid_data['filter'], $valid_data);

			if($animals_list)
			{
				$animals_ids	= array_column($animals_list, 'animal_id');
			}else{
				throw new NotFoundHttpException('Животные не найдены', null, 200);
			}
		}

		if($animals_ids && count($animals_ids) > 0)
		{
			foreach($animals_ids as $animal_id)
			{
				$addition_result	= DataApplications::applicationsAnimalAdd($animal_id);

				if($addition_result)
				{
					$animal_add_result['success']	+= 1;
				}else{
					$animal_add_result['errors']		+= 1;
				}
			}
		}

		if($animal_add_result['success'] > 0)
		{
			$animal_add_message						= 'Добавлено животных: '.$animal_add_result['success'];
		}

		if($animal_add_result['errors'] > 0)
		{
			$animal_add_message						= '; Ошибок: '.$animal_add_result['errors'];
		}

		$application_data	= (array)DataApplications::applicationData(false, true, false);

		if(is_null($application_data))
		{
			throw new NotFoundHttpException('Запрошенная заявка не найдена', null, 200);
		}

		// TODO: не забыть запилить после реализации оповещений
//		(new module_Notifications)->notification_create('application_animal_add', $this->USER('company_id'), false, $application_data);

		dd($animal_add_message);



		$data				= collect([
			'application_data'				=> DataApplications::applicationData($credentials['application_id']),
			'application_status'			=> self::DictionaryApplicationStatus(),
			'user_id'						=> $user['user_id'],
			'status'						=> true,
			'message'						=> '',
			'response_resource_data'		=> SvrApiApplicationDataResource::class,
			'response_resource_dictionary'	=> SvrApiApplicationDataDictionaryResource::class,
			'pagination'					=> [
				'total_records'					=> 1,
				'cur_page'						=> 1,
				'per_page'						=> 1
			],
		]);

		return new SvrApiResponseResource($data);
	}
}
