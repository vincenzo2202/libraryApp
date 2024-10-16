<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016113703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stock_magazine (id INT AUTO_INCREMENT NOT NULL, quantity INT DEFAULT NULL, magazine_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_784A7BA63EB84A1D (magazine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE stock_magazine ADD CONSTRAINT FK_784A7BA63EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock_magazine DROP FOREIGN KEY FK_784A7BA63EB84A1D');
        $this->addSql('DROP TABLE stock_magazine');
    }
}
