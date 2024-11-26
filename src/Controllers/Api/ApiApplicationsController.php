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

use Svr\Core\Resources\SvrApiResponseResource;

use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

class ApiApplicationsController extends Controller
{
    /**
     * Создание новой записи.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $model = new SystemUsers();
        $record = $model->userCreate($request);
        return response()->json($record, 201);
    }

    /**
     * Обновление существующей записи.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $record = new SystemUsers();
        $request->setMethod('PUT');
        $record->userUpdate($request);
        return response()->json($record);
    }

    /**
     * Получение списка записей с пагинацией.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15); // Количество записей на странице по умолчанию
        $records = SystemUsers::paginate($perPage);
        return response()->json($records);
    }

    /**
     * Получение информации о пользователе.
     *
     * @return SvrApiResponseResource|JsonResponse
     */
    public function applicationsData(Request $request): SvrApiResponseResource|JsonResponse
    {
        /** @var  $user - получим авторизированного пользователя */
        $user = auth()->user();
        //Получим токен текущего пользователья
//        $token = $request->bearerToken();
        //Получим данные о токене из базы
//        $token_data = SystemUsersToken::where('token_value', '=', $token)->first()->toArray();
        //запомнили participation_id
//        $participation_id = $token_data['participation_id'];
        //получили привязки пользователя
//        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);
        //собрали данные для передачи в ресурс
        $data = collect([
//            'user' => $user,
            'user_id' => $user['user_id'],
//            'user_participation_info' => $user_participation_info,
//            'company_data' => DataCompaniesLocations::find($user_participation_info['company_location_id'])->company,
//            'region_data' => DirectoryCountriesRegion::find($user_participation_info['region_id']),
//            'district_data' => DirectoryCountriesRegionsDistrict::find($user_participation_info['district_id']),
//            'role_data' => SystemRoles::find($user_participation_info['role_id']),
//            'participation_id' => $participation_id,
            'status' => true,
            'message' => '',
            'response_resource_data' => 'Svr\Core\Resources\SvrApiAuthInfoResource',
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }


    public function appicationsFilterRestrictions()
    {

    }


}
