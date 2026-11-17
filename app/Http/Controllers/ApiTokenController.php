<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()->latest()->get();

        return view('settings.api-tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
        ]);

        $token = $request->user()->createToken($data['name'], ['*'], now()->addDays(30));

        return redirect()->route('api-tokens.index')
            ->with('success', __('messages.api_token_created'))
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $token)
    {
        $request->user()->tokens()->whereKey($token)->delete();

        return back()->with('success', __('messages.deleted_successfully'));
    }
}
