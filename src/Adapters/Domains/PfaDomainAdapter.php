<?php

namespace Opf\Adapters\Domains;

use Opf\Adapters\AbstractAdapter;
use Opf\Models\PFA\PfaDomain;
use Opf\Models\SCIM\Custom\Domains\Domain;
use Opf\Models\SCIM\Standard\Meta;

class PfaDomainAdapter extends AbstractAdapter
{
    public function getSCIMDomain(?PfaDomain $pfaDomain): ?Domain
    {
        if ($pfaDomain === null) {
            return null;
        }
        $scimDomain = new Domain();
        $scimDomain->setId($pfaDomain->getDomain());
        $scimDomain->setDomainName($pfaDomain->getDomain());
        $scimDomain->setDescription($pfaDomain->getDescription());
        $scimDomain->setMaxAliases($pfaDomain->getAliases());
        $scimDomain->setMaxMailboxes($pfaDomain->getMailboxes());
        $scimDomain->setMaxQuota($pfaDomain->getMaxQuota());
        $scimDomain->setUsedQuota($pfaDomain->getQuota());

        $scimDomainMeta = new Meta();
        $scimDomainMeta->setResourceType("Domain");
        $scimDomainMeta->setCreated($pfaDomain->getCreated());
        $scimDomainMeta->setLastModified($pfaDomain->getModified());
        $scimDomain->setMeta($scimDomainMeta);

        $scimDomain->setActive($pfaDomain->getActive());

        return $scimDomain;
    }

    public function getPfaDomain(?Domain $scimDomain): ?PfaDomain
    {
        if ($scimDomain === null) {
            return null;
        }
        $pfaDomain = new PfaDomain();
        $pfaDomain->setDomain($scimDomain->getDomainName());
        $pfaDomain->setDescription($scimDomain->getDescription());
        $pfaDomain->setAliases($scimDomain->getMaxAliases());
        $pfaDomain->setMailboxes($scimDomain->getMaxMailboxes());
        $pfaDomain->setMaxQuota($scimDomain->getMaxQuota());
        $pfaDomain->setQuota($scimDomain->getUsedQuota());

        if ($scimDomain->getMeta() !== null) {
            if ($scimDomain->getMeta()->getCreated() !== null && !empty($scimDomain->getMeta()->getCreated())) {
                $pfaDomain->setCreated($scimDomain->getMeta()->getCreated());
            }

            if (
                $scimDomain->getMeta()->getLastModified() !== null
                && !empty($scimDomain->getMeta()->getLastModified())
            ) {
                $pfaDomain->setModified($scimDomain->getMeta()->getLastModified());
            }
        }

        $pfaDomain->setActive($scimDomain->getActive());

        return $pfaDomain;
    }
}
