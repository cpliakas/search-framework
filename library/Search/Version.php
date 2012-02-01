<?php

/**
 * Search Tools
 *
 * LICENSE
 *
 * This source file is subject to the GNU Lesser General Public License that is
 * bundled with this package in the file LICENSE.txt. It is also available for
 * download at http://www.gnu.org/licenses/lgpl-3.0.txt.
 *
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt
 * @copyright  Copyright (c) 2012 Chris Pliakas <cpliakas@gmail.com>
 */

/**
 * Class to store and retrieve the version of Search Tools.
 *
 * @package    Search
 * @subpackage Version
 */
final class Search_Version
{
    /**
     * Search Tools version identification - see compareVersion()
     */
    const VERSION = '0.0.1dev';

    /**
     * Compare the specified version string $version with the current version of
     * Search Tools.
     *
     * @param string $version
     *   A version string (e.g. "0.7.1").
     * @return int
     *   - -1: if the $version is older
     *   - 0: if they are the same
     *   - +1: if $version is newer
     *
     * @see version_compare()
     */
    public static function compare($version)
    {
        return version_compare($version, self::VERSION);
    }
}
