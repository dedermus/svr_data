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
use Svr\Core\Exceptions\CustomException;

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
		$animals			= new DataAnimals();
		$filterKeys			= ['application_id'];
		$rules				= $model->getFilterValidationRules($request, $filterKeys);
		$messages			= $model->getFilterValidationMessages($filterKeys);

		$request->validate($rules, $messages);

		$credentials		= $request->only($filterKeys);

		$application_data	= DataApplications::applicationData($credentials['application_id']);

		if(is_null($application_data))
		{
			throw new CustomException('Запрошенная заявка не найдена', 200);
		}

        $data				= collect([
			'application_data'				=> $application_data,
			'application_status'			=> self::DictionaryApplicationStatus(),
            'user_id'						=> $user['user_id'],
            'status'						=> true,
            'message'						=> '',
			'animals_list'					=> $animals->animals_list(999999999, 1, true, [
				'application_id'				=> $application_data->application_id,
			], []),
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
		$animals			= new DataAnimals();
		$filterKeys			= ['application_id', 'application_status'];
		$rules				= $model->getFilterValidationRules($request, $filterKeys);
		$messages			= $model->getFilterValidationMessages($filterKeys);

		$request->validate($rules, $messages);

		$credentials		= $request->only($filterKeys);

		if(!in_array($credentials['application_status'], ['prepared','sent']))
		{
			throw new CustomException('Нельзя изменить статус этой заявки', 200);
		}

		$application_data	= (array)DataApplications::applicationData($credentials['application_id']);

		if(is_null($application_data))
		{
			throw new CustomException('Запрошенная заявка не найдена', 200);
		}

		$role_data			= SystemRoles::find($user['role_id'])->toArray();

		switch($credentials['application_status'])
		{
			case 'prepared':
				if($role_data['role_slug'] !== 'doctor_company')
				{
					throw new CustomException('Нельзя завершить заявку с текущей ролью', 200);
				}

				if($application_data['application_status'] !== 'created')
				{
					throw new CustomException('Нельзя завершить эту заявку', 200);
				}

				//TODO: ждем реализацию методов уведомлений
//				(new module_Notifications)->notification_create('application_prepared', $this->USER('company_id'), false, $application_data);

				DataApplications::find($credentials['application_id'])->update(['application_status' => 'prepared']);
			break;

			case 'sent':
				if($application_data['application_status'] !== 'prepared')
				{
					throw new CustomException('Нельзя отправить эту заявку', 200);
				}

				if (empty($user['user_herriot_login']) || empty($user['user_herriot_web_login']))
				{
					throw new CustomException('У пользователя не установлены реквизиты Хорриота', 200);
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
			'animals_list'					=> $animals->animals_list(999999999, 1, true, [
				'application_id'				=> $application_data['application_id'],
			], []),
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

		$valid_data = $request->validate(
			[
				'search'                                 		=> ['string', 'max:255'],
				'company_location_id'                           => ['int', Rule::exists(DataCompaniesLocations::class, 'company_location_id')],
				'company_region_id'                            	=> ['int', Rule::exists(DirectoryCountriesRegion::class, 'region_id')],
				'company_district_id'                          	=> ['int', Rule::exists(DirectoryCountriesRegionsDistrict::class, 'district_id')],
				'animal_id'                               		=> ['array'],
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
			$animals_list		= $model->animals_list(999999999, 1, true, $valid_data['filter'], $valid_data);

			if($animals_list)
			{
				$animals_ids	= array_column($animals_list, 'animal_id');
			}else{
				throw new CustomException('Животные не найдены', 200);
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
					$animal_add_result['errors']	+= 1;
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
			throw new CustomException('Запрошенная заявка не найдена', 200);
		}

		// TODO: не забыть запилить после реализации оповещений
//		(new module_Notifications)->notification_create('application_animal_add', $this->USER('company_id'), false, $application_data);

		$data				= collect([
			'user_id'						=> $user['user_id'],
			'status'						=> true,
			'message'						=> $animal_add_message,
			'response_resource_data'		=> false,
			'response_resource_dictionary'	=> false,
			'pagination'					=> [
				'total_records'					=> 1,
				'cur_page'						=> 1,
				'per_page'						=> 1
			],
		]);

		return new SvrApiResponseResource($data);
	}


	public function applicationsAnimalDelete(Request $request): SvrApiResponseResource|JsonResponse
	{
		/** @var  $user - получим авторизированного пользователя */
		$user						= auth()->user();
		$valid_data					= $request->validate([
			'animal_id'                 => ['required', 'int']
		]);

		$application_data			= DataApplications::applicationData(false, true, false);
		$animal_data				= DataAnimals::animal_data($valid_data['animal_id']);

		if(is_null($application_data) || $application_data === false)
		{
			throw new CustomException('Активная заявка отсутствует', 200);
		}

		if(is_null($animal_data))
		{
			throw new CustomException('Животное не найдено', 200);
		}

		if($application_data->application_status !== 'created')
		{
			throw new CustomException('Нельзя удалить животное из подготовленной заявки', 200);
		}

		if($animal_data['animal_status_delete'] !== 'active')
		{
			throw new CustomException('Животное удалено', 200);
		}

		if($animal_data['application_id'] !== $application_data->application_id)
		{
			throw new CustomException('Животное находится в другой заявке', 200);
		}

		if(!empty($animal_data['application_animal_status']))
		{
			switch($animal_data['application_animal_status'])
			{
				case 'added':
				case 'in_application':
				case 'registered':
				case 'rejected':
				case 'finished':
					// можно удалять
				break;
				case 'sent':
					throw new CustomException('Животное уже отправлено на регистрацию', 200);
				break;
			}
		}

		DataApplicationsAnimals::find($animal_data['application_animal_id'])->delete();

		// TODO: не забыть запилить после реализации оповещений
//		(new module_Notifications)->notification_create('application_animal_delete', $this->USER('company_id'), false, $application_data);

		$data				= collect([
			'user_id'						=> $user['user_id'],
			'status'						=> true,
			'message'						=> 'Животное успешно удалено из текущей заявки',
			'response_resource_data'		=> false,
			'response_resource_dictionary'	=> false,
			'pagination'					=> [
				'total_records'					=> 1,
				'cur_page'						=> 1,
				'per_page'						=> 1
			],
		]);

		return new SvrApiResponseResource($data);
	}
}
