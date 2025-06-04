<?php

namespace App\Http\Controllers\Admin\DeliveryMan;

use Exception;
use App\Models\Order;
use Illuminate\View\View;
use App\Mail\DmSuspendMail;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Mail\DmSelfRegistration;
use App\Traits\NotificationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use App\Models\DisbursementDetails;
use App\Services\DeliveryManService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use App\Exports\DeliveryManListExport;
use Illuminate\Foundation\Application;
use App\Exports\DeliveryManReviewExport;
use App\Http\Controllers\BaseController;
use App\Exports\DeliveryManEarningExport;
use App\Exports\DisbursementHistoryExport;
use Illuminate\Database\Eloquent\Collection;
use App\Exports\SingleDeliveryManReviewExport;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\ExportFileNames\Admin\DeliveryMan;
use App\Http\Requests\Admin\DeliveryManAddRequest;
use App\Http\Requests\Admin\DeliveryManUpdateRequest;
use App\Contracts\Repositories\ZoneRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Contracts\Repositories\DmReviewRepositoryInterface;
use App\Contracts\Repositories\UserInfoRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ConversationRepositoryInterface;
use App\Enums\ViewPaths\Admin\DeliveryMan as DeliveryManViewPath;
use App\Contracts\Repositories\OrderTransactionRepositoryInterface;
use App\Contracts\Repositories\UserNotificationRepositoryInterface;

class DeliveryManController extends BaseController
{
    use NotificationTrait;
    public function __construct(
        protected DeliveryManRepositoryInterface $deliveryManRepo,
        protected ZoneRepositoryInterface $zoneRepo,
        protected TranslationRepositoryInterface $translationRepo,
        protected DmReviewRepositoryInterface $dmReviewRepo,
        protected UserInfoRepositoryInterface $userInfoRepo,
        protected ConversationRepositoryInterface $conversationRepo,
        protected MessageRepositoryInterface $messageRepo,
        protected DeliveryManService $deliveryManService,
    )
    {
    }

    public function index(?Request $request): View|Collection|LengthAwarePaginator|null
    {
        return $this->getListView($request);
    }
    private function getListView(Request $request): View
    {
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getFilterWiseListWhere(
            zoneId: $zoneId,
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise','application_status' => 'approved'],
            additionalFilter: $request['filter'],
            jobType: $request['job_type'],
            relations: ['zone','wallet'],
            dataLimit: config('default_pagination')
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id'=>$zoneId]) : null;
        return view(DeliveryManViewPath::LIST[VIEW], compact('deliveryMen','zone'));
    }

    public function getAddView(): View
    {
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(DeliveryManViewPath::ADD[VIEW], compact('language','defaultLang'));
    }

    public function getNewDeliveryManView(Request $request): View
    {
        $searchBy = $request->query('search_by');
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getZoneWiseListWhere(
            zoneId: $zoneId,
            searchValue: $searchBy,
            filters: ['type' => 'zone_wise','application_status' => 'pending'],
            relations: ['zone'],
            dataLimit: config('default_pagination')
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id'=>$zoneId]) : null;
        return view(DeliveryManViewPath::NEW[VIEW], compact('deliveryMen','zone','searchBy'));
    }

    public function getDeniedDeliveryManView(Request $request): View
    {
        $searchBy = $request->query('search_by');
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getZoneWiseListWhere(
            zoneId: $zoneId,
            searchValue: $searchBy,
            filters: ['type' => 'zone_wise','application_status' => 'denied'],
            relations: ['zone'],
            dataLimit: config('default_pagination')
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id'=>$zoneId]) : null;
        return view(DeliveryManViewPath::DENY[VIEW], compact('deliveryMen','zone','searchBy'));
    }

    public function getSearchList(Request $request): JsonResponse
    {
        $deliveryMen = $this->deliveryManRepo->getListWhere(
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise','application_status' => 'approved'],
        );
        return response()->json([
            'view'=>view(DeliveryManViewPath::SEARCH[VIEW],compact('deliveryMen'))->render(),
            'count'=>$deliveryMen->count()
        ]);
    }

    public function getActiveSearchList(Request $request): JsonResponse
    {
        $deliveryMen = $this->deliveryManRepo->getFilterWiseListWhere(
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise','status' => 1],
        );
        return response()->json([
            'dm'=>$deliveryMen
        ]);
    }

    public function add(DeliveryManAddRequest $request): Application|Redirector|RedirectResponse
    {
        $this->deliveryManRepo->add(data: $this->deliveryManService->getAddData(request: $request));
        Toastr::success(translate('messages.deliveryman_added_successfully'));
        return back();
    }

    public function getUpdateView(string|int $id): View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWithoutGlobalScopeWhere(params: ['id' => $id]);
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(DeliveryManViewPath::UPDATE[VIEW], compact('deliveryMan','language','defaultLang'));
    }

    public function update(DeliveryManUpdateRequest $request, $id): Application|Redirector|RedirectResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id]);

