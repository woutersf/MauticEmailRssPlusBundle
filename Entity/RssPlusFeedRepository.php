<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

class RssPlusFeedRepository extends CommonRepository
{
    public function getTableAlias(): string
    {
        return 'f';
    }

    protected function addCatchAllWhereClause($q, $filter): array
    {
        return $this->addStandardCatchAllWhereClause($q, $filter, [
            'f.name',
            'f.machineName',
            'f.rssUrl',
        ]);
    }

    protected function addSearchCommandWhereClause($q, $filter): array
    {
        return $this->addStandardSearchCommandWhereClause($q, $filter);
    }

    public function getSearchCommands(): array
    {
        return $this->getStandardSearchCommands();
    }

    protected function getDefaultOrder(): array
    {
        return [
            ['f.name', 'ASC'],
        ];
    }
}
