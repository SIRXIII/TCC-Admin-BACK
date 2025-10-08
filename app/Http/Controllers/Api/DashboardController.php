<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\TravelerResource;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Rider;
use App\Models\Traveler;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function getStates()
    {
        try {
            $states = [
                [
                    'label' => 'Total Sales',
                    'value' => '$' . Order::where('status', 'delivered')->sum('total_price'),
                ],
                [
                    'label' => 'Active Partners',
                    'value' => Partner::where('status', 'active')->count(),
                ],
                [
                    'label' => 'Active Riders',
                    'value' => Rider::where('status', 'active')->count(),
                ],
                [
                    'label' => 'Pending Orders',
                    'value' => Order::where('status', 'pending')->count(),
                ],
            ];

            return $this->success($states, 'Widget fetch');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch states: ' . $e->getMessage()
            ], 500);
        }
    }



    public function travelersOverview()
    {

        try {
            $travelers = Traveler::with('orders')->latest()->take(5)->get();
            return $this->success(TravelerResource::collection($travelers), 'Travelers overview');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve travelers: ' . $e->getMessage(), 500);
        }
    }

    public function topPartners()
    {


        $topPartners = Partner::select('partners.*')
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('orders.partner_id', 'partners.id')
                    ->where('orders.status', 'delivered');
            }, 'delivered_orders_count')
            ->selectSub(function ($query) {
                $query->from('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->selectRaw('SUM(order_items.price * order_items.quantity)')
                    ->whereColumn('orders.partner_id', 'partners.id')
                    ->where('orders.status', 'delivered');
            }, 'total_sales')
            ->orderByDesc('total_sales')
            ->withAvg('ratings', 'rating')
            ->where('status', 'active')

            ->take(5)
            ->get();

        return $this->success(PartnerResource::collection($topPartners), 'Top Partners by sales');
    }


    public function latestAlert(Request $request)
{
    $user = $request->user();

    $notifications = $user->notifications()
        ->latest()
        ->take(3)
        ->get()
        ->values() // ensures correct indexing
        ->map(function ($notification, $index) {
            // Assign colors based on position
            switch ($index) {
                case 0:
                    $color = 'text-red-500';
                    $statusColor = 'bg-[#E1FDFD] text-[#3E77B0]';
                    $descriptionColor = 'text-[#ED6C3C]';
                    break;
                case 1:
                    $color = 'text-yellow-700';
                    $statusColor = 'bg-[#FEFCDD] text-[#8F802E]';
                    $descriptionColor = 'text-[#8F802E]';
                    break;
                case 2:
                    $color = 'text-green-700';
                    $statusColor = 'bg-[#E7F7ED] text-[#088B3A]';
                    $descriptionColor = 'text-[#8F802E]';
                    break;
                default:
                    $color = 'text-gray-500';
                    $statusColor = 'bg-gray-100 text-gray-800';
                    $descriptionColor = 'text-gray-600';
                    break;
            }

            $data = $notification->data;

            return [
                'id' => $notification->id,
                'label' => ucfirst($data['type']) ?? 'System Alert',
                'value' => $data['title'] ?? '',
                'description' => $data['message'] ?? '',
                'link' => $data['url'] ?? '',
                'date' => $notification->created_at
                    ? $notification->created_at->format('M d, Y - h:i A')
                    : null,
                'color' => $color,
                'statusColor' => $statusColor,
                'descriptionColor' => $descriptionColor,
            ];
        });

    return response()->json([
        'success' => true,
        'message' => 'Latest alerts',
        'data' => $notifications,
    ]);
}

}
