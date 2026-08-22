<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginActivities;
use App\Models\Page;
use App\Providers\RouteServiceProvider;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    use NotifyTrait;

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $page = Page::where('code', 'login')->where('locale', app()->getLocale())->first();
        if (! $page) {
            $page = Page::where('code', 'login')->where('locale', defaultLocale())->first();
        }
        $data = json_decode($page->data, true);
        $googleReCaptcha = plugin_active('Google reCaptcha');

        return view('frontend::auth.login', compact('data', 'googleReCaptcha'));
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $oldTheme = session()->get('site-color-mode');

        $request->authenticate();
        $request->session()->regenerate();
        if (setting('otp_verification', 'permission')) {
            $user = Auth::user();
            $otp = random_int(1000, 9999);
            $shortcodes = [
                '[[otp_code]]' => $otp,
            ];
            $this->smsNotify('otp', $shortcodes, $user->phone);
            $user->update([
                'otp' => $otp,
            ]);
        }

        LoginActivities::add();
        session()->put('site-color-mode', $oldTheme);

        // A stale intended URL left over from an earlier, unauthenticated attempt to
        // visit the admin panel in this same browser session must not be honored here
        // — it would send this regular user straight back into the admin area, which
        // immediately bounces them to /admin/login since they're not an admin.
        if (intendedUrlHasPrefix(setting('site_admin_prefix', 'global'))) {
            session()->forget('url.intended');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $user->phone_verified = 0;
        $user->save();

        Auth::guard('web')->logout();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
