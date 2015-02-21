<?php

namespace GoogleHelper\Apps;

use GoogleHelper\GoogleHelper;

abstract class AppsHelper
{

    protected $helper;

    /**
     * @param GoogleHelper $helper
     */
    public function __construct(GoogleHelper $helper)
    {
        $helper->getClient()
           ->addScope('https://apps-apis.google.com/a/feeds/emailsettings/');
        $this->helper = $helper;
    }
}
