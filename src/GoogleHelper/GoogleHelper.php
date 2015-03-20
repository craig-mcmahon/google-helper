<?php

namespace GoogleHelper;

use GoogleHelper\Exception\Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GoogleHelper implements LoggerAwareInterface
{

    const AUTH_TYPE_CMD_LINE = 1;
    /** @var String */
    protected $accessToken = null;
    /** @var String|null */
    protected $refreshToken = null;
    /** @var \Google_Client */
    protected $client;
    /** @var LoggerInterface */
    protected $logger = null;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $accessType
     */
    public function __construct($clientId, $clientSecret, $accessType = 'offline')
    {
        $client = new \Google_Client();
        // Get your credentials from the APIs Console
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        // Apparently you need to force to get refresh token
        if ($accessType === 'offline') {
            $client->setApprovalPrompt('force');
        } else {
            $client->setApprovalPrompt('auto');
        }
        $client->setAccessType($accessType);
        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $this->client = $client;
    }


    /**
     * Authenticate
     * @param int $type
     * @throws Exception
     */
    public function auth($type = self::AUTH_TYPE_CMD_LINE)
    {
        if ($this->accessToken !== null) {
            // Already got an auth token
            $this->getLogger()->debug('Using existing accessToken');
            $this->client->setAccessToken($this->accessToken);
            if (!$this->client->isAccessTokenExpired()) {
                return;
            }
            $this->getLogger()->info('Existing accessToken no longer valid');
            if ($this->refreshToken !== null) {
                $this->getLogger()->debug('Using refreshToken');
                $this->client->refreshToken($this->refreshToken);
                return;
            }
        }
        switch($type) {
            case self::AUTH_TYPE_CMD_LINE:
                $this->cmdLineAuth();
                break;
            default:
                throw new Exception('Auth Type not valid');
        }
    }

    /**
     * Auth over command line
     */
    public function cmdLineAuth()
    {

        $authUrl = $this->client->createAuthUrl();
        //Request authorization
        print "Please visit:\n$authUrl\n\n";
        print "Please enter the auth code:\n";
        $authCode = trim(fgets(STDIN));
        // Exchange authorization code for access token
        $accessToken = $this->client->authenticate($authCode);

        $this->client->setAccessToken($accessToken);
        $this->accessToken = $accessToken;
        $this->refreshToken = $this->client->getRefreshToken();
    }

    /**
     * Get Google_Client
     * @return \Google_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Gets a logger
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }
        return $this->logger;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set Access Token, useful to set that was saved from previous request
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    /**
     * Get Access Token if set, useful to save for future requests
     * @return String|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get Refresh Token
     * @return null|String
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set Refresh Token
     * @param $token
     */
    public function setRefreshToken($token)
    {
        $this->refreshToken = $token;
    }
}