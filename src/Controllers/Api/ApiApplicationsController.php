<?php

namespace Svr\Data\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthInfoSystemUsersResource;

use Svr\Data\Models\DataApplications;

use Svr\Core\Resources\SvrApiResponseResource;

use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

class ApiApplicationsController extends Controller
{
	// TODO:: порешать что-то со статическими справочниками (где хранить, как доставать и т.п.)
	public	$application_status						= array(
		'created'		=> array(
			'status_slug'		=> 'created',
			'status_name'		=> 'Создано',
		),
		'prepared'		=> array(
			'status_slug'		=> 'prepared',
			'status_name'		=> 'Подготовлено'
		),
		'sent'		=> array(
			'status_slug'		=> 'sent',
			'status_name'		=> 'Отправлено'
		),
		'complete_full'		=> array(
			'status_slug'		=> 'complete_full',
			'status_name'		=> 'Завершено полностью'
		),
		'complete_partial'		=> array(
			'status_slug'		=> 'complete_partial',
			'status_name'		=> 'Завершено частично'
		),
		'finished'		=> array(
			'status_slug'		=> 'finished',
			'status_name'		=> 'Отработано'
		)
	);




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
			//TODO переписать на нормальный структурированный вид после того как сделаем нормальный конструктор вывода
			return response()->json(['error' => 'Запрошенная заявка не найдена'], 404);
		}

        $data				= collect([
			'application_data'				=> $application_data,
			'application_status'			=> $this->application_status,
            'user_id'						=> $user['user_id'],
            'status'						=> true,
            'message'						=> '',
            'response_resource_data'		=> 'Svr\Data\Resources\SvrApiApplicationDataResource',
            'response_resource_dictionary'	=> 'Svr\Data\Resources\SvrApiApplicationDataDictionaryResource',
            'pagination'					=> [
                'total_records'					=> 1,
                'cur_page'						=> 1,
                'per_page'						=> 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }
}
