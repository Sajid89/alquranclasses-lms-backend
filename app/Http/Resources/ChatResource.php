<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    private $currentUserId;

    // Modify the constructor to accept the current user ID
    public function __construct($resource, $currentUserId)
    {
        parent::__construct($resource);
        $this->currentUserId = $currentUserId;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        list($senderRoleType, $receiverRoleType) = explode('-', $this->type);
        
        $currentUserId = $this->currentUserId; 
        $isCurrentUserSender = $this->from == $currentUserId;
        $isCurrentUserReceiver = $this->to == $currentUserId;
        
        if ($isCurrentUserSender) {
            $senderRole = $senderRoleType;
        } else if ($isCurrentUserReceiver) {
            $senderRole = $senderRoleType;
        }

        return [
            'senderId' => $this->from,
            'sender' => $senderRole,
            'receiverId' => $this->to,
            'message' => $this->message,
            'createdAt' => $this->created_at,
        ];
    }
}