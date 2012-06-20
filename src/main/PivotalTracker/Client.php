<?php
/**
 * This file is part of the PivotalTracker API component.
 *
 * @version 1.0
 * @copyright Copyright (c) 2012 Manuel Pichler
 * @license LGPL v3 license <http://www.gnu.org/licenses/lgpl
 */

namespace PivotalTracker;

/**
 * Simple Pivotal Tracker api client.
 *
 * This class is loosely based on the code from Joel Dare's PHP Pivotal Tracker
 * Class: https://github.com/codazoda/PHP-Pivotal-Tracker-Class
 */
class Client
{
    /**
     * Base url for the PivotalTracker service api.
     */
    const API_URL = 'https://www.pivotaltracker.com/services/v3';

    /**
     * Name of the context project.
     *
     * @var string
     */
    private $project;

    /**
     * Used client to perform rest operations.
     *
     * @var \PivotalTracker\Rest\Client
     */
    private $client;

    public function __construct( $project )
    {
        $this->client = new Rest\Client( self::API_URL );
        $this->client->addHeader( 'Content-type', 'application/x-www-form-urlencoded' );

        $this->project = $project;
    }

    /**
     * Authenticates this client against PivotalTracker.
     *
     * @param string $username
     * @param string $password
     * @return void
     */
    public function authenticate( $username, $password )
    {
        $this->client->addHeader( 'X-TrackerToken', $this->getToken( $username, $password ) );
    }

    /**
     * Adds a new story to PivotalTracker and returns the newly created story
     * object.
     *
     * @param string $type
     * @param string $name
     * @param string $description
     * @return \SimpleXMLElement
     */
    public function addStory( $type, $name, $description )
    {
        return simplexml_load_string(
            $this->client->post(
                "/projects/{$this->project}/stories",
                http_build_query(
                    array(
                        'story'  =>  array(
                            'story_type' => $type,
                            'name' => $name,
                            'description' => $description
                        )
                    )
                )
            )
        );
    }

    /**
     * Adds a new task with <b>$description</b> to the story identified by the
     * given <b>$storyId</b>.
     *
     * @param integer $storyId
     * @param string $description
     * @return \SimpleXMLElement
     */
    public function addTask( $storyId, $description )
    {
        return simplexml_load_string(
            $this->client->post(
                "/projects/{$this->project}/stories/$storyId/tasks",
                http_build_query(
                    array(
                        'task' => array( 'description' => $description )
                    )
                )
            )
        );
    }

    /**
     * Adds the given <b>$labels</b> to the story identified by <b>$story</b>
     * and returns the updated story instance.
     *
     * @param integer $storyId
     * @param array $labels
     * @return \SimpleXMLElement
     */
    public function addLabels( $storyId, array $labels )
    {
        return simplexml_load_string(
            $this->client->put(
                "/projects/{$this->project}/stories/$storyId",
                http_build_query(
                    array(
                        'story' => array( 'labels' => join( ',', $labels ) )
                    )
                )
            )
        );

    }

    /**
     * Returns all stories for the context project.
     *
     * @param array $filter
     * @return object
     */
    public function getStories( $filter = null )
    {
        return simplexml_load_string(
            $this->client->get(
                "/projects/{$this->project}/stories",
                $filter ? array( 'filter' => $filter ) : null
            )
        );
    }

    /**
     * Returns a list of projects for the currently authenticated user.
     *
     * @return \SimpleXMLElement
     */
    public function getProjects()
    {
        return simplexml_load_string(
            $this->client->get(
                "/projects"
            )
        );

    }

    /**
     * Returns the authentication token for the given username and password.
     *
     * @param string $username
     * @param string $password
     * @return string
     */
    public function getToken( $username, $password )
    {
        return (string) simplexml_load_string( $this->client->post(
            '/tokens/active',
            http_build_query(
                array(
                    'username' => $username,
                    'password' => $password
                )
            )
        ) )->guid;
    }
}