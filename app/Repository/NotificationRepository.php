<?php
namespace App\Repository;

use App\Models\Notification;

class NotificationRepository
{
    protected $model;

    public function __construct(Notification $notification)
    {
        $this->model = $notification;
    }

    public function create(array $data)
    {
        return $this->model::create($data);
    }
}