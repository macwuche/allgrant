<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GrantPlan;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GrantPlanController extends Controller
{
    use ImageUpload, NotifyTrait;

    public function __construct()
    {
        $this->middleware('permission:grant-plan-list', ['only' => ['index']]);
        $this->middleware('permission:grant-plan-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:grant-plan-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:grant-plan-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $plans = GrantPlan::latest()->get();

        return view('backend.plan.grant.index', compact('plans'));
    }

    public function create()
    {
        return view('backend.plan.grant.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'installment_intervel' => 'required',
            'total_installment' => 'required',
            'per_installment' => 'required',
            'minimum_amount' => 'required',
            'maximum_amount' => 'required',
            'grant_fee_type' => 'required',
            'charge' => 'required',
            'delay_days' => 'required',
            'charge_type' => 'required',
            'field_options' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $options = [];

        foreach ($request->field_options ?? [] as $key => $field) {
            $field['values'] = data_get($request->field_options_value, $key);

            $options[] = $field;
        }

        $data = [
            'name' => $request->get('name'),
            'minimum_amount' => $request->float('minimum_amount'),
            'maximum_amount' => $request->float('maximum_amount'),
            'per_installment' => $request->float('per_installment'),
            'installment_intervel' => $request->integer('installment_intervel'),
            'charge' => $request->float('charge'),
            'charge_type' => $request->get('charge_type'),
            'total_installment' => $request->integer('total_installment'),
            'grant_fee' => $request->float('grant_fee'),
            'grant_fee_type' => $request->get('grant_fee_type'),
            'admin_profit' => $request->float('bank_profit'),
            'delay_days' => $request->integer('delay_days'),
            'status' => $request->get('status', 1),
            'featured' => $request->get('featured'),
            'badge' => $request->get('badge'),
            'field_options' => $request->has('field_options') ? json_encode($options) : null,
            'instructions' => $request->get('instructions'),
        ];

        GrantPlan::create($data);

        notify()->success(__('Grant plan created successfully!'));

        return redirect()->route('admin.plan.grant.index');
    }

    public function edit($id)
    {
        $plan = GrantPlan::find($id);

        return view('backend.plan.grant.edit', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'installment_intervel' => 'required',
            'total_installment' => 'required',
            'per_installment' => 'required',
            'minimum_amount' => 'required',
            'grant_fee_type' => 'required',
            'maximum_amount' => 'required',
            'charge' => 'required',
            'delay_days' => 'required',
            'charge_type' => 'required',
            'field_options' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $options = [];

        foreach ($request->field_options ?? [] as $key => $field) {
            $field['values'] = data_get($request->field_options_value, $key);

            $options[] = $field;
        }

        $data = [
            'name' => $request->get('name'),
            'minimum_amount' => $request->float('minimum_amount'),
            'maximum_amount' => $request->float('maximum_amount'),
            'per_installment' => $request->float('per_installment'),
            'installment_intervel' => $request->integer('installment_intervel'),
            'charge' => $request->float('charge'),
            'charge_type' => $request->get('charge_type'),
            'total_installment' => $request->integer('total_installment'),
            'grant_fee' => $request->float('grant_fee'),
            'grant_fee_type' => $request->get('grant_fee_type'),
            'admin_profit' => $request->float('bank_profit'),
            'delay_days' => $request->integer('delay_days'),
            'status' => $request->get('status', 1),
            'featured' => $request->get('featured'),
            'badge' => $request->get('badge'),
            'field_options' => $request->has('field_options') ? json_encode($options) : null,
            'instructions' => $request->get('instructions'),
        ];

        $plan = GrantPlan::find($id);

        $plan->update($data);

        notify()->success(__('Grant plan updated successfully!'));

        return redirect()->route('admin.plan.grant.index');
    }

    public function destroy($id)
    {
        $plan = GrantPlan::find($id);

        if ($plan->grants->count() > 0) {
            notify()->error(__('You can not delete this plan. There are some grants associated with this plan.'));

            return redirect()->back();
        }

        $plan->delete();

        notify()->success(__('Grant plan deleted successfully!'));

        return redirect()->back();
    }
}
