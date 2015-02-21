<?php

namespace GoogleHelper\Drive;

use GoogleHelper\GoogleHelper;

abstract class DriveHelper
{

    // https://developers.google.com/drive/web/mime-types
    const MIME_AUDIO = 'application/vnd.google-apps.audio';
    const MIME_DOCUMENT = 'application/vnd.google-apps.document';
    const MIME_DRAWING = 'application/vnd.google-apps.drawing';
    const MIME_FILE = 'application/vnd.google-apps.file';
    const MIME_FOLDER = 'application/vnd.google-apps.folder';
    const MIME_FORM = 'application/vnd.google-apps.form';
    const MIME_FUSION_TABLE = 'application/vnd.google-apps.fusiontable';
    const MIME_PHOTO = 'application/vnd.google-apps.photo';
    const MIME_PRESENTATION = 'application/vnd.google-apps.presentation';
    const MIME_SCRIPT = 'application/vnd.google-apps.script';
    const MIME_SITES = 'application/vnd.google-apps.sites';
    const MIME_SPREADSHEET = 'application/vnd.google-apps.spreadsheet';
    const MIME_UNKNOWN = 'application/vnd.google-apps.unknown';
    const MIME_VIDEO = 'application/vnd.google-apps.video';


    /** @var  \Google_Service_Drive */
    protected $service;

    protected $helper;

    /**
     * @param GoogleHelper $helper
     */
    public function __construct(GoogleHelper $helper)
    {
        $this->helper = $helper;
        $client       = $helper->getClient();
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
        $this->service = new \Google_Service_Drive($client);
    }
}