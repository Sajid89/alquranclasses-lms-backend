<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\ScreenShareStatus;
use App\Http\Requests\AgoraRequest;
use Illuminate\Support\Facades\Log;

class ScreenShareController extends Controller
{
    protected $request;

    public function __construct(AgoraRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Start screen sharing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startScreenShare(Request $request)
    {
        $this->request->validateStartStopScreenShare($request);
        $userId = $request->input('user_id');
        
        event(new ScreenShareStatus('started', $userId));
        
        return response()->json(['status' => 'Screen sharing started']);
    }

    /**
     * Stop screen sharing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopScreenShare(Request $request)
    {
        $this->request->validateStartStopScreenShare($request);
        $userId = $request->input('user_id');
        
        event(new ScreenShareStatus('stopped', $userId));
        
        return response()->json(['status' => 'Screen sharing stopped']);
    }
}