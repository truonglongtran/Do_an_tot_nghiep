<?php
// app/Http/Controllers/Api/Buyer/LoyaltyPointController.php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class LoyaltyPointController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $points = LoyaltyPoint::where('user_id', $user->id)
            ->with([
                'order' => function ($q) {
                    $q->select('id', 'created_at');
                }
            ])
            ->select('id', 'user_id', 'points', 'transaction_type', 'order_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $totalPoints = LoyaltyPoint::where('user_id', $user->id)
            ->sum('points');

        return response()->json([
            'total_points' => $totalPoints,
            'transactions' => $points,
        ]);
    }
}