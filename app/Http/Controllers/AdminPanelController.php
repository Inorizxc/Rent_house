<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\Order;
use App\Models\House;
use App\enum\OrderStatus;
use App\Services\OrderService\OrderService;

class AdminPanelController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $tableNames = collect(DB::select("
            SELECT name
            FROM sqlite_master
            WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
            ORDER BY name
        "))->pluck('name');

        $selectedTable = $request->get('table');
        if ($selectedTable && !$tableNames->contains($selectedTable)) {
            $selectedTable = null;
        }

        $limit = (int) $request->get('per', 10);
        $limit = max(1, min($limit, 100));

        $page = max((int) $request->get('page', 1), 1);

        $columns = collect();
        if ($selectedTable && $tableNames->contains($selectedTable)) {
            $columns = collect(DB::select("PRAGMA table_info(" . DB::getPdo()->quote($selectedTable) . ")"));
        }

        if ($request->isMethod('post') && $selectedTable) {
            $blocked = ['id', 'created_at', 'updated_at', 'deleted_at'];
            $fillable = $columns
                ->pluck('name')
                ->reject(fn ($column) => in_array($column, $blocked, true))
                ->values()
                ->all();

            $payload = $request->only($fillable);
            foreach ($payload as $key => $value) {
                if ($value === '') {
                    $payload[$key] = null;
                }
            }

            if (!empty($payload)) {
                DB::table($selectedTable)->insert($payload);

                return redirect()->route('admin.panel')
                    ->withInput(['table' => $selectedTable])
                    ->with('status', "Запись добавлена в «{$selectedTable}»");
            }
        }
        $rows = collect();
        $total = 0;
        $pages = 1;

        if ($selectedTable) {
            $total = DB::table($selectedTable)->count();
            $pages = max((int) ceil($total / $limit), 1);
            $page = min($page, $pages);

            $rows = DB::table($selectedTable)
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();
        }

        return view('admin.panel', [
            'tables' => $tableNames,
            'selectedTable' => $selectedTable,
            'columns' => $columns,
            'rows' => $rows,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
        ]);
    }

    public function delete(Request $request, $table, $id)
    {
        try {
            $primaryKey = $this->getPrimaryKey($table);
            if (!$primaryKey) {
                return back()->with('error', 'Не удалось определить первичный ключ таблицы');
            }
            DB::table($table)->where($primaryKey, $id)->delete();

            return back()->with('status', 'Запись успешно удалена');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }

    private function getPrimaryKey($table)
    {
        $tableNames = collect(DB::select("
            SELECT name
            FROM sqlite_master
            WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
        "))->pluck('name');
        
        if (!$tableNames->contains($table)) {
            return null;
        }
        
        $info = DB::select("PRAGMA table_info(" . DB::getPdo()->quote($table) . ")");
        foreach ($info as $column) {
            if ($column->pk == 1) {
                return $column->name;
            }
        }
        $columns = collect($info)->pluck('name')->toArray();
        if (in_array('id', $columns)) {
            return 'id';
        }
        if (in_array($table . '_id', $columns)) {
            return $table . '_id';
        }
        return null;
    }

    public function chats(Request $request)
    {
        $limit = (int) $request->get('per', 20);
        $limit = max(1, min($limit, 100));

        $page = max((int) $request->get('page', 1), 1);

        $userFilter = $request->get('user_id') ?: null;
        $dealerFilter = $request->get('dealer_id') ?: null;

        $query = Chat::with(['user', 'rentDealer'])
            ->withCount('message');

        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        if ($dealerFilter) {
            $query->where('rent_dealer_id', $dealerFilter);
        }

        $total = $query->count();
        $pages = max((int) ceil($total / $limit), 1);
        $page = min($page, $pages);

        $chats = $query->orderBy('updated_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function($chat) {
                $chat->last_message = Message::where('chat_id', $chat->chat_id)
                    ->latest('created_at')
                    ->first();
                return $chat;
            });

        $userIds = collect();
        $userIds = $userIds->merge(Chat::pluck('user_id'));
        $userIds = $userIds->merge(Chat::pluck('rent_dealer_id'));
        $userIds = $userIds->unique()->filter();
        
        $users = User::whereIn('user_id', $userIds)
            ->orderBy('name')
            ->get();

        return view('admin.chats', [
            'chats' => $chats,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
            'userFilter' => $userFilter,
            'dealerFilter' => $dealerFilter,
            'users' => $users,
        ]);
    }

    public function chatShow($chatId)
    {
        $chat = Chat::with(['user', 'rentDealer'])->findOrFail($chatId);
        
        $messages = Message::where('chat_id', $chatId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.chat-show', [
            'chat' => $chat,
            'messages' => $messages,
        ]);
    }

    public function orders(Request $request)
    {
        $limit = (int) $request->get('per', 20);
        $limit = max(1, min($limit, 100));

        $page = max((int) $request->get('page', 1), 1);

        $statusFilter = $request->get('status');
        $customerFilter = $request->get('customer_id');
        $ownerFilter = $request->get('owner_id');
        
        $query = Order::with(['house', 'customer', 'house.user']);

        if ($statusFilter) {
            try {
                $status = OrderStatus::from($statusFilter);
                $query->where('order_status', $status);
            } catch (\ValueError $e) {
            }
        }

        if ($customerFilter) {
            $query->where('customer_id', $customerFilter);
        }

        if ($ownerFilter) {
            $query->whereHas('house', function($q) use ($ownerFilter) {
                $q->where('user_id', $ownerFilter);
            });
        }

        $total = $query->count();
        $pages = max((int) ceil($total / $limit), 1);
        $page = min($page, $pages);

        $orders = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        $customers = User::whereHas('ordersAsCustomer')->orderBy('name')->get();
        $owners = User::whereHas('house', function($q) {
            $q->whereHas('order');
        })->orderBy('name')->get();

        return view('admin.orders', [
            'orders' => $orders,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
            'statusFilter' => $statusFilter,
            'customerFilter' => $customerFilter,
            'ownerFilter' => $ownerFilter,
            'statuses' => OrderStatus::cases(),
            'customers' => $customers,
            'owners' => $owners,
        ]);
    }

    public function orderShow($orderId)
    {
        $order = Order::with(['house.user', 'house.photo', 'customer'])
            ->findOrFail($orderId);

        return view('admin.order-show', [
            'order' => $order,
        ]);
    }

    public function refundOrder($orderId)
    {
        $order = Order::with(['house.user', 'customer'])->findOrFail($orderId);

        if ($order->isRefunded()) {
            return redirect()->back();
        }

        if ($order->order_status === OrderStatus::REFUND) {
            $success = $this->orderService->refundOrder($order);

            if ($success) {
                return redirect()->back();
            } else {
                return redirect()->back();
            }
        }

        if ($order->order_status === OrderStatus::CANCELLED) {
            return redirect()->back();
        }
        $order->order_status = OrderStatus::REFUND;
        $order->save();
        $success = $this->orderService->refundOrder($order);

        if ($success) {
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }
}

