<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016113334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE magazine (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, issn VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, edition_year VARCHAR(255) DEFAULT NULL, edition_month VARCHAR(255) DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, number VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, is_special_edition TINYINT(1) NOT NULL, publisher_id INT DEFAULT NULL, editorial_line_id INT DEFAULT NULL, INDEX IDX_378C2FE440C86FCE (publisher_id), INDEX IDX_378C2FE476530BF8 (editorial_line_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE magazine_category (magazine_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_39C94B2F3EB84A1D (magazine_id), INDEX IDX_39C94B2F12469DE2 (category_id), PRIMARY KEY(magazine_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE magazine ADD CONSTRAINT FK_378C2FE440C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
        $this->addSql('ALTER TABLE magazine ADD CONSTRAINT FK_378C2FE476530BF8 FOREIGN KEY (editorial_line_id) REFERENCES editorial_line (id)');
        $this->addSql('ALTER TABLE magazine_category ADD CONSTRAINT FK_39C94B2F3EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE magazine_category ADD CONSTRAINT FK_39C94B2F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magazine DROP FOREIGN KEY FK_378C2FE440C86FCE');
        $this->addSql('ALTER TABLE magazine DROP FOREIGN KEY FK_378C2FE476530BF8');
        $this->addSql('ALTER TABLE magazine_category DROP FOREIGN KEY FK_39C94B2F3EB84A1D');
        $this->addSql('ALTER TABLE magazine_category DROP FOREIGN KEY FK_39C94B2F12469DE2');
        $this->addSql('DROP TABLE magazine');
        $this->addSql('DROP TABLE magazine_category');
    }
}
