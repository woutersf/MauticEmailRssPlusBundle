<?php

declare(strict_types=1);

namespace MauticPlugin\MauticEmailRssPlusBundle\Migration;

use Doctrine\DBAL\Schema\Schema;
use Mautic\Migrations\AbstractMauticMigration;

final class Version20250119000000 extends AbstractMauticMigration
{
    public function up(Schema $schema): void
    {
        // Create rssplus_feeds table
        $feedsTable = $schema->createTable('rssplus_feeds');

        $feedsTable->addColumn('id', 'integer', [
            'autoincrement' => true,
            'notnull' => true,
        ]);

        $feedsTable->addColumn('name', 'string', [
            'length' => 255,
            'notnull' => true,
        ]);

        $feedsTable->addColumn('machine_name', 'string', [
            'length' => 255,
            'notnull' => true,
        ]);

        $feedsTable->addColumn('rss_url', 'string', [
            'length' => 500,
            'notnull' => true,
        ]);

        $feedsTable->addColumn('rss_fields', 'text', [
            'notnull' => false,
        ]);

        $feedsTable->addColumn('button', 'string', [
            'length' => 1,
            'notnull' => true,
            'default' => '1',
        ]);

        $feedsTable->addColumn('token', 'string', [
            'length' => 1,
            'notnull' => true,
            'default' => '0',
        ]);

        $feedsTable->addColumn('created_at', 'datetime', [
            'notnull' => true,
        ]);

        $feedsTable->addColumn('created_by', 'integer', [
            'notnull' => false,
        ]);

        $feedsTable->addColumn('updated_by', 'integer', [
            'notnull' => false,
        ]);

        $feedsTable->setPrimaryKey(['id']);
        $feedsTable->addIndex(['machine_name'], 'idx_rssplus_feeds_machine_name');

        // Create rssplus_templates table
        $templatesTable = $schema->createTable('rssplus_templates');

        $templatesTable->addColumn('id', 'integer', [
            'autoincrement' => true,
            'notnull' => true,
        ]);

        $templatesTable->addColumn('name', 'string', [
            'length' => 255,
            'notnull' => true,
        ]);

        $templatesTable->addColumn('content', 'text', [
            'notnull' => false,
        ]);

        $templatesTable->addColumn('created_at', 'datetime', [
            'notnull' => true,
        ]);

        $templatesTable->addColumn('updated_at', 'datetime', [
            'notnull' => false,
        ]);

        $templatesTable->addColumn('updated_by', 'integer', [
            'notnull' => false,
        ]);

        $templatesTable->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('rssplus_feeds');
        $schema->dropTable('rssplus_templates');
    }
}
