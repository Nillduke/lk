<?php
namespace App\Http\Controllers;

use App\Services\FormProtectionSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormProtectionController extends Controller
{
    public function edit(Request $request, FormProtectionSettings $settings)
    {
        abort_unless($request->user()->canAccess('manage_access'),403);
        return view('admin.form-protection',['settings'=>$settings->values()]);
    }
    public function update(Request $request, FormProtectionSettings $settings)
    {
        abort_unless($request->user()->canAccess('manage_access'),403);
        $data=$request->validate(['enabled'=>'required|boolean','site_key'=>'required|string|max:255|regex:/\A[A-Za-z0-9_-]+\z/',
            'turnstile_secret'=>'nullable|string|max:255|regex:/\A[A-Za-z0-9_-]+\z/']);
        $current = $settings->values();
        if (empty($data['turnstile_secret']) && (($data['enabled'] && !$current['has_secret']) || ($current['has_secret'] && $data['site_key'] !== $current['site_key']))) {
            throw ValidationException::withMessages(['turnstile_secret'=>'Enter the matching secret key from your Cloudflare Turnstile widget.']);
        }
        try { $settings->save($data); } catch (\Throwable) {
            return back()->withErrors(['settings'=>'Form protection settings could not be saved. Check that the application can write its .env file.']);
        }
        return back()->with('success','Form protection settings saved. The new settings take effect immediately.');
    }
}
