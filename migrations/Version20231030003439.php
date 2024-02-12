<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231030003439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE author ADD nb_books INT NOT NULL');
        $this->addSql('ALTER TABLE book ADD id INT AUTO_INCREMENT NOT NULL, ADD author_id_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A33169CCBE9A FOREIGN KEY (author_id_id) REFERENCES author (id)');
        $this->addSql('CREATE INDEX IDX_CBE5A33169CCBE9A ON book (author_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE author DROP nb_books');
        $this->addSql('ALTER TABLE book MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A33169CCBE9A');
        $this->addSql('DROP INDEX IDX_CBE5A33169CCBE9A ON book');
        $this->addSql('DROP INDEX `PRIMARY` ON book');
        $this->addSql('ALTER TABLE book DROP id, DROP author_id_id');
        $this->addSql('ALTER TABLE book ADD PRIMARY KEY (ref)');
    }
}
