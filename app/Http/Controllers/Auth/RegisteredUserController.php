<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
// use GuzzleHttp\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Twilio\Rest\Client as RestClient;
use Twilio\TwiML\Voice\Client as VoiceClient;
use Twilio\Rest\Client;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'mobile' => ['required', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile'=> $request->mobile,
            'password' => Hash::make($request->password),
        ]);
        // $user =User::where('email',$this->input('email'))->frist();
        $user->generateCode();
        
        $message ="Login OTP is ".$user->code;
        $account_sid= getenv("TWILIO_SID");
        $auth_token= getenv("TWILIO_TOKEN");
        $twilio_number= getenv("TWILIO_FROM");
        $client = new Client($account_sid,$auth_token);
        $client->messages->create($user->mobile,[
            'from'=>$twilio_number,
            'body'=>$message,
        ]);


        event(new Registered($user));

        Auth::login($user);
        return redirect(RouteServiceProvider::HOME);
    }
}
