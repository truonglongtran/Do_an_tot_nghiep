<?php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser()
    {
        $user = Auth::user();
        if (!$user) {
            Log::error('Unauthenticated access attempt to getUser');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $avatarUrl = $user->avatar_url ? Storage::url($user->avatar_url) : null;
        Log::info('Fetching user profile', [
            'user_id' => $user->id,
            'avatar_url' => $user->avatar_url,
            'public_url' => $avatarUrl,
            'file_exists' => $user->avatar_url ? Storage::exists($user->avatar_url) : false,
            'storage_link' => file_exists(public_path('storage')) ? 'exists' : 'missing',
        ]);

        return response()->json([
            'data' => [
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar_url' => $avatarUrl,
            ]
        ], 200);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            Log::error('Unauthenticated access attempt to updateUser');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for user update', [
                'user_id' => $user->id,
                'errors' => $validator->errors()->toArray(),
            ]);
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $avatarUrl = $user->avatar_url ? Storage::url($user->avatar_url) : null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            Log::info('Processing avatar upload', [
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'is_valid' => $file->isValid(),
                'mime_type' => $file->getMimeType(),
            ]);

            // Delete old avatar if exists
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
                Log::info('Deleted old avatar', ['path' => $user->avatar_url]);
            }

            // Ensure directory exists
            $directory = 'users/' . $user->id;
            if (!Storage::disk('public')->exists($directory)) {
                if (!Storage::disk('public')->makeDirectory($directory, 0755, true)) {
                    Log::error('Failed to create directory', [
                        'user_id' => $user->id,
                        'directory' => $directory,
                        'path' => storage_path('app/public/' . $directory),
                    ]);
                    return response()->json(['message' => 'Failed to create storage directory'], 500);
                }
                Log::info('Created user directory', ['path' => $directory]);
            }

            // Store new avatar
            $extension = $file->getClientOriginalExtension();
            $filename = 'avatar.' . $extension;
            try {
                $path = $file->storeAs($directory, $filename, 'public');
                if (!Storage::disk('public')->exists($path)) {
                    Log::error('Failed to store avatar', [
                        'user_id' => $user->id,
                        'path' => $path,
                        'full_path' => storage_path('app/public/' . $path),
                        'permissions' => substr(sprintf('%o', fileperms(storage_path('app/public'))), -4),
                    ]);
                    return response()->json(['message' => 'Failed to store avatar'], 500);
                }
                $cleanPath = str_replace('storage/', '', $path);
                $avatarUrl = Storage::disk('public')->url($cleanPath);
                Log::info('Avatar stored successfully', [
                    'user_id' => $user->id,
                    'path' => $cleanPath,
                    'public_url' => $avatarUrl,
                    'file_exists' => Storage::disk('public')->exists($cleanPath),
                ]);
                $user->avatar_url = $cleanPath;
            } catch (\Exception $e) {
                Log::error('Exception during avatar storage', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                return response()->json(['message' => 'Failed to store avatar: ' . $e->getMessage()], 500);
            }
        }

        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->save();

        Log::info('User profile updated', [
            'user_id' => $user->id,
            'avatar_url' => $user->avatar_url,
            'public_url' => $avatarUrl,
            'file_exists' => $user->avatar_url ? Storage::disk('public')->exists($user->avatar_url) : false,
        ]);

        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'user' => [
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar_url' => $avatarUrl,
            ]
        ], 200);
    }
}   