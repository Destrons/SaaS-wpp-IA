<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NewUserNotification;
use App\Services\ConversationalService;
use App\Services\StripeService;
use App\Services\UserServices;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function __construct(protected UserServices $userservices){

    }
    public function new_message(Request $request){

        $phone = "+" . $request->post('WaId');
        $user = User::where('phone', $phone)->first();

        if (!$user){
            $user = $this->userServices->store($request->all());
        }

        $user->notify(new NewUserNotification($user->name, "HX11175782e12e1576a9c11c76dafb0406"));
        
    }
}   