<?php

declare(strict_types=1);

namespace Bugover\Model\Custom;

use Closure;

/**
 *
 */
trait HasCustomRelations
{
    /**
     * Define a custom relationship.
     *
     * @param string $related
     * @param Closure $baseConstraints
     * @param Closure $eagerConstraints
     * @param Closure $eagerMatcher
     * @return Custom
     */
    public function custom($related, Closure $baseConstraints, Closure $eagerConstraints, Closure $eagerMatcher): Custom
    {
        $instance = new $related();
        $query = $instance->newQuery();

        return new Custom($query, $this, $baseConstraints, $eagerConstraints, $eagerMatcher);
    }
}
