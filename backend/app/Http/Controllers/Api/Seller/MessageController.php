<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::user();
        Log::info('Fetching conversations for seller', ['seller_id' => $seller->id]);

        $messages = Message::where('seller_id', $seller->id)
            ->with('buyer:id,username')
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($message) {
                $messagesArray = $message->messages ?? [];
                $lastMessage = !empty($messagesArray) 
                    ? end($messagesArray) 
                    : null;
                return [
                    'buyer' => [
                        'id' => $message->buyer->id,
                        'username' => $message->buyer->username,
                    ],
                    'last_message' => $lastMessage ? [
                        'content' => $lastMessage['content'],
                        'created_at' => $lastMessage['created_at'],
                        'sender_type' => $lastMessage['sender_type'],
                    ] : null,
                    'unread_count' => $message->unread_count,
                ];
            });

        Log::info('Conversations fetched', [
            'seller_id' => $seller->id,
            'conversation_count' => $messages->count(),
            'conversations' => $messages->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'current_seller_id' => $seller->id,
                'conversations' => $messages,
            ],
        ]);
    }

    public function show(Request $request)
    {
        $seller = Auth::user();
        $buyerId = $request->query('buyer_id');

        Log::info('Fetching messages', [
            'seller_id' => $seller->id,
            'buyer_id' => $buyerId,
        ]);

        if (!$buyerId) {
            Log::warning('Missing buyer_id in request');
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp buyer_id để xem cuộc trò chuyện',
            ], 400);
        }

        $message = Message::where('seller_id', $seller->id)
            ->where('buyer_id', $buyerId)
            ->first();

        if (!$message) {
            Log::info('No conversation found', [
                'seller_id' => $seller->id,
                'buyer_id' => $buyerId,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Cuộc trò chuyện chưa tồn tại. Gửi tin nhắn để bắt đầu!',
            ], 404);
        }

        Log::info('Messages fetched', [
            'seller_id' => $seller->id,
            'buyer_id' => $buyerId,
            'message_count' => count($message->messages ?? []),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $message->messages ?? [],
            ],
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
        ]);

        $seller = Auth::user();
        $buyerId = $request->receiver_id;

        Log::info('Sending message', [
            'seller_id' => $seller->id,
            'buyer_id' => $buyerId,
            'content' => $request->content,
        ]);

        $message = Message::where('seller_id', $seller->id)
            ->where('buyer_id', $buyerId)
            ->first();

        $newMessage = [
            'sender_type' => 'seller',
            'content' => $request->content,
            'created_at' => now()->toIso8601String(),
            'is_read' => false,
        ];

        if ($message) {
            $messages = $message->messages ?? [];
            $messages[] = $newMessage;
            $message->messages = $messages;
            $message->last_message_at = now();
            $message->save();
        } else {
            Message::create([
                'buyer_id' => $buyerId,
                'seller_id' => $seller->id,
                'messages' => [$newMessage],
                'last_message_at' => now(),
                'unread_count' => 0,
            ]);
        }

        Log::info('Message sent', [
            'seller_id' => $seller->id,
            'buyer_id' => $buyerId,
            'message' => $newMessage,
        ]);

        return response()->json([
            'success' => true,
            'data' => $newMessage,
        ]);
    }

    public function markAsRead($buyerId)
    {
        $seller = Auth::user();

        Log::info('Marking messages as read', [
            'seller_id' => $seller->id,
            'buyer_id' => $buyerId,
        ]);

        $message = Message::where('seller_id', $seller->id)
            ->where('buyer_id', $buyerId)
            ->firstOrFail();

        $messages = $message->messages ?? [];
        $unreadCount = 0;
        foreach ($messages as $key => $msg) {
            if ($msg['sender_type'] === 'buyer' && !$msg['is_read']) {
                $messages[$key]['is_read'] = true;
            }
            if ($msg['sender_type'] === 'buyer' && !$msg['is_read']) {
                $unreadCount++;
            }
        }
        $message->messages = $messages;
        $message->unread_count = $unreadCount;
        $message->save();

        Log::info('Messages marked as read', [
            'seller_id' => $seller->id,
            'buyer_id' => $buyerId,
            'unread_count' => $unreadCount,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function getBuyers(Request $request)
    {
        $buyers = User::where('role', 'buyer')->select('id', 'username')->get();
        Log::info('Fetching buyers', ['buyer_count' => $buyers->count()]);
        return response()->json([
            'success' => true,
            'data' => $buyers,
        ]);
    }
}