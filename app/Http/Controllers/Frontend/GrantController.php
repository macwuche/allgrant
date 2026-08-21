<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Grant;
use App\Models\GrantPlan;
use App\Services\GrantService;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrantController extends Controller
{
    use ImageUpload, NotifyTrait;

    public function __construct(
        private GrantService $grantService
    ) {}

    public function index()
    {
        if (! setting('user_grant', 'permission') || ! Auth::user()->grant_status) {
            notify()->error(__('Grant currently unavailable!'), 'Error');

            return to_route('user.dashboard');
        } elseif (! setting('kyc_grant') && auth()->user()->kyc != 1) {
            notify()->error(__('Please verify your KYC.'), 'Error');

            return to_route('user.dashboard');
        }

        $plans = GrantPlan::active()->get();

        return view('frontend::grant.index', compact('plans'));
    }

    public function application(Request $request, $id)
    {
        // Check grant available or not
        if (! setting('user_grant', 'permission') || ! Auth::user()->grant_status) {
            notify()->error(__('Grant currently unavailable!'), 'Error');

            return to_route('user.dashboard');
        } elseif (! setting('kyc_grant') && auth()->user()->kyc != 1) {
            notify()->error(__('Please verify your KYC.'), 'Error');

            return to_route('user.dashboard');
        }

        $plan = GrantPlan::findOrFail(decrypt($id));

        // Get plan minimum & maximum amount range
        $min = (int) $plan->minimum_amount;
        $max = (int) $plan->maximum_amount;
        // Get grant amount
        $amount = (int) $request->amount;
        // Get currency symbol from setting
        $currency = setting('currency_symbol', 'global');

        // Check minimum & maximun requirement
        if ($amount < $min || $max < $amount) {
            $message = __('You must choice minimum :min and maximum :max', [':min' => $currency . $plan->minimum_amount, ':max' => $currency . $plan->maximum_amount]);
            notify()->error($message, 'Error');

            return redirect()->back();
        }

        return view('frontend::grant.application', compact('plan', 'request'));
    }

    public function subscribe(Request $request)
    {
        try {
            $user = auth()->user();

            $plan = GrantPlan::find(decrypt($request->grant_id));

            $this->grantService->validate($user, $plan, $request->amount, $request);
            $this->grantService->subscribe($user, $plan, $request->amount, $request);

            notify()->success(__('Grant applied successfully!'), 'Success');
        } catch (\Exception $e) {
            notify()->error($e->getMessage(), 'Error');
        }

        return redirect()->route('user.grant.history');
    }

    public function history()
    {
        $from_date = trim(@explode('-', request('daterange'))[0]);
        $to_date = trim(@explode('-', request('daterange'))[1]);

        $grants = Grant::with('plan', 'user')
            ->where('user_id', auth()->id())
            ->when(request('grant_id'), function ($query) {
                $query->where('grant_no', 'LIKE', '%'.request('grant_id').'%');
            })
            ->when(request('daterange'), function ($query) use ($from_date, $to_date) {
                $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
            })
            ->latest()
            ->paginate(request('limit', 15))
            ->withQueryString();

        return view('frontend::grant.history', compact('grants'));
    }

    public function details($grantNo)
    {
        $grant = Grant::with('plan', 'user')->where('grant_no', $grantNo)->where('user_id', auth()->id())->firstOrFail();

        return view('frontend::grant.details', compact('grant'));
    }

    public function cancel($grant_id)
    {
        // Get grant data
        $grant = Grant::where('grant_no', $grant_id)->where('user_id', auth()->id())->firstOrFail();

        try {

            $this->grantService->cancel(auth()->user(), $grant);

            notify()->success(__('Grant request cancelled successfully!'), 'Success');
        } catch (\Exception $e) {
            notify()->error($e->getMessage(), 'Error');
        }

        return redirect()->route('user.grant.history');
    }
}
