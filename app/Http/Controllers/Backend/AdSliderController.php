<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdSliderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ad-slider-list', ['only' => ['index']]);
        $this->middleware('permission:ad-slider-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ad-slider-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:ad-slider-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $slides = AdSlider::orderBy('position')->latest()->get();
        $templates = AdSlider::templates();

        return view('backend.ad_slider.index', compact('slides', 'templates'));
    }

    public function create()
    {
        $templates = AdSlider::templates();

        return view('backend.ad_slider.create', compact('templates'));
    }

    private function rules(): array
    {
        return [
            'template' => 'required|integer|min:1|max:10',
            'title' => 'required|string',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'duration' => 'required|integer|min:2|max:60',
            'position' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        AdSlider::create([
            'template' => $request->integer('template'),
            'title' => $request->get('title'),
            'button_text' => $request->get('button_text'),
            'button_link' => $request->get('button_link'),
            'duration' => $request->integer('duration'),
            'position' => $request->integer('position', 0),
            'status' => $request->get('status', 1),
        ]);

        notify()->success(__('Ad slide created successfully!'));

        return redirect()->route('admin.ad-slider.index');
    }

    public function edit($id)
    {
        $slide = AdSlider::findOrFail($id);
        $templates = AdSlider::templates();

        return view('backend.ad_slider.edit', compact('slide', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $slide = AdSlider::findOrFail($id);

        $slide->update([
            'template' => $request->integer('template'),
            'title' => $request->get('title'),
            'button_text' => $request->get('button_text'),
            'button_link' => $request->get('button_link'),
            'duration' => $request->integer('duration'),
            'position' => $request->integer('position', 0),
            'status' => $request->get('status', 1),
        ]);

        notify()->success(__('Ad slide updated successfully!'));

        return redirect()->route('admin.ad-slider.index');
    }

    public function destroy($id)
    {
        $slide = AdSlider::findOrFail($id);
        $slide->delete();

        notify()->success(__('Ad slide deleted successfully!'));

        return redirect()->back();
    }
}
