<?php

declare(strict_types=1);

namespace Service\Models\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

/**
 *
 */
trait Touchy
{
    /**
     * First Level, booting(), boot(), booted()
     *
     * @throws BindingResolutionException
     */
    protected static function booting(): void
    {
        $policies = config('shipyard.api.policy_namespace') . Str::afterLast(static::class, '\\') . 'Policy';

        if (!class_exists($policies)) {
            return;
        }

        $policy = app()->make($policies);
        $user = user();

        if ($policy) {
            static::retrieved(static fn() => $user ? $policy->view(user(), self::class) : null);
            static::creating(static fn() => $user ? $policy->create(user(), self::class) : null);
            static::updating(static fn() => $user ? $policy->update(user(), self::class) : null);
            static::deleting(static fn() => $user ? $policy->delete(user(), self::class) : null);
        }
    }
}
