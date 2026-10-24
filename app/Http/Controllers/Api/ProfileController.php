<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = auth('api')->user();

        return response()->json([
            'user' => $user,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user('api');
            $user->update($request->validated());
            $freshUser = $user->fresh();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $freshUser,
        ]);
    }

    public function deleteAccount(Request $request)
    {
        DB::beginTransaction();

        try {
            /** @var User $user */
            $user = $request->user('api');
            $user->tokens()->update(['revoked' => true]);
            $user->delete();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}
