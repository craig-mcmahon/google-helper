<?php

namespace GoogleHelper;

class GoogleHelper
{

    /** @var \Google_Client */
    protected $client;

    /**
     * @param string $client_id
     * @param string $client_secret
     */
    public function __construct($client_id, $client_secret)
    {
        $client = new \Google_Client();
        // Get your credentials from the APIs Console
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $this->client = $client;
    }

    /**
     * Auth over command line
     */
    public function cmdLineAuth()
    {
        $str_auth_url = $this->client->createAuthUrl();
        //Request authorization
        print "Please visit:\n$str_auth_url\n\n";
        print "Please enter the auth code:\n";
        $str_auth_code = trim(fgets(STDIN));
        // Exchange authorization code for access token
        $str_access_token = $this->client->authenticate($str_auth_code);

        $this->client->setAccessToken($str_access_token);
    }

    /**
     * Get Google_Client
     * @return \Google_Client
     */
    public function getClient()
    {
        return $this->client;
    }

}