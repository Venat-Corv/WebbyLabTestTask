<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240323173933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create movie table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE movies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            release_year VARCHAR(255) NOT NULL,
            format ENUM(\'DVD\', \'Blu-ray\', \'VHS\') NOT NULL
        )');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE movies');
    }
}
