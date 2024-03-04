<?php

declare(strict_types=1);

namespace Bugover\Model\Entity;

use Bugover\Model\Attribute\ModelEntity;
use Bugover\Model\Traits\ScopeHelpers;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceModel
 *
 * @package ServiceEntityStory\Messenger
 * @method static first($attributes = ['*'])
 */
#[\AllowDynamicProperties]
#[ModelEntity()]
class ServiceModel extends Model
{
    use ScopeHelpers;

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';
}
