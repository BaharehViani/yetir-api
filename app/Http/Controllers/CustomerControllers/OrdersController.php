<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class OrdersController extends Controller
{
    public function index(Request $request) {

        $request->validate([
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validStatuses = ['waiting_for_pickup', 'in_delivery', 'delivered'];

        $statusArray = $request->input('status') ? explode(',', $request->input('status')) : null;

       if ($statusArray) {
            foreach ($statusArray as $status) {
                if (!in_array($status, $validStatuses)) {
                    return response([
                        'status' => 'FAILED',
                        'message' => 'INVALID_STATUS_VALUE',
                    ])->setStatusCode(422);
                }
            }
        }
        
        $query = $request->user()->customerorders();
    
        if ($statusArray) {
            $query->whereIn('orders.status', $statusArray);
        }
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate || $endDate) {
            $query->whereBetween('orders.updated_at', [$startDate, $endDate]);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        if ($orders->isEmpty()) {
            return response([
                'status' => 'FAILED',
                'message' => 'NO_ACTIVE_ORDERS',
            ])->setStatusCode(404);
        }
    
        return $orders;
    }  

    public function show(Request $request, $id) {
        return $request->user()->customerorders()->find($id) ?: response([
            'status' => 'FAILED',
            'message' => 'ORDER_REQUEST_NOT_FOUND'
        ])->setStatusCode(404);
    }

    public function ordersType(Request $request) {
        $orders = $request->user()->customerorders()
            ->where('orders.status', 'delivered')
            ->with('orderRequest')
            ->get();
    
        $totalOrders = $orders->count();
        if ($totalOrders === 0) {
            return [
                'total_sent_orders' => 0,
                'packages' => [
                    'pocket' => 0,
                    'smallBox' => 0,
                    'mediumBox' => 0,
                    'largeBox' => 0
                ]
            ];
        }

        $pocketOrders = $orders->filter(function ($order) {
            return $order->orderRequest->type === 'پاکت'; 
        });
        $smallBoxOrders = $orders->filter(function ($order) {
            return $order->orderRequest->type === 'جعبه کوچک';
        });
        $mediumBoxOrders = $orders->filter(function ($order) {
            return $order->orderRequest->type === 'جعبه متوسط';
        });
        $largeBoxOrders = $orders->filter(function ($order) {
            return $order->orderRequest->type === 'جعبه بزرگ';
        });
        
        $pocketPercentage = ($pocketOrders->count() / $totalOrders) * 100;
        $smallBoxPercentage = ($smallBoxOrders->count() / $totalOrders) * 100;
        $mediumBoxPercentage = ($mediumBoxOrders->count() / $totalOrders) * 100;
        $largeBoxPercentage = ($largeBoxOrders->count() / $totalOrders) * 100;
        
        return [
            'total_sent_orders' => $totalOrders,
            'packages' => [
                'pocket' => $pocketPercentage,
                'smallBox' => $smallBoxPercentage,
                'mediumBox' => $mediumBoxPercentage,
                'largeBox' => $largeBoxPercentage
            ]
        ];
    }

    public function ordersOrderRequestsStats(Request $request) {
        $currentJalaliYear = Jalalian::fromCarbon(Carbon::now('Asia/Tehran'))->getYear();

        $orderRequests = $request->user()->orderRequests()->get()->groupBy(function ($orderRequest) {
            return Jalalian::fromCarbon(Carbon::parse($orderRequest->created_at)->setTimezone('Asia/Tehran'))->getMonth();
        });

        $orders = $request->user()->customerOrders()->get()->groupBy(function ($order) {
            return Jalalian::fromCarbon(Carbon::parse($order->created_at)->setTimezone('Asia/Tehran'))->getMonth();
        });

        $stats = collect(range(1, 12))->map(function ($month) use ($orderRequests, $orders) {
            return [
                'month' => $month, 
                'requests' => isset($orderRequests[$month]) ? $orderRequests[$month]->count() : 0,
                'delivered' => isset($orders[$month]) ? $orders[$month]->where('status', 'delivered')->count() : 0,
                'canceled' => isset($orders[$month]) ? $orders[$month]->where('status', 'canceled')->count() : 0,
            ];
        });
        return $stats;
    }

    public function monthlySpendingStats(Request $request) {
        $invoices = $request->user()->invoices()
            ->where('status', 'paid') 
            ->get()
            ->groupBy(function ($invoice) {
                return Jalalian::fromCarbon(Carbon::parse($invoice->created_at)->setTimezone('Asia/Tehran'))->getMonth();
            });
    
        $stats = collect(range(1, 12))->map(function ($month) use ($invoices) {
            return [
                'month' => $month,
                'total_spent' => $invoices->has($month) ? $invoices[$month]->sum('grand_total') : 0,
            ];
        });
        return $stats;
    }
}
