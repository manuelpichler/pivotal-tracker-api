<?php
/**
 * This file is part of the PivotalTracker API component.
 *
 * @version 1.0
 * @copyright Copyright (c) 2012 Manuel Pichler
 * @license GPL licenses.
 */

namespace PivotalTracker\Rest;

/**
 * Helper class that provides some basic REST functionality.
 *
 * This class provides helper methods for common HTTP request methods like
 * <em>GET</em>, <em>POST</em> and <em>PUT</em>.
 *
 * <code>
 * $client = new Client( 'http://example.com' );
 * $client->get( '/objects' );
 * $client->put( '/objects', $obj );
 * $client->post( '/objects', $obj );
 * </code>
 *
 * The ctor of this class expects the remote REST server as argument. This
 * includes host/ip, port and protocol.
 */
class Client
{
    /**
     * Wrapped HTTP request methods.
     */
    const GET  = 'GET',
          POST = 'POST',
          PUT  = 'PUT';

    /**
     * Optional default headers for each request.
     *
     * @var string[]
     */
    private $header = array();

    /**
     * The remote REST server location.
     *
     * @var string
     */
    private $server;

    /**
     * Constructs a new REST client instance for the given <b>$server</b>.
     *
     * @param string $server Remote server location. Must include the used protocol.
     */
    public function __construct( $server )
    {
        $url = parse_url( rtrim( $server, '/' ) );
        $url += array(
            'scheme' => 'http',
            'host'   => null,
            'port'   => null,
            'path'   => null,
        );

        $this->server = $url['scheme'] . '://' . $url['host'];
        if ( $url['port'] )
        {
            $this->server .= ':' . $url['port'];
        }
        $this->server .= $url['path'];
    }

    /**
     * Adds an additional request header.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addHeader( $name, $value )
    {
        $this->header[$name] = "{$name}: {$value}";
    }

    /**
     * Execute a HTTP GET request to the remote server
     *
     * Returns the raw response from the remote server.
     *
     * @param string $path
     * @param array $query
     * @param mixed $body
     * @return mixed
     */
    public function get( $path, array $query = null, $body = null )
    {
        if ( $query )
        {
            $path .= '?' . http_build_query( $query );
        }
        return $this->request( self::GET, $path, $body );
    }

    /**
     * Execute a HTTP POST request to the remote server
     *
     * Returns the raw response from the remote server.
     *
     * @param string $path
     * @param mixed $body
     * @return mixed
     */
    public function post( $path, $body = null )
    {
        return $this->request( self::POST, $path, $body );
    }

    /**
     * Execute a HTTP PUT request to the remote server
     *
     * Returns the raw response from the remote server.
     *
     * @param string $path
     * @param mixed $body
     * @return mixed
     */
    public function put( $path, $body = null )
    {
        return $this->request( self::PUT, $path, $body );
    }

    /**
     * Execute a HTTP request to the remote server
     *
     * Returns the raw response from the remote server.
     *
     * @param string $method
     * @param string $path
     * @param mixed $body
     * @return mixed
     */
    public function request( $method, $path, $body = null )
    {
        return file_get_contents(
            $this->server . $path,
            false,
            stream_context_create(
                array(
                    'http' => array(
                        'method'        => $method,
                        'content'       => $body,
                        'ignore_errors' => true,
                        'header'        => join( "\r\n", $this->header )
                    ),
                )
            )
        );
    }
}