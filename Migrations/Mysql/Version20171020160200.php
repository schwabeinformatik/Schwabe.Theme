<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Set up initial table structure
 */
class Version20171020160200 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Set up font table structure';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE cm_neos_thememodule_domain_model_font (family varchar(255) COLLATE utf8_unicode_ci NOT NULL, category varchar(255) COLLATE utf8_unicode_ci NOT NULL, fontsource varchar(255) COLLATE utf8_unicode_ci NOT NULL, variants longtext COLLATE utf8_unicode_ci NOT NULL, subsets longtext COLLATE utf8_unicode_ci NOT NULL, files longtext COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY (family)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE cm_neos_thememodule_domain_model_font');
    }
}
