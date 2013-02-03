<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * And Endpoint that the client library will use to communicate with the search
 * service.
 */
class SearchServiceEndpoint
{
    /**
     * The unique identifier of this endpoint.
     *
     * @var string
     */
    protected $_id;

    /**
     * The URL of this endpoint.
     *
     * @var string|null
     */
    protected $_host;

    /**
     * The index name or endpoint path.
     *
     * @var string|null
     */
    protected $_index;

    /**
     * The port that the endpoint is bound to.
     *
     * @var int|null
     */
    protected $_port;

    /**
     * An associative array of arbitrary, backend specific options.
     *
     * @var array
     */
    protected $_options;

    /**
     * Constructs a SearchServiceEndpoint object.
     *
     * @param string $id
     *   The unique identifier of this endpoint.
     * @param string|null $host
     *   The URL of this endpoint, defaults to null.
     * @param string|null $index
     *   The index name or endpoint path, defaults to null.
     * @param int|null $port
     *   The port that the endpoint is bound to, defaults to null.
     * @param array $options
     *   An associative array of arbitrary, backend specific options.
     */
    public function __construct($id, $host = null, $index = null, $port = null, array $options = array())
    {
        $this->_id = $id;
        $this->_host = $host;
        $this->_index = $index;
        $this->_port = $port;
        $this->_options = $options;
    }

    /**
     * Returns the unique identifier of this endpoint.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the URL of this endpoint.
     *
     * @return string|null
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Returns the index name.
     *
     * @return string|null
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * Returns the path, which is really the index.
     *
     * This method exists to make the code more readable when being implemented
     * by backends such as Solr that require a path to connect to the index.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->_index;
    }

    /**
     * Returns the port that the endpoint is bound to.
     *
     * @return string|null
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * Returns an option's value.
     *
     * @param string $option
     *   The name of the option.
     * @param mixed $default
     *   The value returned if the option does not exist.
     *
     * @return mixed
     *   The option's value.
     */
    public function getOptions($option, $default = null)
    {
        return isset($this->options[$option]) ? $this->options[$option] : $default;
    }
}
