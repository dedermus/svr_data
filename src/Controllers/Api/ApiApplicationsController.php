<?php

namespace Svr\Data\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Svr\Core\Models\SystemRoles;
use Svr\Core\Traits\GetDictionary;
use Svr\Data\Models\DataAnimals;
use Svr\Data\Models\DataApplications;
use Svr\Core\Resources\SvrApiResponseResource;

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
		$filterKeys			= ['animal_id'];
		$rules				= $model->getFilterValidationRules($request, $filterKeys);
		$messages			= $model->getFilterValidationMessages($filterKeys);

		$request->validate($rules, $messages);

		$credentials		= $request->only($filterKeys);
		$animal_data		= DataAnimals::animal_data($credentials['animal_id']);
		$application_data	= (array)DataApplications::applicationData($credentials['application_id']);

		if(is_null($application_data))
		{
			return response()->json(['error' => 'Запрошенная заявка не найдена'], 404);
		}

		if(empty($animal_data))
		{
			return response()->json(['error' => 'Животное не найдено'], 404);
		}

		$animals_list = DataAnimals::anim




		dd($animal_data);



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
