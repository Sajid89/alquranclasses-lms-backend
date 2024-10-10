<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\AgoraHelper;
use App\Http\Requests\AgoraRequest;

class AgoraController extends Controller
{
    private $agoraRequest;

    public function __construct(AgoraRequest $agoraRequest)
    {
        $this->agoraRequest = $agoraRequest;
    }

    /**
     * Generate Agora tokens: student and teacher
     * to join the same class
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        $this->agoraRequest->validateGenerateToken($request);
        $channelName = $request->class_id;
        $token = AgoraHelper::GetToken($request->user_id, $channelName);

        return $this->success([
            'token' => $token,
        ], 'Token generated successfully', 200);
    }

    public function uploadWhiteboardFile(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $path_url = Storage::disk('s3')->put('Files', $file);
            $path = Storage::disk('s3')->url($path_url);
            $file_upload = preg_replace('/\s+/', '%20', $path);

            return response()->json([
                'success' => true,
                'fileUrl' => $path,
                ''
            ], 200);
        }
    }
}