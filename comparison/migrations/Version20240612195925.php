<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612195925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, format VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_variant (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, variant_code VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, material VARCHAR(255) NOT NULL, INDEX IDX_209AA41D4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_variant_file (id INT AUTO_INCREMENT NOT NULL, product_variant_id INT NOT NULL, file_id INT DEFAULT NULL, to_delete TINYINT(1) NOT NULL, INDEX IDX_EEC382EEA80EF684 (product_variant_id), INDEX IDX_EEC382EE93CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41D4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_variant_file ADD CONSTRAINT FK_EEC382EEA80EF684 FOREIGN KEY (product_variant_id) REFERENCES product_variant (id)');
        $this->addSql('ALTER TABLE product_variant_file ADD CONSTRAINT FK_EEC382EE93CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE product ADD weight VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_variant DROP FOREIGN KEY FK_209AA41D4584665A');
        $this->addSql('ALTER TABLE product_variant_file DROP FOREIGN KEY FK_EEC382EEA80EF684');
        $this->addSql('ALTER TABLE product_variant_file DROP FOREIGN KEY FK_EEC382EE93CB796C');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE product_variant');
        $this->addSql('DROP TABLE product_variant_file');
        $this->addSql('ALTER TABLE product DROP weight');
    }
}
