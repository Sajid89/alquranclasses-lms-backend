<?php

namespace App\Repository;

use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;

class ChatRepository
{
    private $model;

    public function __construct(Chat $chat)
    {
        $this->model = $chat;
    }

    /**
     * Create a new chat message
     *
     * @param array $data
     * @return Chat
     */
    public function create($data)
    {
        return $this->model->create($data);
    }

    /**
     * Get all messages between two users
     *
     * @param int $from
     * @param int $to
     * @return Chat
     */
    public function getMessages($from, $to)
    {
        return $this->model->where(function ($query) use ($from, $to) {
            $query->where('from', $from)->where('to', $to);
        })->orWhere(function ($query) use ($from, $to) {
            $query->where('from', $to)->where('to', $from);
        })->get();
    }

    /**
     * Get all messages between two users and format them
     *
     * @param int $from
     * @param int $to
     * @param int $currentUserId
     * @return ChatResource
     */
    public function getFormattedMessages($from, $to, $currentUserId)
    {
        $messages = $this->getMessages($from, $to);
        $resourceCollection = $messages->map(function ($message) use ($currentUserId) {
            return new ChatResource($message, $currentUserId);
        });
        
        return $resourceCollection;
    }

    /**
     * Get the latest message between two users
     *
     * @param int $from
     * @param int $to
     * @return Chat
     */
    public function getLatestMessage($from, $to)
    {
        return $this->model->where('from', $from)->where('to', $to)
            ->latest()->first();
    }

    /**
     * Get the latest message between two users and format it
     *
     * @param int $from
     * @param int $to
     * @param int $currentUserId
     * @return ChatResource
     */
    public function getFormattedLatestMessage($from, $to, $currentUserId)
    {
        $message = $this->getLatestMessage($from, $to);
        return new ChatResource($message, $currentUserId);
    }

    /**
     * Get the unread messages count between two users
     *
     * @param int $from
     * @param int $to
     * @return int
     */
    public function getUnreadMessagesCount($from, $to)
    {
        return $this->model->where('from', $from)->where('to', $to)
            ->where('seen', 0)->count();
    }

    /**
     * Mark all messages between two users as read
     *
     * @param int $from
     * @param int $to
     * @return int
     */
    public function markAsRead($from, $to)
    {
        $messages = $this->model->where(function($query) use ($from, $to) {
            $query->where(['from' => $to, 'to' => $from, 'seen' => 0]);
        })->get();
        
        if ($messages->isNotEmpty()) {
            $this->model->where(function($query) use ($from, $to) {
                $query->where(['from' => $to, 'to' => $from, 'seen' => 0]);
            })->update(['seen' => 1]);
        }
    }
    
    /**
     * Get all users that have chatted with each other 
     * to monitor the users chat list
     *
     * @param int $userId
     * @return array
     */
    public function getChatUsers($teacherIds)
    {
        $chats = DB::table('chats')
            ->leftJoin('users as from_user', function($join) {
                $join->on('chats.from', '=', 'from_user.id')
                ->where(function($query) {
                    $query->where('chats.type', 'like', 'teacher-%')
                    ->orWhere('chats.type', 'like', 'customer-%');
                });
            })
            ->leftJoin('students as from_student', function($join) {
                $join->on('chats.from', '=', 'from_student.id')
                ->where('chats.type', 'like', 'student-%');
            })
            ->leftJoin('users as to_user', function($join) {
                $join->on('chats.to', '=', 'to_user.id')
                ->where(function($query) {
                    $query->where('chats.type', 'like', '%-teacher')
                    ->orWhere('chats.type', 'like', '%-customer');
                });
            })
            ->leftJoin('students as to_student', function($join) {
                $join->on('chats.to', '=', 'to_student.id')
                    ->where('chats.type', 'like', '%-student');
            })
            ->select(
                DB::raw('CONCAT(LEAST(chats.from, chats.to), "-", GREATEST(chats.from, chats.to)) as id'),
                DB::raw('CASE 
                    WHEN chats.type LIKE "teacher-%" THEN from_user.name 
                    WHEN chats.type LIKE "customer-%" THEN from_user.name 
                    WHEN chats.type LIKE "student-%" THEN from_student.name 
                END as from_name'),
                DB::raw('CASE 
                    WHEN chats.type LIKE "%-teacher" THEN to_user.name 
                    WHEN chats.type LIKE "%-customer" THEN to_user.name 
                    WHEN chats.type LIKE "%-student" THEN to_student.name 
                END as to_name'),
                DB::raw('COALESCE(from_user.profile_photo_path, from_student.profile_photo_url, "-") as profilePic'),
                'chats.from',
                'chats.to'
            )
            ->where('chats.type', 'not like', '%coordinator%')
            ->where(function($query) use ($teacherIds) {
                $query->whereIn('chats.from', $teacherIds)
                ->orWhereIn('chats.to', $teacherIds);
            })
            ->where(function($query) {
                $query->where('chats.type', 'like', '%teacher%');
            })
            ->groupBy('chats.from', 'chats.to', 'from_user.name', 'from_student.name', 'to_user.name', 'to_student.name', 'from_user.profile_photo_path', 'from_student.profile_photo_url')
            ->get();
        
        $uniqueUsers = collect();
        
        $chats->map(function($chat) use ($uniqueUsers) {
            $unreadMessages = $this->getUnreadMessagesCount($chat->from, $chat->to);
            $user = [
                'id' => $chat->id,
                'name' => $chat->from_name . '-' . $chat->to_name,
                'profilePic' => $chat->profilePic,
                'unreadMessages' => $unreadMessages,
                'active' => false,
            ];
        
            if ($chat->id && !$uniqueUsers->contains('id', $chat->id) && 
                !empty($chat->from_name && $chat->to_name)) {
                $uniqueUsers->push($user);
            }
        });
        
        return $uniqueUsers->unique('id')->values()->all();
    }

    /**
     * Get all users that have chatted with a teacher
     *
     * @param int $teacherId
     * @return array
     */
    public function getChatUsersForTeacher($teacherId)
    {
        $baseUrl = env('APP_URL');
    
        $chats = DB::table('chats')
            ->leftJoin('users as user', function($join) {
                $join->on('chats.from', '=', 'user.id')
                    ->whereIn('user.user_type', ['customer', 'teacher-coordinator']);
            })
            ->select(
                'chats.id',
                'user.id as user_id',
                'user.name as user_name',
                'user.profile_photo_path as user_profile_pic',
                'chats.from',
                'chats.to'
            )
            ->where(function($query) use ($teacherId) {
                $query->where('chats.from', $teacherId)
                      ->orWhere('chats.to', $teacherId);
            })
            ->where('chats.type', 'not like', '%student%')
            ->distinct()
            ->get();
    
        $uniqueUsers = collect();
    
        $chats->each(function($chat) use ($uniqueUsers, $baseUrl) {
            $user = [
                'id' => $chat->user_id,
                'name' => $chat->user_name,
                'profilePic' => $chat->user_profile_pic ? $baseUrl . '/' . $chat->user_profile_pic : null,
                'unreadMessages' => $this->getUnreadMessagesCount($chat->from, $chat->to),
                'active' => false,
            ];
    
            if ($chat->user_id && !$uniqueUsers->contains('id', $chat->user_id)) {
                $uniqueUsers->push($user);
            }
        });
    
        return $uniqueUsers->unique('id')->values()->all();
    }
}