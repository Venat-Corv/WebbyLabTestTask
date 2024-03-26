<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240325234110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create movie_stars table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE movie_stars (
            id INT AUTO_INCREMENT PRIMARY KEY,
            movie_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            CONSTRAINT fk_movie_stars_movies FOREIGN KEY (movie_id) REFERENCES movies (id)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE movie_stars');
    }
}
