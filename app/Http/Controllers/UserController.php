<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\House;
use App\Models\Order;
use App\Services\UserServices\UserService;
use App\Services\OrderService\OrderService;
use App\Services\VerificationService\VerificationService;
use App\Services\PaymentGatewaySimulator;

class UserController extends Controller
{
    protected $userService;
    protected $orderService;
    protected $verificationService;

    public function __construct(
        UserService $userService,
        OrderService $orderService,
        VerificationService $verificationService
    ) {
        $this->userService = $userService;
        $this->orderService = $orderService;
        $this->verificationService = $verificationService;
    }
    
    public function index(){
        $users = User::orderBy("timestamp", "desc")->get();
        return view("users.index", ["users"=>$users]);
    }
    public function show(string $id){
        return redirect()->route('profile.tab.houses', $id);
    }

    public function tabHouses(string $id, Request $request){
        $user = $this->userService->getUserWithRoleHouse($id);
        $currentUser = auth()->user();
        if ($currentUser) {
            $this->authorize('view', $user);
        }
        $isOwner = $currentUser && $currentUser->canEditProfile($user);

        if ($request->ajax() || $request->wantsJson()) {
            return view("users.partials.houses-tab", [
                "user" => $user,
                "houses" => $user->house,
                "isOwner" => $isOwner,
            ])->render();
        }
        return view("users.show", [
            "user" => $user,
            "houses" => $user->house,
            "isOwner" => $isOwner,
        ]);
    }

    public function tabSettings(string $id, Request $request){
        $user = $this->userService->getUserWithRoleHouse($id);
        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(403, 'Требуется авторизация');
        }
        $this->authorize('viewPrivateData', $user);

        $isOwner = true; 

        if ($request->ajax() || $request->wantsJson()) {
            return view("users.partials.settings-tab", [
                "user" => $user,
                "isOwner" => $isOwner,
            ])->render();
        }

