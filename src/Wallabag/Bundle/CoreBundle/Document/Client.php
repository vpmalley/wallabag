<?php

namespace Wallabag\Bundle\CoreBundle\Document;

use FOS\OAuthServerBundle\Document\Client as BaseClient;

/**
 * Client
 *
 * @MongoDB\Document
 */
class Client extends BaseClient
{
    protected $id;
}
