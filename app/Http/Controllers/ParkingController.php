<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CheckIn;
use App\Vehicle;
use App\Transaction;
use Str;
use Carbon\Carbon;

class ParkingController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }

    public function CheckIn(CheckIn $request)
    {
        DB::beginTransaction();
        try {
            $vehicle = Vehicle::where('license_plate', $request->license_plate)->first();

            if(!$vehicle) {
                $vehicle = Vehicle::create([
                    'license_plate' => $request->license_plate,
                ]);
            }
            $code = Str::random(10);
            $transaction = Transaction::create([
                'vehicle_id' => $vehicle->id,
                'code' => $code,
            ]);
            DB::commit();
            return response()->json(
                [
                    'data' => $transaction
                ], 201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }        
    }

    public function CheckOut($code)
    {
        $transaction = Transaction::where('code', $code)->first();
        if(!$transaction) {
            return response()->json(
                [
                    'message' => 'Transaction not found'
                ], 404
            );
        }
        $start = $transaction->check_in;
        $end = Carbon::now();
        $result = $end->diffInHours($from);
        $transaction->check_out = $end;
        $price = $result * 3000;
        $transaction->price = $price;
        $transaction->save();

        return response()->json(
            [
                'data' => $transaction
            ], 201
        );

    }

    public function getTransaction(Request $request)
    {
        $transaction = Transaction::whereBetween('check_in', [$request->from, $request->to])->get();
        return response()->json(
            [
                'data' => $transaction
            ], 200
        );
        
    }
}
