<?php

namespace App\Repository;

use App\Models\ChangeTeacherReason;
use App\Models\SubscriptionCancelationReason;
use App\Repository\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Interfaces\CancelationReasonsRepositoryInterface;
class CancelationReasonsRepository extends BaseRepository implements CancelationReasonsRepositoryInterface
{
    /**
     * @var Eloquent | Model
     */
    public $model;

    /**
     * @var Eloquent | Model
     */
    protected $originalModel;

    /**
     * RepositoriesAbstract constructor.
     * @param User $model
     */
    public function __construct(SubscriptionCancelationReason $model)
    {
        $this->model = $model;
        $this->originalModel = $model;
    }
    
    public function getAllChangeTeacherReasons()
    {
        $reasons = ChangeTeacherReason::orderBy('id', 'asc')->get();
        $data = [];
        foreach ($reasons as $reason) {
            $data[] = [
                'id' => $reason->id,
                'reason' => $reason->reason,
            ];
        }
        return $data;
    }
}


