<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Rename tables
 */
class Version20180605103800 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Rename tables';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings RENAME TO schwabe_theme_domain_model_settings');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_font RENAME TO schwabe_theme_domain_model_font');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

				$this->addSql('ALTER TABLE schwabe_theme_domain_model_settings RENAME TO cm_neos_thememodule_domain_model_settings');
        $this->addSql('ALTER TABLE schwabe_theme_domain_model_font RENAME TO cm_neos_thememodule_domain_model_font');
    }
}