        $deliveryMan = $this->deliveryManRepo->update(id: $id ,data: $this->deliveryManService->getUpdateData(request: $request, deliveryMan: $deliveryMan));
        if($deliveryMan->userinfo) {
            $this->userInfoRepo->update(id: $deliveryMan->userinfo->id,data: [
                'f_name' => $deliveryMan->f_name,
                'l_name' => $deliveryMan->l_name,
                'email' => $deliveryMan->email,
                'image' => $deliveryMan->image,
            ]);
        }

        Toastr::success(translate('messages.deliveryman_updated_successfully'));
        return back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->deliveryManRepo->delete(id: $request['id']);
        Toastr::success(translate('messages.deliveryman_deleted_successfully'));
        return back();
    }

    public function updateStatus(Request $request,UserNotificationRepositoryInterface $notificationRepo): RedirectResponse
    {
        $deliveryMan = $this->deliveryManRepo->update(id: $request['id'] ,data: ['status'=>$request['status']]);


            if($request['status'] == 0)
            {   $deliveryMan->auth_token = null;

                if(isset($deliveryMan->fcm_token) &&  Helpers::getNotificationStatusData('deliveryman','deliveryman_account_block','push_notification_status'))
                {
                    $data = [
                        'title' => translate('messages.suspended'),
                        'description' => translate('messages.your_account_has_been_suspended'),
                        'order_id' => '',
                        'image' => '',
                        'type'=> 'block'
                    ];
                    $this->sendPushNotificationToDevice($deliveryMan->fcm_token, $data);

                    $notificationRepo->add([
                        'data'=> json_encode($data),
                        'delivery_man_id'=>$deliveryMan->id,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }
                else{
                    Toastr::warning(translate('messages.push_notification_failed'));
                }
            } else{
                if( Helpers::getNotificationStatusData('deliveryman','deliveryman_account_unblock','push_notification_status') && isset($deliveryMan->fcm_token))
                {
                    $data = [
                        'title' => translate('messages.Account_activation'),
                        'description' => translate('messages.your_account_has_been_activated'),
                        'order_id' => '',
                        'image' => '',
                        'type'=> 'unblock'
                    ];
                    Helpers::send_push_notif_to_device($deliveryMan->fcm_token, $data);

                    DB::table('user_notifications')->insert([
                        'data'=> json_encode($data),
                        'delivery_man_id'=>$deliveryMan->id,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }
            }
            try {
                if (config('mail.status') && getWebConfigStatus('suspend_mail_status_dm') == '1' &&  $request['status'] == 0 && Helpers::getNotificationStatusData('deliveryman','deliveryman_account_block','mail_status') ) {
                    Mail::to($deliveryMan['email'])->send(new DmSuspendMail('suspend',$deliveryMan['f_name']));
                }
                elseif(config('mail.status') && getWebConfigStatus('unsuspend_mail_status_dm') == '1' &&  $request['status'] != 0 && Helpers::getNotificationStatusData('deliveryman','deliveryman_account_unblock','mail_status')){
                    Mail::to($deliveryMan['email'])->send(new DmSuspendMail('unsuspend',$deliveryMan['f_name']));
                }
            }  catch (Exception) {
                Toastr::warning(translate('messages.failed_to_send_mail'));
            }

        Toastr::success(translate('messages.deliveryman_status_updated'));
        return back();
    }
    public function updateEarning(Request $request): RedirectResponse
    {
        $this->deliveryManRepo->update(id: $request['id'] ,data: ['earning'=>$request['status']]);
        Toastr::success(translate('messages.deliveryman_type_updated'));
        return back();
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $zoneId = $request->query('zone_id', 'all');
        $deliveryMen = $this->deliveryManRepo->getZoneWiseListWhere(
            zoneId: $zoneId,
            searchValue: $request['search'],
            filters: ['type' => 'zone_wise','application_status' => 'approved'],
            relations: ['zone']
        );
        $zone = is_numeric($zoneId) ? $this->zoneRepo->getFirstWhere(params: ['id'=>$zoneId]) : null;

        $data = [
            'delivery_men'=>$deliveryMen,
            'search'=>$request->search??null,
            'zone'=>is_numeric($zoneId)?$zone['name']:null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new DeliveryManListExport($data), DeliveryMan::EXPORT_XLSX);
        }
        return Excel::download(new DeliveryManListExport($data), DeliveryMan::EXPORT_CSV);
    }

    public function getReviewListView(Request $request): View
    {
        $filter=$request['deliveryman_id'] && is_numeric($request['deliveryman_id'])  ?  ['delivery_man_id' => $request['deliveryman_id'] ] : [];
        $orderBy=$request['order_by'] && isset($request['order_by']) && in_array($request['order_by'],['asc','desc']) ?  ['col' => 'rating' ,'type' => $request['order_by'] ] : [];
        $reviews = $this->dmReviewRepo->getListWhereOrder(searchValue: $request['search'],
        filters:$filter ,relations: ['delivery_man','customer','order'],dataLimit: config('default_pagination') ,orderBy: $orderBy);

        return view(DeliveryManViewPath::REVIEW_LIST[VIEW],compact('reviews'));
    }

    public function getReviewSearchList(Request $request): JsonResponse
    {
        $reviews = $this->dmReviewRepo->getListWhere(searchValue: $request['search'],relations: ['delivery_man','customer']);

        return response()->json([
            'view' => view(DeliveryManViewPath::REVIEW_SEARCH_LIST[VIEW], compact('reviews'))->render(),
            'count' => $reviews->count()
        ]);
    }

    public function getAllReviewExportList(Request $request): BinaryFileResponse
    {
        $reviews = $this->dmReviewRepo->getListWhere(searchValue: $request['search'],relations: ['delivery_man','customer']);
        $data = [
            'reviews'=>$reviews,
            'search'=>$request->search??null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new DeliveryManReviewExport($data), DeliveryMan::REVIEW_EXPORT_XLSX);
        }
        return Excel::download(new DeliveryManReviewExport($data), DeliveryMan::EXPORT_CSV);

    }

    public function updateReviewStatus(Request $request): RedirectResponse
    {
        $this->dmReviewRepo->update(id: $request['id'] ,data: ['status'=>$request['status']]);
        Toastr::success(translate('messages.review_visibility_updated'));
        return back();
    }

    public function getReviewExportList(Request $request): BinaryFileResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise','id' => $request['id']], relations: ['reviews']);
        $reviews = $this->dmReviewRepo->getListWhere(searchValue: $request['search'],filters: ['delivery_man_id' => $request['id']]);

        $data = [
            'dm'=>$deliveryMan,
            'reviews'=>$reviews,
            'search'=>$request->search??null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new SingleDeliveryManReviewExport($data), DeliveryMan::REVIEW_EXPORT_XLSX);
        }
        return Excel::download(new SingleDeliveryManReviewExport($data), DeliveryMan::EXPORT_CSV);

    }

    public function getPreview(Request $request, int|string $id, string $tab='info'): View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise','id' => $id], relations: ['reviews']);
        if($tab == 'info')
        {
            $reviews = $this->dmReviewRepo->getListWhere(filters: ['delivery_man_id'=>$id], dataLimit: config('default_pagination'));
            return view(DeliveryManViewPath::INFO[VIEW], compact('deliveryMan', 'reviews'));
        }
        else if($tab == 'transaction')
        {
            $date = $request->query('date');
            return view(DeliveryManViewPath::TRANSACTION[VIEW], compact('deliveryMan', 'date'));
        }
        else if ($tab == 'order_list') {
            $order_lists = Order::where('delivery_man_id', $deliveryMan->id)->paginate(config('default_pagination'));
            return view(DeliveryManViewPath::ORDER_LIST[VIEW], compact('deliveryMan', 'order_lists'));
        }

        else if ($tab == 'disbursement') {
            $key = explode(' ', $request['search']);
            $disbursements=DisbursementDetails::where('delivery_man_id', $deliveryMan->id)
                ->when(isset($key), function ($q) use ($key){
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('disbursement_id', 'like', "%{$value}%")
                                ->orWhere('status', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()->paginate(config('default_pagination'));
            return view('admin-views.delivery-man.view.disbursement', compact('deliveryMan','disbursements'));
        }

        $user = $this->userInfoRepo->getFirstWhere(params: ['deliveryman_id' => $id]);
        if($user){
            $conversations = $this->conversationRepo->getListWithScope(relations: ['sender', 'receiver', 'last_message'],dataLimit: 8, scopes: ['WhereUser' => [$user['id']]] , conversation_with:$request?->conversation_with ?? 'customer' );
        }else{
            $conversations = [];
        }

        return view(DeliveryManViewPath::CONVERSATION[VIEW], compact('conversations','deliveryMan'));

    }

    public function getEarningListExport(Request $request, OrderTransactionRepositoryInterface $orderTransactionRepo): BinaryFileResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['type' => 'zone_wise','id' => $request['id']], relations: ['reviews']);
        $earnings=$orderTransactionRepo->getDmEarningList(request: $request);

        $data = [
            'dm'=>$deliveryMan,
            'earnings'=>$earnings,
            'date'=>$request->date??null,
        ];

        if ($request['type'] == 'excel') {
            return Excel::download(new DeliveryManEarningExport($data), 'DeliveryManEarnings.xlsx');
        }
        return Excel::download(new DeliveryManEarningExport($data), 'DeliveryManEarnings.csv');

    }

    public function getDropdownList(Request $request): JsonResponse
    {
        $data = $this->deliveryManRepo->getDropdownList(request: $request);
        return response()->json($data);
    }

    public function getAccountData(Request $request): JsonResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['id']]);
        $wallet = $deliveryMan['wallet'];
        $cashInHand = 0;
        $balance = 0;

        if($wallet)
        {
            $cashInHand = $wallet->collected_cash;
            $balance = round($wallet->total_earning - $wallet->total_withdrawn - $wallet->pending_withdraw, config('round_up_to_digit'));
        }
        return response()->json(['cash_in_hand'=>$cashInHand, 'earning_balance'=>$balance]);

    }

    public function getConversationList(Request $request): JsonResponse
    {
        // dd($request->all());
        $user = $this->userInfoRepo->getFirstWhere(params: ['deliveryman_id' => $request['user_id']]);
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['user_id']]);
        if($user){
            $conversations = $this->conversationRepo->getDmConversationList(request: $request,dataLimit: 8 ,user: $user->id);
        }else{
            $conversations = [];
        }
        $view = view(DeliveryManViewPath::CONVERSATION_LIST[VIEW],compact('conversations','deliveryMan'))->render();

        return response()->json(['html'=>$view]);

    }

    public function getConversationView($conversation_id,$user_id): JsonResponse
    {
        $conversations = $this->messageRepo->getListWhere(filters: ['conversation_id' => $conversation_id]);
        $conversation = $this->conversationRepo->getFirstWhere(params: ['id'=>$conversation_id],relations: ['receiver','sender']);
        $receiver = $conversation['receiver'];
        $user = $this->userInfoRepo->getFirstWhere(params: ['id'=>$user_id]);
        return response()->json([
            'view' => view(DeliveryManViewPath::CONVERSATIONS[VIEW], compact('conversations', 'user', 'receiver'))->render()
        ]);
    }

    public function updateApplication(Request $request): RedirectResponse
    {
        $deliveryMan = $this->deliveryManRepo->update(id: $request['id'] ,data: ['application_status'=>$request['status']]);
        if($request['status'] == 'approved') $this->deliveryManRepo->update(id: $request['id'] ,data: ['status'=>1]);
        try{
            if($request['status']=='approved'){

                $mail_status = getWebConfigStatus('approve_mail_status_dm');
                if(config('mail.status') && $mail_status == '1'  && Helpers::getNotificationStatusData('deliveryman','deliveryman_registration_approval','mail_status')){
                    Mail::to($deliveryMan->email)->send(new DmSelfRegistration('approved',$deliveryMan->f_name.' '.$deliveryMan->l_name));
                }
            }else{

                $mail_status = getWebConfigStatus('deny_mail_status_dm');
                if(config('mail.status') && $mail_status == '1' && Helpers::getNotificationStatusData('deliveryman','deliveryman_registration_deny','mail_status')){
                    Mail::to($deliveryMan->email)->send(new DmSelfRegistration('denied', $deliveryMan->f_name.' '.$deliveryMan->l_name));
                }
            }
        }catch(Exception $ex){
            info($ex->getMessage());
        }
        Toastr::success(translate('messages.application_status_updated_successfully'));
        return back();
    }

    public function disbursement_export(Request $request,$id,$type)
    {
        $key = explode(' ', $request['search']);

        $dm= \App\Models\DeliveryMan::find($id);
        $disbursements=DisbursementDetails::where('delivery_man_id', $dm->id)
            ->when(isset($key), function ($q) use ($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('disbursement_id', 'like', "%{$value}%")
                            ->orWhere('status', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->get();
        $data = [
            'disbursements'=>$disbursements,
            'search'=>$request->search??null,
            'delivery_man'=>$dm->f_name.' '.$dm->l_name,
            'type'=>'dm',
        ];

        if ($request->type == 'excel') {
            return Excel::download(new DisbursementHistoryExport($data), 'Disbursementlist.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new DisbursementHistoryExport($data), 'Disbursementlist.csv');
        }
    }
}
