<?php

declare(strict_types=1);

use Opf\Adapters\Domains\PfaDomainAdapter;
use Opf\Adapters\Users\PfaUserAdapter;
use Opf\DataAccess\Domains\PfaDomainDataAccess;
use Opf\DataAccess\Users\PfaUserDataAccess;
use Opf\Middleware\PfaAuthMiddleware;
use Opf\Repositories\Domains\PfaDomainsRepository;
use Opf\Repositories\Users\PfaUsersRepository;
use Opf\Util\Authentication\PfaBasicAuthenticator;
use Opf\Util\Authentication\PfaBearerAuthenticator;
use Opf\Util\Authentication\SimpleBearerAuthenticator;
use Psr\Container\ContainerInterface;

return [
    // Repositories
    'UsersRepository' => function (ContainerInterface $c) {
        return new PfaUsersRepository($c);
    },

    'DomainsRepository' => function (ContainerInterface $c) {
        return new PfaDomainsRepository($c);
    },

    // Data access classes
    'UsersDataAccess' => function () {
        return new PfaUserDataAccess();
    },

    'DomainsDataAccess' => function () {
        return new PfaDomainDataAccess();
    },

    // Adapters
    'UsersAdapter' => function () {
        return new PfaUserAdapter();
    },

    'DomainsAdapter' => function () {
        return new PfaDomainAdapter();
    },

    // Auth middleware
    'PfaAuthMiddleware' => function (ContainerInterface $c) {
        return new PfaAuthMiddleware($c);
    },

    // Authenticators
    'BasicAuthenticator' => function (ContainerInterface $c) {
        return new PfaBasicAuthenticator($c);
    },

    'BearerAuthenticator' => function (ContainerInterface $c) {
        return new PfaBearerAuthenticator($c);
    }
];
