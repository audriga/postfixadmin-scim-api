<?php

namespace Opf\DataAccess\Domains;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Opf\Models\PFA\PfaDomain;
use Opf\Util\Util;
use PDO;
use PDOException;

class PfaDomainDataAccess
{
    /** @var PDO $dbConnection */
    private PDO $dbConnection;

    /** @var \Monolog\Logger $logger */
    private \Monolog\Logger $logger;

    public function __construct()
    {
        // Instantiate our logger
        $this->logger = new Logger(PfaDomainDataAccess::class);
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../../logs/app.log', Logger::DEBUG));

        // Try to obtain a DB connection
        try {
            $this->dbConnection = Util::getDbConnection();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    public function getAll(): ?array
    {
        if (isset($this->dbConnection)) {
            // Domain data is contained in the 'domain' DB table of PFA's DB
            // TODO: Should we also somehow consider doing a JOIN with the 'alias_domain' DB table?
            $selectStatement = $this->dbConnection->query("SELECT * FROM domain");

            if ($selectStatement) {
                $pfaDomains = [];
                $pfaDomainsRaw = $selectStatement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($pfaDomainsRaw as $domain) {
                    $pfaDomain = new PfaDomain();
                    $pfaDomain->mapFromArray($domain);
                    $pfaDomains[] = $pfaDomain;
                }
                return $pfaDomains;
            }

            $this->logger->error("Couldn't read all domains from PFA. SELECT query to DB failed");
        }

        $this->logger->error("Couldn't connect to DB while attempting to read all domains from PFA");
        return null;
    }

    public function getOneById(?string $id): ?PfaDomain
    {
        if (isset($id) && !empty($id)) {
            if (isset($this->dbConnection)) {
                try {
                    $selectOnePreparedStatement = $this->dbConnection->prepare(
                        "SELECT * FROM domain
                         WHERE domain = ?"
                    );

                    $selectRes = $selectOnePreparedStatement->execute([$id]);

                    if ($selectRes) {
                        $pfaDomainsRaw = $selectOnePreparedStatement->fetchAll(PDO::FETCH_ASSOC);
                        if ($pfaDomainsRaw) {
                            $pfaDomain = new PfaDomain();
                            $pfaDomain->mapFromArray($pfaDomainsRaw[0]);
                            return $pfaDomain;
                        } else {
                            return null;
                        }
                    } else {
                        return null;
                    }
                } catch (PDOException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }

        $this->logger->error(
            "Argument provided to getOneById in class " . PfaDomainDataAccess::class . " is not set or empty"
        );
        return null;
    }

    public function create(PfaDomain $domainToCreate): ?PfaDomain
    {
        $dateNow = date('Y-m-d H:i:s');

        if (isset($this->dbConnection)) {
            try {
                $insertStatement = $this->dbConnection->prepare(
                    "INSERT INTO domain
                    (domain, description, aliases, mailboxes, maxquota, transport,
                     backupmx, created, modified, active, password_expiry)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $insertRes = $insertStatement->execute([
                    $domainToCreate->getDomain(),
                    $domainToCreate->getDescription() !== null ? $domainToCreate->getDescription() : "",
                    $domainToCreate->getAliases() !== null ? (int) $domainToCreate->getAliases() : 0,
                    $domainToCreate->getMailboxes() !== null ? (int) $domainToCreate->getMailboxes() : 0,
                    $domainToCreate->getMaxQuota() !== null ? (int) $domainToCreate->getMaxQuota() : 0,
                    $domainToCreate->getTransport() !== null ? $domainToCreate->getTransport() : "",
                    $domainToCreate->getBackupMx() !== null ? (int) $domainToCreate->getBackupMx() : 0,
                    $dateNow,
                    $dateNow,
                    $domainToCreate->getActive(),
                    $domainToCreate->getPasswordExpiry()
                ]);

                if ($insertRes) {
                    $this->logger->info("Created domain " . $domainToCreate->getDomain());
                    return $this->getOneById($domainToCreate->getDomain());
                } else {
                    return null;
                }
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
        } else {
            $this->logger->error("DB connection not available");
        }
        $this->logger->error("Error creating domain");
        return null;
    }

    public function update(string $domain, PfaDomain $domainToUpdate): ?PfaDomain
    {
        $dateNow = date('Y-m-d H:i:s');

        if (isset($this->dbConnection)) {
            try {
                $query = "";
                $values = array();
                if ($domainToUpdate->getDescription() !== null) {
                    $query = $query . "description = ?, ";
                    $values[] = $domainToUpdate->getDescription();
                }
                if ($domainToUpdate->getAliases() !== null) {
                    $query = $query . "aliases = ?, ";
                    $values[] = $domainToUpdate->getAliases();
                }
                if ($domainToUpdate->getMailboxes() !== null) {
                    $query = $query . "mailboxes = ?, ";
                    $values[] = $domainToUpdate->getMailboxes();
                }
                if ($domainToUpdate->getMaxQuota() !== null) {
                    $query = $query . "maxquota = ?, ";
                    $values[] = $domainToUpdate->getMaxQuota();
                }
                if ($domainToUpdate->getTransport() !== null) {
                    $query = $query . "transport = ?, ";
                    $values[] = $domainToUpdate->getTransport();
                }
                if ($domainToUpdate->getBackupMx() !== null) {
                    $query = $query . "backupmx = ?, ";
                    $values[] = $domainToUpdate->getBackupMx();
                }
                if ($domainToUpdate->getActive() !== null) {
                    $query = $query . "active = ?, ";
                    $values[] = $domainToUpdate->getActive();
                }
                if ($domainToUpdate->getPasswordExpiry() !== null) {
                    $query = $query . "password_expiry = ?, ";
                    $values[] = $domainToUpdate->getPasswordExpiry();
                }

                if (empty($query)) {
                    $this->logger->error("No domain properties to update");
                    return null;
                }

                $query = $query . "modified = ? ";
                $values[] = $dateNow;
                $values[] = $domain;

                // Since in PFA the domain column in the domain table is the primary key and is unique,
                // we use domain in this case as an id that serves the purpose of a unique identifier
                // TODO: should we use a variable for the table name?
                $updateStatement = $this->dbConnection->prepare(
                    "UPDATE domain SET " . $query . " WHERE domain = ?"
                );

                $updateRes = $updateStatement->execute($values);

                // In case the update was successful, return the domain that was just updated
                if ($updateRes) {
                    $this->logger->info("Updated domain " . $domain);
                    return $this->getOneById($domain);
                } else {
                    $this->logger->error("Error updating domain " . $domain);
                    return null;
                }
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
        } else {
            $this->logger->error("Error updating domain " . $domain . " - DB connection unavailable");
        }
        $this->logger->error("Error updating domain " . $domain);
        return null;
    }

    public function delete(string $domain): bool
    {
        if (isset($this->dbConnection)) {
            try {
                // TODO: should we use a variable for the table name?
                $deleteStatement = $this->dbConnection->prepare(
                    "DELETE FROM domain WHERE domain = ?"
                );
                $deleteRes = $deleteStatement->execute([$domain]);

                // In case the delete was successful, return true
                if ($deleteRes) {
                    $this->logger->info("Deleted domain " . $domain);
                    return true;
                } else {
                    // Otherwise, just return false
                    return false;
                }
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
        } else {
            $this->logger->error("Error deleting domain " . $domain . " - DB connection unavailable");
        }
        $this->logger->error("Error deleting domain " . $domain);
        return false;
    }
}
