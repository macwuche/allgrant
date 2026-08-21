<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GrantDetailsResource;
use App\Http\Resources\GrantHistoryResource;
use App\Http\Resources\GrantPlanResource;
use App\Models\Grant;
use App\Models\GrantPlan;
use App\Services\GrantService;
use Illuminate\Http\Request;

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
        $grants = Grant::with('plan', 'user')
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
        $grant = Grant::with('plan', 'user')->where('grant_no', $grantId)->where('user_id', auth()->id())->firstOrFail();

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
}
