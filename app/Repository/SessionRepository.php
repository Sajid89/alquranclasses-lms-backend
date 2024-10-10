<?php

namespace App\Repository;

use App\Models\DeviceSession;

class SessionRepository
{
    private $model;

    public function __construct(DeviceSession $model)
    {
        $this->model = $model;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function update($id, $data)
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }
}