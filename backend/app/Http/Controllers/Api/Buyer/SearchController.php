<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q');
        if (!$query) {
            Log::info('Search query is empty');
            return response()->json(['products' => []], 200);
        }

        $products = Product::where('status', 'approved')
            ->whereNull('deleted_at')
            ->where('name', 'like', "%$query%")
            ->whereHas('shop', function ($q) {
                $q->where('status', 'active');
            })
            ->with([
                'variants' => function ($q) {
                    $q->where('status', 'approved')
                      ->select('id', 'product_id', 'price', 'image_url')
                      ->first();
                },
                'shop' => function ($q) {
                    $q->where('status', 'active')
                      ->select('id', 'shop_name');
                }
            ])
            ->select('id', 'shop_id', 'name', 'sold_count')
            ->take(20)
            ->get();

        $user = $request->user();
        $token = $request->bearerToken();
        Log::info("Search attempt with query: {$query}, user: " . ($user ? "ID {$user->id}, role {$user->role}" : 'null') . ", token: " . ($token ? substr($token, 0, 10) . '...' : 'none'));
        if ($user) {
            try {
                $searchHistory = SearchHistory::create([
                    'user_id' => $user->id,
                    'keyword' => $query,
                ]);
                Log::info("Search history saved for user {$user->id} with keyword: {$query}, record ID: {$searchHistory->id}");
            } catch (\Exception $e) {
                Log::error("Failed to save search history for user {$user->id} with keyword: {$query}, error: {$e->getMessage()}");
            }
        } else {
            Log::warning("No authenticated user for search history: query={$query}");
        }

        return response()->json([
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sold_count' => $p->sold_count,
                'product_variant' => $p->variants->first() ? [
                    'id' => $p->variants->first()->id,
                    'price' => $p->variants->first()->price,
                    'image_url' => $p->variants->first()->image_url,
                ] : null,
                'shop' => $p->shop ? [
                    'id' => $p->shop->id,
                    'shop_name' => $p->shop->shop_name,
                ] : null,
            ]),
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        Log::info("Fetching search history for user {$user->id}");
        $history = SearchHistory::where('user_id', $user->id)
            ->select('id', 'keyword', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json(['history' => $history]);
    }

    public function deleteSearchHistory(Request $request, $id)
    {
        $user = $request->user();
        $history = SearchHistory::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$history) {
            Log::warning("Search history entry not found for user {$user->id}, id {$id}");
            return response()->json(['message' => 'Search history entry not found'], 404);
        }

        $history->forceDelete();
        Log::info("Search history entry deleted for user {$user->id}, id {$id}");
        return response()->json(['message' => 'Search history entry deleted successfully']);
    }

    public function clearSearchHistory(Request $request)
    {
        $user = $request->user();
        SearchHistory::where('user_id', $user->id)->forceDelete();
        Log::info("All search history cleared for user {$user->id}");
        return response()->json(['message' => 'Search history cleared successfully']);
    }
}