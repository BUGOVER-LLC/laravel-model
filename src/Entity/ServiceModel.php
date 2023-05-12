<?php

declare(strict_types=1);

namespace Service\Models\Entity;

use Illuminate\Database\Eloquent\Model;
use Service\Models\Traits\ScopeHelpers;

/**
 * Class ServiceModel
 * @package ServiceEntityStory\Messenger
 * @method static first($attributes = ['*'])
 */
class ServiceModel extends Model
{
    use ScopeHelpers;

    /**
     * @var string
     */
    protected $connection = 'pgsql_app';

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';
}
