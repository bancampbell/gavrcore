<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CookieConsentController extends Controller
{
    public function accept(Request $request)
    {
        $categories = $request->input('categories', ['analytics' => true, 'marketing' => true]);

        Cookie::queue('cookie_consent', 'accepted', 60 * 24 * 365);
        Cookie::queue('cookie_consent_categories', json_encode($categories), 60 * 24 * 365);

        return response()->json([
            'success' => true,
            'message' => 'Cookie consent saved',
        ]);
    }

    public function decline(Request $request)
    {
        Cookie::queue(Cookie::forget('cookie_consent'));
        Cookie::queue(Cookie::forget('cookie_consent_categories'));

        return response()->json([
            'success' => true,
            'message' => 'Cookie consent declined',
        ]);
    }
}
