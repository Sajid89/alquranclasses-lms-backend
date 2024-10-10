<?php

namespace App\Repository;

use App\Models\NotToCancelSubscriptionReason;
use App\Repository\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Interfaces\NotToCancelReasonsRepositoryInterface;

class NotToCancelReasonsRepository extends BaseRepository implements NotToCancelReasonsRepositoryInterface
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
    public function __construct(NotToCancelSubscriptionReason $model)
    {
        $this->model = $model;
        $this->originalModel = $model;
    }
    
}


