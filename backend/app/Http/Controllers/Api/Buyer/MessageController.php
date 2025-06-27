<?php

namespace App\Http\Controllers\Api\Buyer;

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
        $buyer = Auth::user();
        Log::info('Fetching conversations for buyer', ['buyer_id' => $buyer->id]);

        try {
            $messages = Message::where('buyer_id', $buyer->id)
                ->with('seller:id,username,avatar_url')
                ->orderBy('last_message_at', 'desc')
                ->get()
                ->map(function ($message) {
                    $messagesArray = $message->messages ?? [];
                    $lastMessage = !empty($messagesArray) ? end($messagesArray) : null;
                    return [
                        'seller' => [
                            'id' => $message->seller->id,
                            'username' => $message->seller->username ?? 'Shop',
                            'avatar_url' => $message->seller->avatar_url ?? 'https://via.placeholder.com/50',
                        ],
                        'last_message' => $lastMessage ? [
                            'content' => $lastMessage['content'],
                            'created_at' => $lastMessage['created_at'],
                            'sender_type' => $lastMessage['sender_type'],
                        ] : null,
                        'unread_count' => $message->unread_count ?? 0,
                    ];
                });

            Log::info('Conversations fetched', [
                'buyer_id' => $buyer->id,
                'conversation_count' => $messages->count(),
                'conversations' => $messages->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_buyer_id' => $buyer->id,
                    'conversations' => $messages,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching conversations', [
                'buyer_id' => $buyer->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách cuộc trò chuyện',
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $buyer = Auth::user();
        $sellerId = $request->query('seller_id');

        Log::info('Fetching messages', [
            'buyer_id' => $buyer->id,
            'seller_id' => $sellerId,
        ]);

        if (!$sellerId) {
            Log::warning('Missing seller_id in request');
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp seller_id để xem cuộc trò chuyện',
            ], 400);
        }

        try {
            // Kiểm tra seller_id hợp lệ
            $seller = User::where('id', $sellerId)->where('role', 'seller')->first();
            if (!$seller) {
                Log::warning('Invalid seller_id', [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Người bán không tồn tại hoặc không hợp lệ',
                ], 400);
            }

            // Ưu tiên bản ghi có messages không rỗng
            $messagesQuery = Message::where('buyer_id', $buyer->id)
                ->where('seller_id', $sellerId)
                ->orderByRaw('JSON_LENGTH(messages) DESC, last_message_at DESC');
            $messageCount = $messagesQuery->count();
            if ($messageCount > 1) {
                Log::warning('Multiple conversation records found', [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                    'count' => $messageCount,
                ]);
                $latestMessage = $messagesQuery->first();
                Message::where('buyer_id', $buyer->id)
                    ->where('seller_id', $sellerId)
                    ->where('id', '!=', $latestMessage->id)
                    ->delete();
                Log::info('Deleted duplicate conversation records', [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                ]);
                $message = $latestMessage;
            } else {
                $message = $messagesQuery->first();
            }

            if (!$message) {
                Log::info('No conversation found', [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Cuộc trò chuyện chưa tồn tại. Gửi tin nhắn để bắt đầu!',
                ], 404);
            }

            Log::info('Messages fetched', [
                'buyer_id' => $buyer->id,
                'seller_id' => $sellerId,
                'message_count' => count($message->messages ?? []),
                'messages' => $message->messages,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $message->messages ?? [],
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching messages', [
                'buyer_id' => $buyer->id,
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy tin nhắn',
            ], 500);
        }
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
        ]);

        $buyer = Auth::user();
        $sellerId = $request->receiver_id;

        Log::info('Sending message', [
            'buyer_id' => $buyer->id,
            'seller_id' => $sellerId,
            'content' => $request->content,
        ]);

        try {
            // Kiểm tra receiver_id là seller
            $seller = User::where('id', $sellerId)->where('role', 'seller')->first();
            if (!$seller) {
                Log::warning('Invalid receiver_id', [
                    'buyer_id' => $buyer->id,
                    'receiver_id' => $sellerId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Người nhận không phải là người bán',
                ], 400);
            }

            // Ưu tiên bản ghi có messages không rỗng
            $messagesQuery = Message::where('buyer_id', $buyer->id)
                ->where('seller_id', $sellerId)
                ->orderByRaw('JSON_LENGTH(messages) DESC, last_message_at DESC');
            $messageCount = $messagesQuery->count();
            if ($messageCount > 1) {
                Log::warning('Multiple conversation records found', [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                    'count' => $messageCount,
                ]);
                $latestMessage = $messagesQuery->first();
                Message::where('buyer_id', $buyer->id)
                    ->where('seller_id', $sellerId)
                    ->where('id', '!=', $latestMessage->id)
                    ->delete();
                Log::info('Deleted duplicate conversation records', [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                ]);
                $message = $latestMessage;
            } else {
                $message = $messagesQuery->first();
            }

            $newMessage = [
                'sender_type' => 'buyer',
                'content' => $request->content,
                'created_at' => now()->toIso8601String(),
                'is_read' => false,
            ];

            if ($message) {
                $messages = $message->messages ?? [];
                $messages[] = $newMessage;
                $message->messages = $messages;
                $message->last_message_at = now();
                $message->unread_count = count(array_filter($messages, fn($msg) => $msg['sender_type'] === 'seller' && !($msg['is_read'] ?? false)));
                $message->save();
            } else {
                $message = Message::create([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                    'messages' => [$newMessage],
                    'last_message_at' => now(),
                    'unread_count' => 0,
                ]);
            }

            Log::info('Message sent', [
                'buyer_id' => $buyer->id,
                'seller_id' => $sellerId,
                'message' => $newMessage,
                'total_messages' => count($message->messages),
                'unread_count' => $message->unread_count,
            ]);

            return response()->json([
                'success' => true,
                'data' => $newMessage,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'buyer_id' => $buyer->id,
                'seller_id' => $sellerId,
                'content' => $request->content,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi tin nhắn',
            ], 500);
        }
    }

    public function markAsRead($sellerId)
    {
        $buyer = Auth::user();

        Log::info('Marking messages as read', [
            'buyer_id' => $buyer->id,
            'seller_id' => $sellerId,
        ]);

        try {
            // Kiểm tra seller_id hợp lệ
            $seller = User::where('id', $sellerId)->where('role', 'seller')->first();
            if (!$seller) {
                Log::warning('Invalid seller_id', [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Người bán không tồn tại hoặc không hợp lệ',
                ], 400);
            }

            $message = Message::where('buyer_id', $buyer->id)
                ->where('seller_id', $sellerId)
                ->orderByRaw('JSON_LENGTH(messages) DESC, last_message_at DESC')
                ->firstOrFail();

            $messages = $message->messages ?? [];
            $unreadCount = 0;
            foreach ($messages as $key => $msg) {
                if ($msg['sender_type'] === 'seller' && !($msg['is_read'] ?? false)) {
                    $messages[$key]['is_read'] = true;
                }
                if ($msg['sender_type'] === 'seller' && !($msg['is_read'] ?? false)) {
                    $unreadCount++;
                }
            }
            $message->messages = $messages;
            $message->unread_count = $unreadCount;
            $message->save();

            Log::info('Messages marked as read', [
                'buyer_id' => $buyer->id,
                'seller_id' => $sellerId,
                'unread_count' => $unreadCount,
                'total_messages' => count($messages),
            ]);

            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error marking messages as read', [
                'buyer_id' => $buyer->id,
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đánh dấu tin nhắn là đã đọc',
            ], 500);
        }
    }

    public function getSellers(Request $request)
    {
        try {
            $sellers = User::where('role', 'seller')->select('id', 'username', 'avatar_url')->get();
            Log::info('Fetching sellers', ['seller_count' => $sellers->count()]);
            return response()->json([
                'success' => true,
                'data' => $sellers,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching sellers', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách người bán',
            ], 500);
        }
    }
}
?>