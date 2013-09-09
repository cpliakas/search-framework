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
 * Models a document containing the source data being indexed.
 *
 * This object adds Lucene specific properties, such as document boosting.
 */
class DateNormalizer implements NormalizerInterface
{
    /**
     * The format that dates are normalized to.
     *
     * @var string
     */
    protected $_format = 'Y-m-d\TH:i:s\Z';

    /**
     * Sets the format that dates are normalized to.
     *
     * @param string $format
     *    The format that dates are normalized to.
     *
     * @return DateNormalizer
     *
     * @see http://php.net/manual/en/function.date.php
     */
    public function setDateFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * Implements NormalizerInterface::normalize().
     *
     * Normalizes date formats to avoid errors thrown by Elasticsearch.
     */
    public function normalize($value)
    {
        if ($value) {
            if (is_int($value) || ctype_digit($value)) {
                $timestamp = $value;
            } elseif (!$timestamp = strtotime($value)) {
                return $value;
            }
            return date($this->_format, $timestamp);
        }
        return $value;
    }
}
