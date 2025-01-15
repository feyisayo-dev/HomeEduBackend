<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\adminController;
use Illuminate\Http\Request;
use App\Models\user_streaks;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class UserStreakController extends Controller
{
    public function resetInactiveStreaks()
    {
        $today = Carbon::today();

        // Fetch all users who haven't practiced today
        $inactiveStreaks = user_streaks::where('last_practice_date', '<', $today)->get();

        foreach ($inactiveStreaks as $streak) {
            $streak->streak_count = 0; // Reset streak count
            $streak->save();
        }
    }

    public function updateStreak(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $username = $request->input('username');
        $today = Carbon::today();

        // Get the user's streak record
        $streak = user_streaks::firstOrCreate(['username' => $username]);

        // Check if the user practiced yesterday
        $lastPracticeDate = $streak->last_practice_date ? Carbon::parse($streak->last_practice_date) : null;

        if ($lastPracticeDate && $lastPracticeDate->isYesterday()) {
            // Increment streak count
            $streak->streak_count += 1;
        } elseif (!$lastPracticeDate || !$lastPracticeDate->isToday()) {
            // Reset streak if not consecutive
            $streak->streak_count = 1;
        }

        // Update last practice date to today
        $streak->last_practice_date = $today;
        $streak->save();

        return response()->json([
            'streak_count' => $streak->streak_count,
            'message' => 'Streak updated successfully',
        ]);
    }

    public function getStreaks(Request $request)
    {
        // Log the incoming request data
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->url());
        Log::info('Request Headers: ' . json_encode($request->headers->all()));
        Log::info('Request Data: ' . json_encode($request->all()));

        // Retrieve user_id from request
        $userId = $request->username; // Get user_id from the request body (or query parameter)

        if (!$userId) {
            return response()->json([
                'error' => 'User ID is required.',
            ], 400);
        }

        $streak = user_streaks::where('username', $userId)->first();

        return response()->json([
            'streak_count' => $streak->streak_count ?? 0,
        ]);
    }

}
