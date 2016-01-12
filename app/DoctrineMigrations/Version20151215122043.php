<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151215122043 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E01F55203D');
        $this->addSql('DROP INDEX IDX_FDA8C6E01F55203D ON reply');
        $this->addSql('ALTER TABLE reply CHANGE topic_id post_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E04B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FDA8C6E04B89032C ON reply (post_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E04B89032C');
        $this->addSql('DROP INDEX IDX_FDA8C6E04B89032C ON reply');
        $this->addSql('ALTER TABLE reply CHANGE post_id topic_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E01F55203D FOREIGN KEY (topic_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FDA8C6E01F55203D ON reply (topic_id)');
    }
}
