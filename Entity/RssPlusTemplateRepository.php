<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

class RssPlusTemplateRepository extends CommonRepository
{
    public function getTableAlias(): string
    {
        return 't';
    }

    protected function addCatchAllWhereClause($q, $filter): array
    {
        return $this->addStandardCatchAllWhereClause($q, $filter, [
            't.name',
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
            ['t.name', 'ASC'],
        ];
    }
}
