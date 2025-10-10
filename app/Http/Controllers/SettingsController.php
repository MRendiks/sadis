<?php
// app/Http/Controllers/SettingsController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        // Only call middleware if the method exists on the controller to avoid undefined method errors
        if (method_exists($this, 'middleware')) {
            $this->middleware('superadmin');
        }
    }

    public function index(Request $request)
    {
        // Try to read simple settings; fall back to session if app doesn't have a preferences column yet.
        $user = $request->user();
        $defaults = [
            'page_size' => 10,
            'theme' => 'system', // light | dark | system
        ];

        $prefs = $defaults;
        if (property_exists($user, 'preferences') && is_array($user->preferences ?? null)) {
            $prefs = array_merge($defaults, $user->preferences);
        } else {
            $prefs = array_merge($defaults, $request->session()->get('preferences', []));
        }

        return view('settings.index', ['prefs' => $prefs]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'page_size' => ['required','integer','in:10,25,50,100'],
            'theme' => ['required','in:light,dark,system'],
        ]);

        $user = $request->user();

        // If your users table has a JSON 'preferences' column, this will persist there.
        if (property_exists($user, 'preferences')) {
            $current = is_array($user->preferences ?? null) ? $user->preferences : [];
            $user->preferences = array_merge($current, $data);
            $user->save();
        } else {
            // Fallback to session-based settings if you don't have a preferences column.
            $request->session()->put('preferences', $data);
        }

        return back()->with('success','Settings saved.');
    }
}
