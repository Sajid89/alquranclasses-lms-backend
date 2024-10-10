<?php

namespace App\Http\Controllers;

use App\Classes\Enums\UserTypesEnum;
use App\Http\Requests\ChatRequest;
use App\Models\Chat;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class ChatController extends Controller
{
    private $pusher;
    private $chatRepository;
    private $chatRequest;
    private $userRepository;

    public function __construct(
        ChatRepository $chatRepository,
        ChatRequest $chatRequest,
        UserRepository $userRepository
    )
    {
        $this->chatRepository = $chatRepository;
        $this->chatRequest = $chatRequest;
        $this->userRepository = $userRepository;

        $this->pusher = new Pusher(
            config('pusher.key'),
            config('pusher.secret'),
            config('pusher.app_id'),
            [
                'cluster' => config('pusher.cluster'),
                'useTLS' => config('pusher.useTLS')
            ]
        );
    }

    /**
     * Authenticate the pusher request
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function pusherAuth(Request $request)
    {
        $this->chatRequest->validatePusheAuth($request);

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');
        
        $authData = $this->pusher->socket_auth($channelName, $socketId);

        return response($authData);
    }

    /**
     * Send a new message
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request)
    {
        $this->chatRequest->validateSendMessage($request);

        $senderId = $request->input('from');
        $receiverId = $request->input('to');
        $data = $request->only(['message', 'sender']);
        $data['createdAt'] = Carbon::now()->toJSON();
        $data['senderId'] = $senderId;
        $data['receiverId'] = $receiverId;

        // Sort the IDs to ensure consistent channel naming
        $ids = [$senderId, $receiverId];
        sort($ids, SORT_NUMERIC);

        $channelName = "private-chat-{$ids[0]}-{$ids[1]}";
        $this->pusher->trigger($channelName, 'new-message', $data);

        // Broadcast to the monitoring channel
        $this->pusher->trigger('private-monitoring', 'new-message', $data);

        $this->chatRepository->create($request->all());

        return $this->success($data, 'Message sent successfully');
    }

    /**
     * Get messages
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessages(Request $request)
    {
        $this->chatRequest->validateGetMessages($request);
        
        $data = $request->all();

        $data['user_id'] = $data['user_type'] === 'student' || $data['user_type'] === UserTypesEnum::TeacherCoordinator 
            ?  $data['from'] : auth()->user()->id;
        
        $messages = $this->chatRepository
            ->getFormattedMessages($data['from'], $data['to'], $data['user_id']);
        
        return $this->success($messages, 'Messages retrieved successfully');
    }

    /**
     * Get the latest message
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatestMessage(Request $request)
    {
        $this->chatRequest->validateGetMessages($request);
        
        $data = $request->all();
        $data['user_id'] = $data['user_type'] === 'student' ?
            $data['from'] : auth()->user()->id;
        
        $latestMessage = $this->chatRepository
            ->getFormattedLatestMessage($data['from'], $data['to'], $data['user_id']);
        
        return $this->success($latestMessage, 'Latest message retrieved successfully');
    }

    /**
     * Update the read status of messages
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function markMessagesAsRead(Request $request)
    {
        $this->chatRequest->validateUpdateAsRead($request);
        $data = $request->all();
        
        $this->chatRepository->markAsRead($data['from'], $data['to']);
        
        return $this->success([], 'Messages marked as read successfully');
    }

    /**
     * Get the users for monitoring
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getChatUsers()
    {
        $user = auth()->user();

        if ($user->user_type !== UserTypesEnum::TeacherCoordinator) {
            return $this->error('You do not have the necessary permissions to access the requested resource.', 403);
        }

        $teacherIds = $this->userRepository->getCoordinatedTeachers($user->id);
        $users = $this->chatRepository->getChatUsers($teacherIds);
        
        return $this->success($users, 'Chat users for monitoring retrieved successfully');
    }
}