<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GrantDetailsResource;
use App\Http\Resources\GrantHistoryResource;
use App\Http\Resources\GrantPlanResource;
use App\Http\Resources\GrantTransactionResource;
use App\Models\Grant;
use App\Models\GrantPlan;
use App\Services\GrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrantController extends Controller
{
    public function __construct(
        private GrantService $grantService
    ) {}

    public function plans()
    {
        $plans = GrantPlan::active()->get();

        return response()->json([
            'status' => true,
            'data' => GrantPlanResource::collection($plans),
        ]);
    }

    public function subscribe(Request $request)
    {
        try {
            $user = auth()->user();

            $plan = GrantPlan::find($request->plan_id);

            $this->grantService->validate($user, $plan, $request->amount, $request);

            $this->grantService->subscribe($user, $plan, $request->amount, $request);

            return response()->json([
                'status' => true,
                'message' => __('Grant applied successfully!'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function history(Request $request)
    {
        $grants = Grant::with('transactions', 'plan', 'user')
            ->where('user_id', auth()->id())
            ->when($request->has('grant_id'), function ($query) use ($request) {
                $query->where('grant_no', 'LIKE', '%'.$request->grant_id.'%');
            })
            ->when($request->filled(['from_date', 'to_date']), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->from_date)
                    ->whereDate('created_at', '<=', $request->to_date);
            })
            ->latest()->paginate();

        return response()->json([
            'status' => true,
            'data' => GrantHistoryResource::collection($grants),
            'meta' => [
                'current_page' => $grants->currentPage(),
                'last_page' => $grants->lastPage(),
                'per_page' => $grants->perPage(),
                'total' => $grants->total(),
            ],
        ]);
    }

    public function details($grantId)
    {
        $grant = Grant::with('transactions', 'plan', 'user')->where('grant_no', $grantId)->where('user_id', auth()->id())->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new GrantDetailsResource($grant),
        ]);
    }

    public function cancel(Request $request)
    {
        try {
            $grant = Grant::where('grant_no', $request->grant_id)->where('user_id', auth()->id())->firstOrFail();

            $this->grantService->cancel(auth()->user(), $grant);

            return response()->json([
                'status' => true,
                'message' => __('Grant request cancelled successfully!'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function installments($grant_id)
    {
        try {
            $user = auth()->user();

            $grant = Grant::where('grant_no', $grant_id)->where('user_id', $user->id)->firstOrFail();

            $transactions = $grant->transactions()->get();

            return response()->json([
                'status' => true,
                'data' => GrantTransactionResource::collection($transactions),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function payInstallment(Request $request)
    {
        $grant_id = $request->grant_id;
        $trans_id = $request->trans_id;

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $grant = Grant::query()
                ->with('transactions')
                ->where('user_id', $user->id)
                ->where('grant_no', $grant_id)
                ->first();

            foreach ($grant->transactions as $grantTransaction) {

                if ($trans_id && $grantTransaction->id != $trans_id) {
                    continue;
                }

                $this->grantService->payInstallment($user, $grant, $grantTransaction);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => __('User Grant Installment Successfully Done'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
