<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016091846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE editorial_line (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, publisher_id INT DEFAULT NULL, INDEX IDX_C576EB1D40C86FCE (publisher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE editorial_line ADD CONSTRAINT FK_C576EB1D40C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE editorial_line DROP FOREIGN KEY FK_C576EB1D40C86FCE');
        $this->addSql('DROP TABLE editorial_line');
    }
}
