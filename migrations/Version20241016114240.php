<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016114240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, purchase_price DOUBLE PRECISION DEFAULT NULL, purchase_date VARCHAR(255) DEFAULT NULL, user_id INT DEFAULT NULL, magazine_id INT DEFAULT NULL, book_id INT DEFAULT NULL, INDEX IDX_6117D13BA76ED395 (user_id), UNIQUE INDEX UNIQ_6117D13B3EB84A1D (magazine_id), UNIQUE INDEX UNIQ_6117D13B16A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B3EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BA76ED395');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B3EB84A1D');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B16A2B381');
        $this->addSql('DROP TABLE purchase');
    }
}
