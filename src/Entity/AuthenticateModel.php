<?php

declare(strict_types=1);

namespace Bugover\Model\Entity;

use Bugover\Model\Attribute\ModelEntity;
use Bugover\Model\Contract\EntityContract;
use Bugover\Model\Traits\ScopeHelpers;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class ServiceAuthenticate
 *
 * @package Service\Messenger
 */
#[\AllowDynamicProperties]
#[ModelEntity()]
class AuthenticateModel extends Authenticate implements EntityContract
{
    use ScopeHelpers;
    use HasApiTokens;
    use Notifiable;
    use HasRoles;

    /**
     * @var string[]
     */
    public array $socketAuth = [];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * Specifies the user's FCM tokens
     *
     * @return string|null
     * @noinspection PhpUnused
     */
    public function routeNotificationForFcm(): ?string
    {
        return $this->fcm()->first()->key ?? null;
    }
}
