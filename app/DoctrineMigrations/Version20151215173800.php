<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151215173800 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D7E3C61F9');
        $this->addSql('ALTER TABLE post_rating DROP FOREIGN KEY FK_E8ABC2477E3C61F9');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E07E3C61F9');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B7E3C61F9');
        $this->addSql('DROP TABLE user_account');
        $this->addSql('DROP INDEX IDX_5A8A6C8D7E3C61F9 ON post');
        $this->addSql('ALTER TABLE post ADD user_id VARCHAR(32) NOT NULL, DROP owner_id');
        $this->addSql('DROP INDEX IDX_E8ABC2477E3C61F9 ON post_rating');
        $this->addSql('ALTER TABLE post_rating ADD user_id VARCHAR(32) NOT NULL, DROP owner_id');
        $this->addSql('DROP INDEX IDX_FDA8C6E07E3C61F9 ON reply');
        $this->addSql('ALTER TABLE reply ADD user_id VARCHAR(32) NOT NULL, DROP owner_id');
        $this->addSql('DROP INDEX IDX_9D40DE1B7E3C61F9 ON topic');
        $this->addSql('ALTER TABLE topic ADD user_id VARCHAR(32) NOT NULL, DROP owner_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_account (id CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:guid)\', username VARCHAR(64) NOT NULL COLLATE utf8_unicode_ci, roles LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD owner_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:guid)\', DROP user_id');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D7E3C61F9 ON post (owner_id)');
        $this->addSql('ALTER TABLE post_rating ADD owner_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:guid)\', DROP user_id');
        $this->addSql('ALTER TABLE post_rating ADD CONSTRAINT FK_E8ABC2477E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E8ABC2477E3C61F9 ON post_rating (owner_id)');
        $this->addSql('ALTER TABLE reply ADD owner_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:guid)\', DROP user_id');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E07E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_FDA8C6E07E3C61F9 ON reply (owner_id)');
        $this->addSql('ALTER TABLE topic ADD owner_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:guid)\', DROP user_id');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9D40DE1B7E3C61F9 ON topic (owner_id)');
    }
}
