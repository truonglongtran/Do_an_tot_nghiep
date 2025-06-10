<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    // Lấy danh sách thanh toán
    public function index()
    {
        return response()->json(Payment::with('order')->get());
    }

    // Tạo thanh toán mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'status' => 'required|in:success,failed,refund',
        ]);

        $payment = Payment::create([
            'order_id' => $validated['order_id'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'status' => $validated['status'],
        ]);

        return response()->json($payment, 201);
    }

    // Cập nhật thông tin thanh toán
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'status' => 'required|in:success,failed,refund',
        ]);

        $payment->update([
            'order_id' => $validated['order_id'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Cập nhật thanh toán thành công', 'payment' => $payment]);
    }

    // Cập nhật trạng thái (success/failed/refunded)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:success,failed,refund',
        ]);

        $payment = Payment::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();

        return response()->json(['message' => 'Trạng thái thanh toán đã được cập nhật', 'payment' => $payment]);
    }

    // Xóa thanh toán
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json(['message' => 'Thanh toán đã được xóa']);
    }

    // Lấy thông tin thanh toán cụ thể (để sửa)
    public function show($id)
    {
        $payment = Payment::with('order')->findOrFail($id);
        return response()->json($payment);
    }
}