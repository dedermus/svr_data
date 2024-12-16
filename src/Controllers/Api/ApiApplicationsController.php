<?php

namespace Svr\Data\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

use Svr\Core\Enums\ApplicationStatusEnum;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotificationsMessages;
use Svr\Core\Traits\GetDictionary;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataApplications;
use Svr\Core\Resources\SvrApiResponseResource;

use Illuminate\Support\Facades\Config;

use Svr\Data\Models\DataCompanies;
use Svr\Data\Resources\SvrApiApplicationsListResource;
use Svr\Data\Resources\SvrApiApplicationsListDictionaryResource;
use Svr\Core\Exceptions\CustomException;

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
	 * Получение информации о заявке.
	 *
	 * @return SvrApiResponseResource|JsonResponse
	 * @throws CustomException
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
			throw new CustomException('Запрошенная заявка не найдена', 200);
		}

        $data				= collect([
			'application_data'				=> $application_data,
			'application_status'			=> self::DictionaryApplicationStatus(),
            'user_id'						=> $user['user_id'],
            'status'						=> true,
            'message'						=> '',
			'animals_list'					=> DataAnimals::animalsList(999999999, 1, true, [
				'application_id'				=> $application_data->application_id,
			], []),
            'response_resource_data'		=> SvrApiApplicationDataResource::class,
            'response_resource_dictionary'	=> SvrApiApplicationDataDictionaryResource::class
        ]);

        return new SvrApiResponseResource($data);
    }


	/**
	 * Изменение статуса заявки
	 *
	 * @throws CustomException
	 */
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

				(new SystemUsersNotifications())->notificationCreate('application_prepared', $application_data['company_id'], false, $application_data);

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

				(new SystemUsersNotifications())->notificationCreate('application_sent', $application_data['company_id'], false, $application_data);

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
			'animals_list'					=> DataAnimals::animalsList(999999999, 1, true, [
				'application_id'				=> $application_data['application_id'],
			], []),
			'response_resource_data'		=> SvrApiApplicationDataResource::class,
			'response_resource_dictionary'	=> SvrApiApplicationDataDictionaryResource::class
		]);

		return new SvrApiResponseResource($data);
	}


	/**
	 * Добавление животного в заявку
	 *
	 * @throws CustomException
	 */
	public function applicationsAnimalAdd(Request $request): SvrApiResponseResource|JsonResponse
	{
		/** @var  $user - получим авторизированного пользователя */
		$user				= auth()->user();

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
			$animals_list		= DataAnimals::animalsList(999999999, 1, true, $valid_data['filter'], $valid_data);

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
			$animal_add_message						.= 'Добавлено животных: '.$animal_add_result['success'];
		}

		if($animal_add_result['errors'] > 0)
		{
			$animal_add_message						.= '; Ошибок: '.$animal_add_result['errors'];
		}

		$application_data	= (array)DataApplications::applicationData(false, true, false);

		if(is_null($application_data))
		{
			throw new CustomException('Запрошенная заявка не найдена', 200);
		}

		(new SystemUsersNotifications())->notificationCreate('application_animal_add', $application_data['company_id'], false, $application_data);

		$data				= collect([
			'user_id'						=> $user['user_id'],
			'status'						=> true,
			'message'						=> $animal_add_message,
			'response_resource_data'		=> false,
			'response_resource_dictionary'	=> false
		]);

		return new SvrApiResponseResource($data);
	}


	/**
	 * Удаление животного из заявки
	 *
	 * @throws CustomException
	 */
	public function applicationsAnimalDelete(Request $request): SvrApiResponseResource|JsonResponse
	{
		/** @var  $user - получим авторизированного пользователя */
		$user						= auth()->user();
		$valid_data					= $request->validate([
			'animal_id'                 => ['required', 'int']
		]);

		$application_data			= DataApplications::applicationData(false, true, false);
		$animal_data				= DataAnimals::animalData($valid_data['animal_id']);

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

		(new SystemUsersNotifications())->notificationCreate('application_animal_delete', $application_data['company_id'], false, $application_data);

		$data				= collect([
			'user_id'						=> $user['user_id'],
			'status'						=> true,
			'message'						=> 'Животное успешно удалено из текущей заявки',
			'response_resource_data'		=> false,
			'response_resource_dictionary'	=> false
		]);

		return new SvrApiResponseResource($data);
	}


	/**
	 * Список заявок
	 *
	 * @throws CustomException
	 */
	public function applicationsList(Request $request): SvrApiResponseResource|JsonResponse
	{
		/** @var  $user - получим авторизированного пользователя */
		$user						= auth()->user();
		$applications				= new DataApplications();
		$valid_data 				= $request->validate([
			'search'                                 		=> ['string', 'max:255'],
			'filter'                                        => ['array'],
			'filter.application_id'                         => ['int', Rule::exists(DataApplications::class, 'application_id')],
			'filter.user_id'                         		=> ['int', Rule::exists(SystemUsers::class, 'user_id')],
			'filter.company_id'                         	=> ['int', Rule::exists(DataCompanies::class, 'company_id')],
			'filter.company_district_id'					=> ['int', Rule::exists(DirectoryCountriesRegionsDistrict::class, 'district_id')],
			'filter.application_status'                     => ['string', Rule::in(ApplicationStatusEnum::get_option_list())],
			'filter.application_date_create_min'			=> ['date'],
			'filter.application_date_create_max'			=> ['date'],
			'filter.application_date_registration_min'		=> ['date'],
			'filter.application_date_registration_max'		=> ['date']
		]);

		if(!isset($valid_data['filter']))
		{
			$valid_data['filter']			= [];
		}

		if(!isset($valid_data['search']))
		{
			$valid_data['search']			= '';
		}

		$applications_list					= $applications->applicationsList(Config::get('per_page'), Config::get('cur_page'), $valid_data['filter'], $valid_data['search']);
		$users_ids							= [];

		if($applications_list && !is_null($applications_list))
		{
			$users_ids						= array_column($applications_list, 'user_id');
		}

		$data				= collect([
			'application_status'			=> self::DictionaryApplicationStatus(),
			'users_list'					=> SystemUsers::getListUser($users_ids),
			'user_id'						=> $user['user_id'],
			'status'						=> true,
			'message'						=> '',
			'applications_list'				=> $applications_list,
			'response_resource_data'		=> SvrApiApplicationsListResource::class,
			'response_resource_dictionary'	=> SvrApiApplicationsListDictionaryResource::class
		]);

		return new SvrApiResponseResource($data);
	}
}
