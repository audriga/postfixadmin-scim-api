<?php

namespace Opf\Repositories\Domains;

use Monolog\Logger;
use Opf\Models\SCIM\Custom\Domains\Domain;
use Opf\Repositories\Repository;
use Psr\Container\ContainerInterface;

class PfaDomainsRepository extends Repository
{
    /** @var \Monolog\Logger $logger */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->dataAccess = $this->container->get('DomainsDataAccess');
        $this->adapter = $this->container->get('DomainsAdapter');
        $this->logger = $this->container->get(Logger::class);
    }

    public function getAll(
        $filter = '',
        $startIndex = 0,
        $count = 0,
        $attributes = [],
        $excludedAttributes = []
    ): array {
        $pfaDomains = $this->dataAccess->getAll();
        $scimDomains = [];

        foreach ($pfaDomains as $pfaDomain) {
            $scimDomain = $this->adapter->getSCIMDomain($pfaDomain);
            $scimDomains[] = $scimDomain;
        }

        return $scimDomains;
    }

    public function getOneById(
        string $id,
        $filter = '',
        $startIndex = 0,
        $count = 0,
        $attributes = [],
        $excludedAttributes = []
    ): ?Domain {
        $pfaDomain = $this->dataAccess->getOneById($id);
        return $this->adapter->getSCIMDomain($pfaDomain);
    }

    public function create($object): ?Domain
    {
        $scimDomainToCreate = new Domain();
        $scimDomainToCreate->fromSCIM($object);

        // In case one of the required attributes is null, exit earlier and complain as well
        if (
            null === $scimDomainToCreate->getDomainName()
            || null === $scimDomainToCreate->getActive()
        ) {
            $this->logger->error(
                "\"domainName\" and \"active\" are required SCIM domain properties, but were not set"
            );
            return null;
        }

        $pfaDomainToCreate = $this->adapter->getPfaDomain($scimDomainToCreate);
        $pfaDomainCreated = $this->dataAccess->create($pfaDomainToCreate);

        if (isset($pfaDomainCreated)) {
            return $this->adapter->getSCIMDomain($pfaDomainCreated);
        }

        $this->logger->error("There was an issue with creating the domain");
        return null;
    }

    public function update(string $id, $object): ?Domain
    {
        $scimDomainToUpdate = new Domain();
        $scimDomainToUpdate->fromSCIM($object);

        $pfaDomainToUpdate = $this->adapter->getPfaDomain($scimDomainToUpdate);
        $pfaDomainUpdated = $this->dataAccess->update($id, $pfaDomainToUpdate);

        if (isset($pfaDomainUpdated)) {
            return $this->adapter->getSCIMDomain($pfaDomainUpdated);
        }

        $this->logger->error("There was an issue with updating the domain");
        return null;
    }

    public function delete(string $id): bool
    {
        return $this->dataAccess->delete($id);
    }
}
