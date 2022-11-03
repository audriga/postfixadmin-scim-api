<?php

namespace Opf\Util\Authentication;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Opf\Util\Authorization\PfaAuthorizer;
use Opf\Util\Util;
use PDO;
use Psr\Container\ContainerInterface;

class PfaBearerAuthenticator extends SimpleBearerAuthenticator
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \Opf\Util\Authorization\PfaAuthorizer */
    private $authorizer;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(\Monolog\Logger::class);
        $this->authorizer = $container->get(PfaAuthorizer::class);
    }

    public function authenticate(string $credentials, array $authorizationInfo): bool
    {
        $jwtPayload = [];
        $jwtSecret = Util::getConfigFile()['jwt']['secret'];
        try {
            $jwtPayload = (array) JWT::decode($credentials, new Key($jwtSecret, 'HS256'));
        } catch (Exception $e) {
            // If we land here, something was wrong with the JWT and auth has thus failed
            $this->logger->error($e->getMessage());
            return false;
        }

        // If there's no 'user' claim in the JWT, auth is considered to have failed
        if (!isset($jwtPayload['user']) || empty($jwtPayload['user'])) {
            $this->logger->error("No \"user\" claim found in JWT");
            return false;
        }

        // If we've reached thus far, we obtain the username from the JWT 'user' claim
        $username = $jwtPayload['user'];

        // Try to obtain a DB connection
        try {
            $dbConnection = Util::getDbConnection();
        } catch (Exception $e) {
            // If something went wrong here, we return false
            $this->logger->error($e->getMessage());
            return false;
        }

        // First check if the user is some type of admin
        $sqlAdmin = "SELECT admin.username from admin
                     WHERE admin.username = ? LIMIT 1";
        $selectStatementAdmin = $dbConnection->prepare($sqlAdmin);

        // An array to store whatever we got from the DB
        $dbResult = [];

        if ($selectStatementAdmin->execute([$username])) {
            $dbResult = $selectStatementAdmin->fetchAll(PDO::FETCH_ASSOC);

            // If we couldn't get anything from the 'admin' DB table,
            // we try with the 'mailbox' DB table
            if (empty($dbResult)) {
                $sqlMailbox = "SELECT mailbox.username from mailbox
                               WHERE mailbox.username = ? LIMIT 1";
                $selectStatementMailbox = $dbConnection->prepare($sqlMailbox);
                if ($selectStatementMailbox->execute([$username])) {
                    $dbResult = $selectStatementMailbox->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $this->logger->error("There was an issue with SELECT statement for mailbox table");
                    return false;
                }
            }

            // If we managed to find something in the 'admin' or in the 'mailbox' DB table,
            // then we can move on to authorization
	        if (!empty($dbResult)) {
                return $this->authorizer->authorize($username, $authorizationInfo);
            }
        }

        $this->logger->error("There was an issue with SELECT statement for admin table");
        return false;
    }
}
