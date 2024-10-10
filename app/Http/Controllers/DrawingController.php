<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\DrawingEvent;
use Illuminate\Support\Facades\Log;

class DrawingController extends Controller
{
    /**
     * Start drawing on whiteboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startRemoteScreenShareDrawing(Request $request)
    {
        $data = $request->all();
        
        Log::info('Start drawing event called with data: ' . json_encode($data));
        
        event(new DrawingEvent('started', $data['type'], $data['pos'], $data['user_id']));
        return response()->json(['status' => 'Screen share drawing started']);
    }

    /**
     * Stop drawing on whiteboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopRemoteScreenShareDrawing(Request $request)
    {
        $data = $request->all();
        
        event(new DrawingEvent('stopped', $data['type'], $data['pos'], $data['user_id']));
        return response()->json(['status' => 'Screen share drawing stopped']);
    }
}