<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\FdrStatus;
use App\Http\Controllers\Controller;
use App\Models\Fdr;
use App\Models\FdrPlan;
use App\Services\FdrService;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FdrController extends Controller
{
    use NotifyTrait;

    public function __construct(
        private FdrService $fdrService
    ) {}

    public function index()
    {
        if (! setting('user_fdr', 'permission') || ! Auth::user()->fdr_status) {
            notify()->error(__('FDR currently unavailable!'), 'Error');

            return to_route('user.dashboard');
        } elseif (! setting('kyc_fdr') && auth()->user()->kyc != 1) {
            notify()->error(__('Please verify your KYC.'), 'Error');

            return to_route('user.dashboard');
        }

        $plans = FdrPlan::active()->latest()->get();

        return view('frontend::fdr.index', compact('plans'));
    }

    public function subscribe(Request $request)
    {
        try {
            // Get user
            $user = auth()->user();
            // Get FDR Plan
            $plan = FdrPlan::find(decrypt($request->fdr_id));

            // Validate
            $this->fdrService->validate($user, $plan);
            // Subscribe
            $this->fdrService->subscribe($plan, $user, $request);

            notify()->success(__('FDR Plan Subscribed Successfully!'), 'Success');
        } catch (\Throwable $th) {
            notify()->error($th->getMessage(), 'Error');
        }

        return redirect()->route('user.fdr.history');
    }

    public function increment(Request $request, $id)
    {
        try {
            // Get FDR data
            $fdr = Fdr::findOrFail(decrypt($id));

            // Validate
            $this->fdrService->valdiateIncrement($request, $fdr);
            // Increment
            $this->fdrService->increment($request, $fdr);

            notify()->success(__('FDR Increased Successfully!'), 'Success');
        } catch (\Exception $e) {
            notify()->error($e->getMessage(), 'Error');
        }

        return back();
    }

    public function decrement(Request $request, $id)
    {
        try {
            // Get FDR data
            $fdr = Fdr::findOrFail(decrypt($id));

            // Validate
            $this->fdrService->valdiateDecrement($request, $fdr);

            // Decrement
            $this->fdrService->decrement($request, $fdr);

            notify()->success(__('FDR Decreased Successfully!'), 'Success');
        } catch (\Exception $e) {
            notify()->error($e->getMessage(), 'Error');
        }

        return back();
    }

    protected function checkAbility($fdr)
    {
        $status = $fdr->status->value;

        if ($status == FdrStatus::Completed->value) {
            notify()->error(__('Sorry, Your FDR is completed!'), 'Error');

            return false;
        } elseif ($status == FdrStatus::Closed->value) {
            notify()->error(__('Your FDR is closed!'), 'Error');

            return false;
        }

        return true;
    }

    public function history()
    {

        $from_date = trim(@explode('-', request('daterange'))[0]);
        $to_date = trim(@explode('-', request('daterange'))[1]);

        $fdrs = Fdr::with(['user', 'plan', 'transactions'])
            ->where('user_id', auth()->id())
            ->when(request('fdr_id'), function ($query) {
                $query->where('fdr_id', 'LIKE', '%'.request('fdr_id').'%');
            })
            ->when(request('daterange'), function ($query) use ($from_date, $to_date) {
                $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
            })
            ->latest()
            ->paginate(request('limit', 15))
            ->withQueryString();

        return view('frontend::fdr.history', compact('fdrs'));
    }

    public function details($fdrId)
    {
        $fdr = Fdr::with(['transactions', 'plan', 'user'])->where('fdr_id', $fdrId)->where('user_id', auth()->id())->firstOrFail();

        return view('frontend::fdr.details', compact('fdr'));
    }

    public function cancel($fdrId)
    {
        try {
            // Get fdr data
            $fdr = Fdr::where('fdr_id', $fdrId)->where('user_id', auth()->id())->firstOrFail();

            // Cancel process
            $this->fdrService->checkFdrCancellationAbility($fdr);
            $this->fdrService->cancel($fdr);

            notify()->success(__('FDR Cancelled Successfully!'), 'Success');
        } catch (\Exception $e) {
            notify()->error($e->getMessage());
        }

        return redirect()->back();
    }
}
