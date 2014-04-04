<?php

/**
 * Yammer strategy for Opauth
 * based on https://developer.yammer.com/authentication/
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2014 Andrej Griniuk
 * @link         http://opauth.org
 * @package      Opauth.YammerStrategy
 * @license      MIT License
 */
class YammerStrategy extends OpauthStrategy
{
    /**
     * Compulsory config keys, listed as unassociative arrays
     */
    public $expects = array('client_id', 'client_secret');

    /**
     * Optional config keys, without predefining any default values.
     */
    public $optionals = array('redirect_uri');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}oauth2callback'
    );

    /**
     * Auth request
     */
    public function request()
    {
        $url = 'https://www.yammer.com/dialog/oauth';
        $params = array(
            'client_id'    => $this->strategy['client_id'],
            'redirect_uri' => $this->strategy['redirect_uri']
        );

        foreach ($this->optionals as $key) {
            if (empty($this->strategy[$key])) {
                $params[$key] = $this->strategy[$key];
            }
        }

        $this->clientGet($url, $params);
    }

    /**
     * Internal callback, after OAuth
     */
    public function oauth2callback()
    {
        if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
            $code = $_GET['code'];
            $url = 'https://www.yammer.com/oauth2/access_token.json';

            $params = array(
                'code'          => $code,
                'client_id'     => $this->strategy['client_id'],
                'client_secret' => $this->strategy['client_secret'],
                'redirect_uri'  => $this->strategy['redirect_uri'],
            );

            $response = $this->serverPost($url, $params, null, $headers);
            $results = json_decode($response, true);

            if (!empty($results) && !empty($results['access_token']['token'])) {
                $user = $this->user($results['access_token']['token']);

                $this->auth = array(
                    'uid'         => $user['id'],
                    'info'        => array(),
                    'credentials' => array(
                        'token' => $results['access_token']['token']
                    ),
                    'raw'         => $user
                );

                $this->mapProfile($user, 'full_name', 'info.name');
                $this->mapProfile($user, 'first_name', 'info.first_name');
                $this->mapProfile($user, 'last_name', 'info.last_name');
                $this->mapProfile($user, 'mugshot_url', 'info.image');
                $this->mapProfile($user, 'name', 'info.nickname');
                $this->mapProfile($user, 'web_url', 'info.urls.yammer');
                $this->mapProfile($user, 'contact.email_addresses.0.address', 'info.email');
                $this->mapProfile($user, 'location', 'info.location');
                $this->mapProfile($user, 'url', 'info.urls.yammer_api');
                $this->mapProfile($user, 'web_url', 'info.urls.website');

                $this->callback();
            } else {
                $error = array(
                    'code'    => 'access_token_error',
                    'message' => 'Failed when attempting to obtain access token',
                    'raw'     => array(
                        'response' => $response,
                        'headers'  => $headers
                    )
                );

                $this->errorCallback($error);
            }
        } else {
            $error = array(
                'code' => 'oauth2callback_error',
                'raw'  => $_GET
            );

            $this->errorCallback($error);
        }
    }

    /**
     * Queries Yammer API for user info
     *
     * @param string $access_token
     * @return array Parsed JSON results
     */
    private function user($access_token)
    {
        $options = array();
        $options['http']['header'] = "Authorization: Bearer {$access_token}";

        $user = $this->serverGet(
            'https://www.yammer.com/api/v1/users/current.json',
            array('access_token' => $access_token),
            $options,
            $headers
        );

        if (!empty($user)) {
            return $this->recursiveGetObjectVars(json_decode($user));
        } else {
            $error = array(
                'code'    => 'users_current',
                'message' => 'Failed when attempting to query Yammer API for user information',
                'raw'     => array(
                    'response' => $user,
                    'headers'  => $headers
                )
            );

            $this->errorCallback($error);
        }
    }
}