        return view("users.show", [
            "user" => $user,
            "houses" => $user->house,
            "isOwner" => $isOwner,
        ]);
    }

    public function tabOrders(string $id, Request $request){
        $user = $this->userService->getUserWithRoleHouse($id);

        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(403, 'Требуется авторизация для просмотра заказов');
        }
        $currentUser->load('roles');

        if ($currentUser->isBanned()) {
            abort(403, 'Заблокированные пользователи не могут просматривать заказы');
        }

        $this->authorize('viewOrders', $user);

        if ($currentUser->isBanned()) {
            abort(403, 'Заблокированные пользователи не могут просматривать заказы');
        }

        $isOwner = $currentUser->canEditProfile($user);

        $ordersAsCustomer = $this->orderService->getOrdersAsCustomer($user->user_id);

        $houseIds = $user->house->pluck('house_id')->toArray();
        $ordersAsOwner = $this->orderService->getOrdersAsOwner($user->user_id);

        $allOrders = $ordersAsCustomer->merge($ordersAsOwner)->unique('order_id')->sortByDesc('created_at');

        $customers = User::whereIn('user_id', $allOrders->pluck('customer_id')->unique()->filter())
            ->orderBy('name')
            ->get();
        
        $owners = User::whereIn('user_id', $allOrders->pluck('house.user_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return view("users.partials.orders-tab", [
                "user" => $user,
                "orders" => $allOrders,
                "ordersAsCustomer" => $ordersAsCustomer,
                "ordersAsOwner" => $ordersAsOwner,
                "isOwner" => $isOwner,
                "customers" => $customers,
                "owners" => $owners,
            ])->render();
        }
        return view("users.show", [
            "user" => $user,
            "houses" => $user->house,
            "isOwner" => $isOwner,
        ]);
    }

    public function create(){
        return view('users.create');
    }

    
    public function showHouses(){
        $houses = House::with('user')->get();
        return view('users.showHouses', ['houses' => $houses]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'required|string|max:100'
        ]);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit()
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $users)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'required|string',
            'sename' => 'required|string|max:100'
        ]);

        $users->update($validated);

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function requestVerification(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Требуется авторизация'
                ], 403);
            }
            return back();
        }

        $canRequest = $this->verificationService->canRequestVerification($user);
        
        if (!$canRequest['can_request']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $canRequest['message']
                ], 400);
            }
            return back()->with('error', $canRequest['message']);
        }

        $this->verificationService->requestVerification($user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Заявка на верификацию успешно подана. Мы рассмотрим её в ближайшее время.'
            ]);
        }

        return back();
    }


    public function balanceTopup(Request $request, PaymentGatewaySimulator $gateway)
    {
        $request->merge([
            'card_number' => preg_replace('/\D/', '', (string) $request->input('card_number')),
        ]);
    
        $data = $request->validate([
            'amount'      => ['required', 'numeric', 'min:1', 'max:10000000'],
            'card_number' => ['required', 'digits:16'],
            'exp_month'   => ['required', 'integer', 'between:1,12'],
            'exp_year'    => ['required', 'integer', 'between:0,99'],
            'cvv'         => ['required', 'digits:3'],
    
            'simulate_mode' => ['nullable', 'in:no_funds'],
        ], [
            'amount.required' => 'Введите сумму пополнения.',
            'amount.numeric'  => 'Сумма должна быть числом.',
            'amount.min'      => 'Минимальная сумма пополнения: 1.',
            'card_number.digits' => 'Номер карты должен содержать 16 цифр.',
            'exp_month.between'  => 'Месяц должен быть от 1 до 12.',
            'exp_year.between'   => 'Год укажите двумя цифрами (например 26).',
            'cvv.digits_between' => 'CVV должен быть 3 или 4 цифры.',
        ]);
    
        $resp = $gateway->charge([
            'amount' => (float)$data['amount'],
            'card_number' => $data['card_number'],
            'exp_month' => (int)$data['exp_month'],
            'exp_year' => (int)$data['exp_year'],
            'cvv' => (string)$data['cvv'],
            'simulate_mode' => $data['simulate_mode'] ?? null,
            'operation'=>'topup'
        ]);
    
        if ($resp['status'] === 'declined') {
            return back()
                ->withErrors([
                    'payment' => $resp['reason_message'] . ' (код: ' . $resp['reason_code'] . ')',
                ])
                ->with('gateway', $resp) 
                ->withInput();
        }
    
        $user = auth()->user();
        if (!$user) abort(403, 'Требуется авторизация');
    
        $user->balance = $user->balance + (float)$data['amount'];
        $user->save();
    
        return back()
            ->with('success', 'Платёж принят. Транзакция: ' . $resp['transaction_id'])
            ->with('gateway', $resp);
    }

        public function balanceSpisanie(Request $request)
    {
       $request->merge([
            'card_number' => preg_replace('/\D/', '', (string) $request->input('card_number')),
        ]);
    
        $data = $request->validate([
            'amount'      => ['required', 'numeric', 'min:1', 'max:10000000'],
            'card_number' => ['required', 'digits:16'],
            'exp_month'   => ['required', 'integer', 'between:1,12'],
            'exp_year'    => ['required', 'integer', 'between:0,99'],
            'cvv'         => ['required', 'digits:3'],
    
            'simulate_mode' => ['nullable'],
        ], [
            'amount.required' => 'Введите сумму списания.',
            'amount.numeric'  => 'Сумма должна быть числом.',
            'amount.min'      => 'Минимальная сумма списания: 1.',
            'card_number.digits' => 'Номер карты должен содержать 16 цифр.',
            'exp_month.between'  => 'Месяц должен быть от 1 до 12.',
            'exp_year.between'   => 'Год укажите двумя цифрами (например 26).',
            'cvv.digits_between' => 'CVV должен быть 3 или 4 цифры.',
        ]);
    
        $resp = $gateway->charge([
            'amount' => (float)$data['amount'],
            'card_number' => $data['card_number'],
            'exp_month' => (int)$data['exp_month'],
            'exp_year' => (int)$data['exp_year'],
            'cvv' => (string)$data['cvv'],
            'simulate_mode' => $data['simulate_mode'] ?? null,
            'operation' => 'spisanie'
        ]);
    
        if ($resp['status'] === 'declined') {
            return back()
                ->withErrors([
                    'payment' => $resp['reason_message'] . ' (код: ' . $resp['reason_code'] . ')',
                ])
                ->with('gateway', $resp) 
                ->withInput();
        }
    
        $user = auth()->user();
        if (!$user) abort(403, 'Требуется авторизация');
        if($user->balance < (float)$data['amount']){
            return back()
                ->withErrors([
                   'Недостаточно средств на балансе',
                ])
                ->with('gateway', $resp) 
                ->withInput();
        
        }
        $user->balance = $user->balance - (float)$data['amount'];
        $user->save();
    
        return back()
            ->with('success', 'Платёж принят. Транзакция: ' . $resp['transaction_id'])
            ->with('gateway', $resp);
    }

}
