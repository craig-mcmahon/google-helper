<?php

namespace GoogleHelper\Apps;

class EmailHelper extends AppsHelper
{

    const BASE_URL = 'https://apps-apis.google.com/a/feeds/emailsettings/2.0/';

    /**
     * Get a Users email signature
     *
     * @param string $domain
     * @param string $user
     * @return string|null Signature or null on error
     */
    public function getSignature($domain, $user)
    {
        $str_url = self::BASE_URL . "{$domain}/{$user}/signature";
        $request = new \Google_Http_Request($str_url, 'GET', null, null);

        $httpRequest = $this->helper->getClient()
           ->getAuth()
           ->authenticatedRequest($request);
        if ($httpRequest->getResponseHttpCode() == 200) {
            $xml_response = new \SimpleXMLElement($httpRequest->getResponseBody(), 0, false, 'apps', true);

            return (string)$xml_response->children('apps', true)
               ->attributes()->value;
        } else {
            // An error occurred.
            return null;
        }
    }

    /**
     * Set a Users email signature
     * @param string $domain
     * @param string $user
     * @param string $signature
     * @return bool success
     */
    public function setSignature($domain, $user, $signature)
    {
        $str_url     = self::BASE_URL . "{$domain}/{$user}/signature";
        $request     = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:apps="http://schemas.google.com/apps/2006">
    <apps:property name="signature" value="{$signature}" />
</atom:entry>
XML;
        $request     = new \Google_Http_Request($str_url, 'PUT', array('Content-Type' => 'application/atom+xml'),
           $request);
        $httpRequest = $this->helper->getClient()
           ->getAuth()
           ->authenticatedRequest($request);

        return ($httpRequest->getResponseHttpCode() == 200);
    }

    /**
     * Add a Send-as Alias
     * @param string $domain
     * @param string $user
     * @param string $name
     * @param string $address
     * @param string|null $replyTo
     * @param bool $makeDefault
     * @return bool Success
     */
    public function setSendAsAlias($domain, $user, $name, $address, $replyTo = null, $makeDefault = false)
    {
        $str_url = self::BASE_URL . "{$domain}/{$user}/sendas";
        $request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:apps="http://schemas.google.com/apps/2006">
    <apps:property name="name" value="{$name}" />
    <apps:property name="address" value="{$address}" />
XML;
        if ($replyTo !== null) {
            $request .= "<apps:property name=\"replyTo\" value=\"{$replyTo}\" />";
        }
        if ($makeDefault) {
            $request .= "<apps:property name=\"makeDefault\" value=\"true\" />";
        }
        $request .= "</atom:entry>";
        $request = new \Google_Http_Request($str_url, 'POST', array('Content-Type' => 'application/atom+xml'),
           $request);

        $httpRequest = $this->helper->getClient()
           ->getAuth()
           ->authenticatedRequest($request);

        return ($httpRequest->getResponseHttpCode() == 201);
    }
}
