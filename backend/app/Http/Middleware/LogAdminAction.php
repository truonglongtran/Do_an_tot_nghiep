<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AdminLog;
use Symfony\Component\HttpFoundation\Response;

class LogAdminAction
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Chỉ ghi log nếu người dùng là admin và response thành công (200 hoặc 201)
        $user = $request->user();
        if ($user && in_array($user->role, ['superadmin', 'admin', 'moderator']) && in_array($response->getStatusCode(), [200, 201])) {
            // Kiểm tra route có tồn tại và có tên
            $route = $request->route();
            if (!$route || $route->getName() === null) {
                return $response; // Bỏ qua nếu route không có tên
            }

            // Bỏ qua route logout
            if ($route->getName() === 'admin.logout') {
                return $response;
            }

            // Xác định thông tin log
            $routeName = $route->getName();
            $method = $request->method();
            $actionType = $this->determineActionType($routeName, $method);
            $targetType = $this->determineTargetType($routeName);
            $targetId = $this->determineTargetId($request, $routeName);
            $description = $this->generateDescription($user, $actionType, $targetType, $targetId, $routeName);

            AdminLog::logAction(
                $user,
                $actionType,
                $targetType,
                $targetId,
                $description
            );
        }

        return $response;
    }

    private function determineActionType(string $routeName, string $method): string
    {
        if (str_contains($routeName, '.index')) {
            return 'list';
        } elseif (str_contains($routeName, '.store')) {
            return 'create';
        } elseif (str_contains($routeName, '.update') || str_contains($routeName, '.updateStatus')) {
            return 'update';
        } elseif (str_contains($routeName, '.destroy')) {
            return 'delete';
        } elseif (str_contains($routeName, '.show')) {
            return 'view';
        }

        return $method === 'GET' ? 'view' : 'modify';
    }

    private function determineTargetType(string $routeName): ?string
    {
        if (str_contains($routeName, 'admins.')) {
            return 'Admin';
        } elseif (str_contains($routeName, 'users.')) {
            return 'User';
        } elseif (str_contains($routeName, 'shops.')) {
            return 'Shop';
        } elseif (str_contains($routeName, 'products.')) {
            return 'Product';
        } elseif (str_contains($routeName, 'orders.')) {
            return 'Order';
        } elseif (str_contains($routeName, 'disputes.')) {
            return 'Dispute';
        } elseif (str_contains($routeName, 'vouchers.')) {
            return 'Voucher';
        } elseif (str_contains($routeName, 'payments.')) {
            return 'Payment';
        } elseif (str_contains($routeName, 'shipping-partners.')) {
            return 'ShippingPartner';
        } elseif (str_contains($routeName, 'banners.')) {
            return 'Banner';
        } elseif (str_contains($routeName, 'reviews.')) {
            return 'Review';
        } elseif (str_contains($routeName, 'reports.')) {
            return 'Report';
        }

        return null;
    }

    private function determineTargetId(Request $request, string $routeName): ?int
    {
        $parameterNames = ['id', 'shop', 'user', 'product', 'order', 'dispute', 'voucher', 'payment', 'shippingPartner', 'banner', 'shopId'];
        foreach ($parameterNames as $param) {
            if ($request->route($param)) {
                return $request->route($param);
            }
        }

        return null;
    }

    private function generateDescription($user, string $actionType, ?string $targetType, ?int $targetId, string $routeName): string
    {
        $actionDescriptions = [
            'list' => 'Liệt kê',
            'create' => 'Tạo',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'view' => 'Xem',
            'modify' => 'Sửa đổi',
        ];

        $action = $actionDescriptions[$actionType] ?? 'Thực hiện';
        $target = $targetType ? mb_strtolower($targetType) : 'tài nguyên';
        $targetInfo = $targetId ? " với ID: {$targetId}" : '';
        return "{$action} {$target}{$targetInfo} trên màn hình {$routeName}";
    }
}