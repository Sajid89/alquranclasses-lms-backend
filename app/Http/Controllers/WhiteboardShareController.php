<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\WhiteBoardStatus;
use App\Http\Requests\AgoraRequest;
use Illuminate\Support\Facades\Log;

class WhiteboardShareController extends Controller
{
    protected $request;

    public function __construct(AgoraRequest $request)
    {
        $this->request = $request;
    }

    public function startWhiteboardShare(Request $request)
    {
        $this->request->validateStartStopScreenShare($request);
        $userId = $request->input('user_id');
        
        event(new WhiteBoardStatus('started', $userId));
        
        return response()->json(['status' => 'Whiteboard sharing started']);
    }

    public function stopWhiteboardShare(Request $request)
    {
        $this->request->validateStartStopScreenShare($request);
        $userId = $request->input('user_id');
        
        event(new WhiteBoardStatus('stopped', $userId));
        
        return response()->json(['status' => 'Whiteboard sharing stopped']);
    }
}