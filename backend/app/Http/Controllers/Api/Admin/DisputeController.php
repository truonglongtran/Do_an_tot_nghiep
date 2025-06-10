<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index()
    {
        return response()->json(Dispute::with(['order', 'buyer', 'seller'])->get());
    }
    public function show($id)
    {
        $dispute = Dispute::with(['order', 'buyer', 'seller'])->findOrFail($id);
        return response()->json($dispute);
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,resolved,rejected',
            'admin_note' => 'nullable|string',
        ]);

        $dispute = Dispute::findOrFail($id);
        $dispute->status = $request->status;
        $dispute->admin_note = $request->admin_note;
        $dispute->save();

        return response()->json(['message' => 'Trạng thái khiếu nại đã được cập nhật', 'dispute' => $dispute]);
    }
    public function destroy($id)
    {
        $dispute = Dispute::findOrFail($id);
        $dispute->delete();

        return response()->json(['message' => 'Khiếu nại đã được xóa']);
    }
}