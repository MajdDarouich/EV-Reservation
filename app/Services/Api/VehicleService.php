<?php

namespace App\Services\Api;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        /** @var User $user */
        $user = auth('api')->user();

        return $user->vehicles()->get();
    }

    public function attach(array $data)
    {
        /** @var User $user */
        $user = auth('api')->user();

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::query()
                ->whereKey($data['vehicle_id'])
                ->whereNull('user_id')
                ->lockForUpdate()
                ->firstOrFail();

            $vehicle->update(['user_id' => $user->id]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return $vehicle->fresh();
    }

    public function detach(array $data)
    {
        /** @var User $user */
        $user = auth('api')->user();

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::query()
                ->where('user_id', $user->id)
                ->whereKey($data['vehicle_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $vehicle->update([
                'user_id' => null,
                'is_default' => false,
            ]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return $vehicle->fresh();
    }

    public function makeDefault(array $data)
    {
        /** @var User $user */
        $user = auth('api')->user();

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::query()
                ->where('user_id', $user->id)
                ->whereKey($data['vehicle_id'])
                ->lockForUpdate()
                ->firstOrFail();

            Vehicle::query()
                ->where('user_id', $user->id)
                ->where('id', '!=', $vehicle->id)
                ->update(['is_default' => false]);

            $vehicle->update(['is_default' => true]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return $vehicle->fresh();
    }

    public function removeDefault(array $data)
    {
        /** @var User $user */
        $user = auth('api')->user();

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::query()
                ->where('user_id', $user->id)
                ->whereKey($data['vehicle_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $vehicle->update(['is_default' => false]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return $vehicle->fresh();
    }
}
