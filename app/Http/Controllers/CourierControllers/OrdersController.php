<?php

namespace App\Http\Controllers\CourierControllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function create(Request $request) {
        $request->validate([
            'order_request_id' => 'required|ulid|exists:order_requests,id',
        ]);
        
        if($request->user()->courierinfo()->first()->orders()) {
            $activeOrder = $request->user()->courierinfo()->first()->orders()->whereIn('status', ['waiting_for_pickup', 'in_delivery'])->first();
            if ($activeOrder) {
                return response([
                    'status' => 'FAILED',
                    'message' => 'COURIER_HAS_ACTIVE_ORDER'
                ])->setStatusCode(400);
            }
        }

        $order_request = OrderRequest::find($request->input('order_request_id'));
        if (!$order_request) {
            return response([
                'status' => 'FAILED',
                'message' => 'REQUEST_NOT_FOUND'
            ])->setStatusCode(404);
        }

        if ($order_request->status !== 'pending') {
            return response([
                'status' => 'FAILED',
                'message' => 'REQUEST_CANNOT_BE_ACCEPTED'
            ])->setStatusCode(400);
        }

        $new_order = new Order;
        $new_order->order_request_id = $order_request->id;
        $new_order->courier_id = $request->user()->courierinfo()->first()->id;
        $new_order->status = 'waiting_for_pickup';
        $new_order->code = (Order::max('code') ?? 999) + 1;
        $new_order->save();
        
        $order_request->status = 'accepted';
        $order_request->save();

        $new_invoice = new Invoice;
        $new_invoice->order_id = $new_order->id;
        $new_invoice->user_id = $order_request->user_id;
        $new_invoice->total = $order_request->cost;
        $new_invoice->tax = $order_request->cost / 10;
        $new_invoice->grand_total = $new_invoice->total + $new_invoice->tax;
        $new_invoice->status = 'pending';
        $new_invoice->save();

