<?php

declare(strict_types=1);

namespace Bugover\Model\Traits;

use Exception;
use Ramsey\Uuid\Uuid as RamseyUuid;
use RuntimeException;

/**
 * Trait UUID
 *
 * @package Src\Core\Traits
 */
trait Uuid
{
    /**
     * The "booting" method of the model.
     *
     * @throws Exception
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model): void {
            if (empty($model->{$model->getKeyName()}) && 'string' === $model->getKeyType()) {
                $model->{$model->getKeyName()} = $model->generateUuid();
            }

            if (in_array('uuid', $model->getFillable(), true)) {
                $model->setAttribute('uuid', $model->generateUuid());
            }
        });
    }

    /**
     * @return string
     */
    protected function generateUuid(): string
    {
        return match ($this->uuidVersion()) {
            1 => RamseyUuid::uuid1()->toString(),
            default => RamseyUuid::uuid4()->toString(),
        };

        throw new RuntimeException("UUID version [{$this->uuidVersion()}] not supported.");
    }

    /**
     * The UUID version to use.
     *
     * @return int
     */
    protected function uuidVersion(): int
    {
        return 4;
    }
}
