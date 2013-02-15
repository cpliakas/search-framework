<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 *
 */
interface NormalizerInterface
{
    /**
     * Normalizes a value.
     *
     * @param mixed $value
     *   The value being normalized.
     *
     * @return string
     *   The normalized value.
     */
    public function normalize($value);
}
