<?php

require __DIR__ . '/../vendor/autoload.php';

use Opf\ScimServer;

$scimServerPhpRoot = dirname(__DIR__);

$scimServer = new ScimServer($scimServerPhpRoot);

$configFilePath = __DIR__ . '/../config/config.php';
$scimServer->setConfig($configFilePath);

$dependencies = require __DIR__ . '/../src/Dependencies/pfa-dependencies.php';
$scimServer->setDependencies($dependencies);

$scimServerPhpAuthMiddleware = 'PfaAuthMiddleware';
$scimServer->setMiddleware(array($scimServerPhpAuthMiddleware));

$scimServer->run();
