<?php

declare(strict_types=1);

namespace Bugover\Model\Attribute;

use Attribute;

#[Attribute]
class ModelEntity
{
    public function __construct(
        private readonly string|object $repositoryClass = '',
        private readonly bool $readonly = true,
    ) {
    }
}
