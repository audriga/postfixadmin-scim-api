<?php

namespace Opf\Util\Authorization;

use Exception;
use Opf\Util\Util;
use PDO;
use Psr\Container\ContainerInterface;

class PfaAuthorizer
{
    private const ROLE_SUPERADMIN = 1;
    private const ROLE_DOMAINADMIN = 2;
    private const ROLE_USER = 3;

    /** @var \Monolog\Logger */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(\Monolog\Logger::class);
    }

    public function authorize(string $username, array $authorizationInfo): bool
    {
        // Try to obtain a DB connection
        try {
            $dbConnection = Util::getDbConnection();
        } catch (Exception $e) {
            // If something went wrong here, we return false
            $this->logger->error($e->getMessage());
            return false;
        }

        // Determine role of user, based on $username
        $sqlAdmin = "SELECT admin.username, admin.superadmin, domain_admins.domain
                     FROM admin
                     INNER JOIN domain_admins ON admin.username = domain_admins.username
                     WHERE admin.active = 1 AND domain_admins.active = 1
                     AND admin.username = ?
                     LIMIT 1";
        $selectStatementAdmin = $dbConnection->prepare($sqlAdmin);
        $selectStatementAdmin->execute([$username]);
        $dbResult = $selectStatementAdmin->fetchAll(PDO::FETCH_ASSOC);

        $role = null;
        $domain = null;

        if (!empty($dbResult) && !empty($dbResult[0])) {
            if (strcmp($dbResult[0]['superadmin'], "1") === 0) {
                $role = self::ROLE_SUPERADMIN;
                $domain = 'ALL';
            } else {
                $role = self::ROLE_DOMAINADMIN;
                $domain = $dbResult[0]['domain'];
            }
        } else {
            $sqlMailbox = "SELECT username
                           FROM mailbox
                           WHERE username = ?
                           LIMIT 1";
            $selectStatementMailbox = $dbConnection->prepare($sqlMailbox);
            $selectStatementMailbox->execute([$username]);
            $dbResult = $selectStatementMailbox->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($dbResult) && !empty($dbResult[0])) {
                $role = self::ROLE_USER;
            }
        }

        // If role is still null, then the user does not exist
        // (Note: this should theoretically never occur, since authentication
        // should've already detected this and we shouldn't have even reached authorization)
        if (!isset($role)) {
            $this->logger->error("No user role could be set for authorization");
            return false;
        }

        // Intuition here is that super admins can do everything and we can exit this function early
        // in case of a super admin role
        if ($role === self::ROLE_SUPERADMIN) {
            return true;
        }

        // TODO: For now, we only allow super admins during authorization
        // Hence, if we've reached this point, then the role is not super admin
        // and we thus return false, such that authorization fails
        return false;
    }
}
