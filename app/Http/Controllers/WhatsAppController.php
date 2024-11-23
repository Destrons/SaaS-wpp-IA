<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function new_message(Request $request){

        $phone = "+" . $request->post('WaId');

        $user = User::where('phone', $phone)->first();

        if (!$user){
            
        }
        
        dsd($request->all());
    }
}   