<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

class RssPlusTemplate
{
    public const TABLE_NAME = 'rssplus_templates';

    private $id;
    private $name;
    private $content;
    private $createdAt;
    private $updatedAt;
    private $updatedBy;

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable(self::TABLE_NAME);
        $builder->setCustomRepositoryClass(RssPlusTemplateRepository::class);

        $builder->addId();

        $builder->createField('name', 'string')
            ->columnName('name')
            ->length(255)
            ->build();

        $builder->createField('content', 'text')
            ->columnName('content')
            ->nullable()
            ->build();

        $builder->createField('createdAt', 'datetime')
            ->columnName('created_at')
            ->build();

        $builder->createField('updatedAt', 'datetime')
            ->columnName('updated_at')
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
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
