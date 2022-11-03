<?php

namespace Opf\Models\PFA;

class PfaDomain
{
    /** @var string|null $domain */
    private ?string $domain = null;

    /** @var string|null $description */
    private ?string $description = null;

    /** @var int|null $aliases */
    private ?int $aliases = null;

    /** @var int|null $mailboxes */
    private ?int $mailboxes = null;

    /** @var int|null $maxQuota */
    private ?int $maxQuota = null;

    /** @var int|null $quota */
    private ?int $quota = null;

    /** @var string|null $transport */
    private ?string $transport = null;

    /** @var bool|null $backupMx */
    private ?bool $backupMx = null;

    /** @var string|null $created */
    private ?string $created = null;

    /** @var string|null $modified */
    private ?string $modified = null;

    /** @var bool|null $active */
    private ?bool $active = null;

    /** @var int|null $passwordExpiry */
    private ?int $passwordExpiry = null;

    public function mapFromArray(?array $properties = null): bool
    {
        $result = true;
        if ($properties !== null) {
            foreach ($properties as $key => $value) {
                if (strcasecmp($key, 'domain') === 0) {
                    $this->domain = $value;
                    continue;
                }
                if (strcasecmp($key, 'description') === 0) {
                    $this->description = $value;
                    continue;
                }
                if (strcasecmp($key, 'aliases') === 0) {
                    $this->aliases = $value;
                    continue;
                }
                if (strcasecmp($key, 'mailboxes') === 0) {
                    $this->mailboxes = $value;
                    continue;
                }
                if (strcasecmp($key, 'maxQuota') === 0) {
                    $this->maxQuota = $value;
                    continue;
                }
                if (strcasecmp($key, 'quota') === 0) {
                    $this->quota = $value;
                    continue;
                }
                if (strcasecmp($key, 'transport') === 0) {
                    $this->transport = $value;
                    continue;
                }
                if (strcasecmp($key, 'backupmx') === 0) {
                    $this->backupMx = $value;
                    continue;
                }
                if (strcasecmp($key, 'created') === 0) {
                    $this->created = $value;
                    continue;
                }
                if (strcasecmp($key, 'modified') === 0) {
                    $this->modified = $value;
                    continue;
                }
                if (strcasecmp($key, 'active') === 0) {
                    $this->active = $value;
                    continue;
                }
                if (strcasecmp($key, 'password_expiry') === 0) {
                    $this->passwordExpiry = $value;
                    continue;
                }
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int|null
     */
    public function getAliases(): ?int
    {
        return $this->aliases;
    }

    /**
     * @param int|null $aliases
     */
    public function setAliases(?int $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * @return int|null
     */
    public function getMailboxes(): ?int
    {
        return $this->mailboxes;
    }

    /**
     * @param int|null $mailboxes
     */
    public function setMailboxes(?int $mailboxes): void
    {
        $this->mailboxes = $mailboxes;
    }

    /**
     * @return int|null
     */
    public function getMaxQuota(): ?int
    {
        return $this->maxQuota;
    }

    /**
     * @param int|null $maxQuota
     */
    public function setMaxQuota(?int $maxQuota): void
    {
        $this->maxQuota = $maxQuota;
    }

    /**
     * @return int|null
     */
    public function getQuota(): ?int
    {
        return $this->quota;
    }

    /**
     * @param int|null $quota
     */
    public function setQuota(?int $quota): void
    {
        $this->quota = $quota;
    }

    /**
     * @return string|null
     */
    public function getTransport(): ?string
    {
        return $this->transport;
    }

    /**
     * @param string|null $transport
     */
    public function setTransport(?string $transport): void
    {
        $this->transport = $transport;
    }

    /**
     * @return bool|null
     */
    public function getBackupMx(): ?bool
    {
        return $this->backupMx;
    }

    /**
     * @param bool|null $backupMx
     */
    public function setBackupMx(?bool $backupMx): void
    {
        $this->backupMx = $backupMx;
    }

    /**
     * @return string|null
     */
    public function getCreated(): ?string
    {
        return $this->created;
    }

    /**
     * @param string|null $created
     */
    public function setCreated(?string $created): void
    {
        $this->created = $created;
    }

    /**
     * @return string|null
     */
    public function getModified(): ?string
    {
        return $this->modified;
    }

    /**
     * @param string|null $modified
     */
    public function setModified(?string $modified): void
    {
        $this->modified = $modified;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool|null $active
     */
    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int|null
     */
    public function getPasswordExpiry(): ?int
    {
        return $this->passwordExpiry;
    }

    /**
     * @param int|null $passwordExpiry
     */
    public function setPasswordExpiry(?int $passwordExpiry): void
    {
        $this->passwordExpiry = $passwordExpiry;
    }
}
