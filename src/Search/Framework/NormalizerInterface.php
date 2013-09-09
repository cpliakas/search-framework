<?php

/**
 * Search Framework
 *
 * @author    Chris Pliakas <opensource@chrispliakas.com>
 * @copyright 2013 Chris Pliakas
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License (LGPL)
 * @link      https://github.com/cpliakas/search-framework
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
