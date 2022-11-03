<?php

namespace Opf\Util\Authentication;

use Exception;
use Opf\Util\Authorization\PfaAuthorizer;
use Opf\Util\Util;
use PDO;
use Psr\Container\ContainerInterface;

class PfaBasicAuthenticator implements AuthenticatorInterface
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
        $decodedCredentials = base64_decode($credentials);
        $username = explode(':', $decodedCredentials)[0];
        $password = explode(':', $decodedCredentials)[1];

        // Try to obtain a DB connection
        try {
            $dbConnection = Util::getDbConnection();
        } catch (Exception $e) {
            // If something went wrong here, we return false
            $this->logger->error($e->getMessage());
            return false;
        }

        $sqlAdmin = "SELECT admin.username, admin.password from admin
                     WHERE admin.username = ? LIMIT 1";
        $selectStatementAdmin = $dbConnection->prepare($sqlAdmin);

        // An array to store whatever we got from the DB
        $dbResult = [];

        if ($selectStatementAdmin->execute([$username])) {
            $dbResult = $selectStatementAdmin->fetchAll(PDO::FETCH_ASSOC);

            // If we couldn't get anything from the 'admin' DB table,
            // we try with the 'mailbox' DB table
            if (empty($dbResult)) {
                $sqlMailbox = "SELECT mailbox.username, mailbox.password from mailbox
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
            // we do a comparison of the password hashes
            // If the password check's also fine, then we move on to authorization
            if (!empty($dbResult) && !empty($dbResult[0])) {
                if (password_verify($password, $dbResult[0]['password'])) {
                    return $this->authorizer->authorize($username, $authorizationInfo);
                }
            }
        }

        $this->logger->error("There was an issue with SELECT statement for admin table");
        return false;
    }
}
