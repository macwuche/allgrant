<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmailAddress;
use Illuminate\Http\Request;

/**
 * The "add multiple email addresses" management (Gmail "Send mail as"
 * style aliases -- see email.md section 2). Lives under the Email Inbox
 * settings tab, not its own sidebar entry.
 */
class EmailAddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('email-inbox-enabled');
        $this->middleware('permission:email-inbox-manage-addresses');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:email_addresses,email',
            'label' => 'nullable|string|max:255',
        ]);

        $address = EmailAddress::create([
            'email' => $data['email'],
            'label' => $data['label'] ?? null,
            'is_default' => ! EmailAddress::exists(),
            'status' => true,
            'created_by' => auth('admin')->id(),
        ]);

        notify()->success(__('Address added.'), 'Success');

        return redirect()->back();
    }

    public function setDefault($id)
    {
        EmailAddress::query()->update(['is_default' => false]);
        EmailAddress::findOrFail($id)->update(['is_default' => true]);

        notify()->success(__('Default address updated.'), 'Success');

        return redirect()->back();
    }

    public function toggleStatus($id)
    {
        $address = EmailAddress::findOrFail($id);
        $address->update(['status' => ! $address->status]);

        notify()->success(__('Address updated.'), 'Success');

        return redirect()->back();
    }

    public function destroy($id)
    {
        EmailAddress::findOrFail($id)->delete();

        notify()->success(__('Address removed.'), 'Success');

        return redirect()->back();
    }
}