        return[
            'status' => 'SUCCESSFUL',
            'message' => 'REQUEST_ACCEPTED_ORDER_CREATED_INVOICE_CREATED_SUCCESSFULY',
            'payload' => [
                'order' => $new_order
            ]
        ];
    }

    public function update(Request $request, $id) {

        $request->validate([
            'status' => 'required|string|in:waiting_for_pickup,in_delivery,delivered,canceled',
        ]);  

        $order = Order::where('courier_id', $request->user()->courierinfo()->first()->id)->find($id);
        
        if (!$order || $order->status === 'canceled') {
            return response([
                'status' => 'FAILED',
                'message' => 'ORDER_NOT_FOUND'
            ])->setStatusCode(404);
        }

        if ($request->input('status') === 'canceled') {
            $pre_orderRequest = $order->orderRequest;
            $pre_orderRequest->status = 'pending';
            $pre_orderRequest->updated_at = now();
            $pre_orderRequest->save();
            $order->canceled_at = now();
        }

        $order->status = $request->input('status');
        $order->updated_at = now();
        $order->save();

        return [
            'status' => 'SUCCESSFUL',
            'message' => 'ORDER_UPDATED_SUCCESSFULLY',
        ];      
    }

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

        $query = $request->user()->courierorders();

        if ($statusArray) {
            $query->whereIn('status', $statusArray);
        }
    
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate || $endDate) {
            $query->whereBetween('orders.updated_at', [$startDate, $endDate]);
        }

        $orders = $query->orderBy('updated_at', 'desc')->get();
    
        if ($orders->isEmpty()) {
            return response([
                'status' => 'FAILED',
                'message' => 'NO_ACTIVE_ORDERS',
            ])->setStatusCode(404);
        }
        return $orders;
    }  
    
    public function activeOrder(Request $request) {
        return $request->user()->courierorders()->whereIn('status', ['waiting_for_pickup', 'in_delivery'])->first() ?: null;
    } 
    
    public function monthlyIncome(Request $request) {
        $orders = $request->user()->courierorders()
        ->where('status', 'delivered')
        ->with('orderRequest') // لود کردن `orderRequest` برای دسترسی به هزینه‌ها
        ->get()
        ->groupBy(function ($order) {
            return CalendarUtils::strftime('Y-m', strtotime($order->updated_at)); // دسته‌بندی بر اساس سال-ماه شمسی
        })
        ->map(function ($group, $key) {
            list($year, $month) = explode('-', $key); // استخراج سال و ماه شمسی
            return [
                'jalali_year' => (int) $year,
                'jalali_month' => (int) $month,
                'total_income' => $group->sum(fn($order) => $order->orderRequest->cost),
            ];
        })
        ->values(); // تبدیل به آرایه مرتب‌شده
        return $orders;
    }

    public function ordersType(Request $request) {
        // گرفتن تمام سفارشات تحویل داده شده
        $orders = $request->user()->courierorders()
            ->where('status', 'delivered')
            ->with('orderRequest')
            ->get();
    
        // تعداد کل سفارشات تحویل داده شده
        $totalOrders = $orders->count();
        // در صورتی که هیچ سفارشی وجود نداشته باشد
        if ($totalOrders === 0) {
            return [
                'total_delivered_orders' => 0,
                'packages' => [
                    'pocket' => 0,
                    'smallBox' => 0,
                    'mediumBox' => 0,
                    'largeBox' => 0
                ]
            ];
        }
        // گروه‌بندی سفارشات بر اساس نوع بسته
        $pocketOrders = $orders->filter(function ($order) {
            return $order->orderRequest->type === 'پاکت';  // فرض بر اینکه 'package_type' مشخص‌کننده نوع بسته است
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
        // محاسبه درصد تحویل داده شده برای هر نوع بسته
        $pocketPercentage = ($pocketOrders->count() / $totalOrders) * 100;
        $smallBoxPercentage = ($smallBoxOrders->count() / $totalOrders) * 100;
        $mediumBoxPercentage = ($mediumBoxOrders->count() / $totalOrders) * 100;
        $largeBoxPercentage = ($largeBoxOrders->count() / $totalOrders) * 100;
        // ارسال داده‌ها به فرانت‌اند
        return [
            'total_delivered_orders' => $totalOrders,
            'packages' => [
                'pocket' => $pocketPercentage,
                'smallBox' => $smallBoxPercentage,
                'mediumBox' => $mediumBoxPercentage,
                'largeBox' => $largeBoxPercentage
            ]
        ];
    }

    public function dailyOrdersCount(Request $request) {
        $orders = $request->user()->courierorders()
            ->where('status', 'delivered')
            ->get()
            ->groupBy(fn($order) => CalendarUtils::strftime('Y-m-d', strtotime($order->created_at)))
            ->map(fn($group, $date) => [
                'date' => $date,
                'jalali_year' => (int) explode('-', $date)[0],
                'jalali_month' => (int) explode('-', $date)[1],
                'jalali_day' => (int) explode('-', $date)[2],
                'count' => $group->count(),
            ])
            ->values();
        
        return $orders;
    }

    public function weekHoursActivity(Request $request) {
        $orders = $request->user()->courierorders()
            ->where('status', 'delivered')
            ->get()
            ->groupBy(function ($order) {
                $createdAtTehran = Carbon::parse($order->created_at)->setTimezone('Asia/Tehran');
                //$dayOfWeek = Carbon::parse($order->created_at)->dayOfWeek; // مقدار اصلی روز هفته (۰ = یکشنبه)
                $dayOfWeek = $createdAtTehran->dayOfWeek;
                // تبدیل ترتیب روزهای هفته (جمعه = ۰، پنج‌شنبه = ۱، ... ، شنبه = ۶)
                $newDayOfWeek = match ($dayOfWeek) {
                    5 => 0, // جمعه -> ۰
                    4 => 1, // پنج‌شنبه -> ۱
                    3 => 2, // چهارشنبه -> ۲
                    2 => 3, // سه‌شنبه -> ۳
                    1 => 4, // دوشنبه -> ۴
                    0 => 5, // یکشنبه -> ۵
                    6 => 6, // شنبه -> ۶
                };
    
                // تقسیم ۲۴ ساعت به ۴ بازه‌ی ۶ ساعته
                //$hour = Carbon::parse($order->created_at)->hour;
                $hour = $createdAtTehran->hour;
                $timeSlot = match (true) {
                    $hour < 6  => 0, // 00:00 - 05:59
                    $hour < 12 => 1, // 06:00 - 11:59
                    $hour < 18 => 2, // 12:00 - 17:59
                    default    => 3, // 18:00 - 23:59
                };
    
                return "$newDayOfWeek-$timeSlot"; // مقدار جدید برای دسته‌بندی
            })
            ->map(fn($group, $key) => [
                'day' => (int) explode('-', $key)[0], // روز اصلاح‌شده (۰ = جمعه)
                'slot' => (int) explode('-', $key)[1], // بازه‌ی ۶ ساعتی
                'count' => $group->count(), // تعداد سفارشات
            ])
            ->values();
    
        return $orders;
    }    

}
