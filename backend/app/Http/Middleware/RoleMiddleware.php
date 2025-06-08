<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, $roles)) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $route = $request->route()->getName();
        $method = $request->method();

        if ($user->role === 'admin') {
            if (in_array($route, ['admins.index', 'admins.store', 'admins.show', 'admins.update', 'admins.destroy'])) {
                return response()->json(['message' => 'Admin không thể quản lý admin khác'], 403);
            }
            if ($method === 'DELETE' && in_array($route, ['users.destroy', 'shops.destroy', 'orders.destroy', 'disputes.destroy', 'payments.destroy'])) {
                return response()->json(['message' => 'Admin không thể xóa tài nguyên này'], 403);
            }
        }

        if ($user->role === 'moderator') {
            if (in_array($route, ['users.index', 'users.store', 'users.show', 'users.update', 'users.destroy', 'users.updateStatus', 'admins.index', 'admins.store', 'admins.show', 'admins.update', 'admins.destroy', 'reports.index'])) {
                return response()->json(['message' => 'Moderator không thể truy cập tài nguyên này'], 403);
            }
            if ($route === 'shops.updateStatus' || $route === 'shops.destroy') {
                return response()->json(['message' => 'Moderator không thể sửa trạng thái hoặc xóa cửa hàng'], 403);
            }
            if ($route === 'products.destroy') {
                return response()->json(['message' => 'Moderator không thể xóa sản phẩm'], 403);
            }
            if (in_array($route, ['orders.updateSettledStatus', 'orders.updateShippingStatus', 'orders.updateOrderStatus', 'orders.destroy'])) {
                return response()->json(['message' => 'Moderator không thể sửa hoặc xóa đơn hàng'], 403);
            }
            if ($route === 'disputes.updateStatus' || $route === 'disputes.destroy') {
                return response()->json(['message' => 'Moderator không thể sửa hoặc xóa khiếu nại'], 403);
            }
            if ($route === 'vouchers.destroy') {
                return response()->json(['message' => 'Moderator không thể xóa voucher'], 403);
            }
            if ($route === 'payments.updateStatus' || $route === 'payments.destroy') {
                return response()->json(['message' => 'Moderator không thể sửa hoặc xóa thanh toán'], 403);
            }
            if (in_array($route, ['shipping-partners.store', 'shipping-partners.update', 'shipping-partners.destroy'])) {
                return response()->json(['message' => 'Moderator không thể sửa hoặc xóa đối tác vận chuyển'], 403);
            }
            if ($route === 'banners.destroy') {
                return response()->json(['message' => 'Moderator không thể xóa banner'], 403);
            }
        }

        return $next($request);
    }
}