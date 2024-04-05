<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\StartVideoChat;
use App\Models\User;

class VideoChatController extends Controller
{

    public function callUser(Request $request)
    {
        $data['userToCall'] = $request->user_to_call;
        $data['signalData'] = $request->signal_data;
        $data['from'] = Auth::id();
        $data['type'] = 'incomingCall';

        broadcast(new StartVideoChat($data))->toOthers();
    }
    public function acceptCall(Request $request)
    {
        $data['signal'] = $request->signal;
        $data['to'] = $request->to;
        $data['type'] = 'callAccepted';
        broadcast(new StartVideoChat($data))->toOthers();
    }

    public function details()
    {
        $result = [
            "allUsers" => User::where('id', '<>', Auth::id())->get(),
            "authUserId" => auth()->id(),
            "turn_url" => env('TURN_SERVER_URL'),
            "turn_username" => env('TURN_SERVER_USERNAME'),
            "turn_credential" => env('TURN_SERVER_CREDENTIAL'),
        ];
        return response()->json([
            'status' => "success",
            'result' => $result
        ], 200);
    }
}
