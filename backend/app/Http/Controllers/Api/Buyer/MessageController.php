<?php
// app/Http/Controllers/Api/Buyer/MessageController.php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $messages = Message::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->with([
                'sender' => function ($q) {
                    $q->select('id', 'email', 'avatar_url');
                },
                'receiver' => function ($q) {
                    $q->select('id', 'email', 'avatar_url');
                }
            ])
            ->select('id', 'sender_id', 'receiver_id', 'content', 'is_read', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'content' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $shop = Shop::where('id', $request->shop_id)
            ->where('status', 'active')
            ->firstOrFail();

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $shop->owner_id,
            'content' => $request->content,
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Message sent', 'data' => $message], 201);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $message = Message::where('id', $id)
            ->where('receiver_id', $user->id)
            ->firstOrFail();

        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Message marked as read']);
    }
}