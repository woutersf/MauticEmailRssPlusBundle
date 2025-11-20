<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

class RssPlusFeed
{
    public const TABLE_NAME = 'rssplus_feeds';

    private $id;
    private $name;
    private $machineName;
    private $rssUrl;
    private $rssFields;
    private $button = '1';
    private $token = '0';
    private $createdAt;
    private $createdBy;
    private $updatedBy;

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable(self::TABLE_NAME);
        $builder->setCustomRepositoryClass(RssPlusFeedRepository::class);

        $builder->addId();

        $builder->createField('name', 'string')
            ->columnName('name')
            ->length(255)
            ->build();

        $builder->createField('machineName', 'string')
            ->columnName('machine_name')
            ->length(255)
            ->build();

        $builder->createField('rssUrl', 'string')
            ->columnName('rss_url')
            ->length(500)
            ->build();

        $builder->createField('rssFields', 'text')
            ->columnName('rss_fields')
            ->nullable()
            ->build();

        $builder->createField('button', 'string')
            ->columnName('button')
            ->length(1)
            ->build();

        $builder->createField('token', 'string')
            ->columnName('token')
            ->length(1)
            ->build();

        $builder->createField('createdAt', 'datetime')
            ->columnName('created_at')
            ->build();

        $builder->createField('createdBy', 'integer')
            ->columnName('created_by')
            ->nullable()
            ->build();

        $builder->createField('updatedBy', 'integer')
            ->columnName('updated_by')
            ->nullable()
            ->build();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getMachineName(): ?string
    {
        return $this->machineName;
    }

    public function setMachineName(string $machineName): self
    {
        $this->machineName = $machineName;
        return $this;
    }

    public function getRssUrl(): ?string
    {
        return $this->rssUrl;
    }

    public function setRssUrl(string $rssUrl): self
    {
        $this->rssUrl = $rssUrl;
        return $this;
    }

    public function getRssFields(): ?string
    {
        return $this->rssFields;
    }

    public function setRssFields(?string $rssFields): self
    {
        $this->rssFields = $rssFields;
        return $this;
    }

    public function getButton(): string
    {
        return $this->button;
    }

    public function setButton(string $button): self
    {
        $this->button = $button;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?int $updatedBy): self
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }
}
