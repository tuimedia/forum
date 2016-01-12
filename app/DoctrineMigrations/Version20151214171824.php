<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151214171824 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', topic_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', content LONGTEXT DEFAULT NULL, is_sticky TINYINT(1) NOT NULL, score INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_5A8A6C8D1F55203D (topic_id), INDEX IDX_5A8A6C8D7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reply (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', topic_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', content LONGTEXT DEFAULT NULL, score INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_FDA8C6E01F55203D (topic_id), INDEX IDX_FDA8C6E07E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE topic (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', namespace VARCHAR(32) NOT NULL, title VARCHAR(255) NOT NULL, external_ref VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_9D40DE1B7E3C61F9 (owner_id), INDEX IDX_9D40DE1B727ACA70 (parent_id), INDEX namespace_idx (namespace), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_account (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', username VARCHAR(64) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E01F55203D FOREIGN KEY (topic_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E07E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B727ACA70 FOREIGN KEY (parent_id) REFERENCES topic (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E01F55203D');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D1F55203D');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B727ACA70');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D7E3C61F9');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E07E3C61F9');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B7E3C61F9');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE reply');
        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE user_account');
    }
}
