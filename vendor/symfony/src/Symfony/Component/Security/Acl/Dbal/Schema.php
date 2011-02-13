<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Acl\Dbal;

use Doctrine\DBAL\Schema\Schema as BaseSchema;

/**
 * The schema used for the ACL system.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Schema extends BaseSchema
{
    protected $options;

    /**
     * Constructor
     *
     * @param array $options the names for tables
     * @return void
     */
    public function __construct(array $options)
    {
        parent::__construct();

        $this->options = $options;

        $this->addClassTable();
        $this->addSecurityIdentitiesTable();
        $this->addObjectIdentitiesTable();
        $this->addObjectIdentityAncestorsTable();
        $this->addEntryTable();
    }

    /**
     * Adds the class table to the schema
     *
     * @return void
     */
    protected function addClassTable()
    {
        $table = $this->createTable($this->options['class_table_name']);
        $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => 'auto'));
        $table->addColumn('class_type', 'string', array('length' => 200));
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('class_type'));
    }

    /**
     * Adds the entry table to the schema
     *
     * @return void
     */
    protected function addEntryTable()
    {
        $table = $this->createTable($this->options['entry_table_name']);

        $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => 'auto'));
        $table->addColumn('class_id', 'integer', array('unsigned' => true));
        $table->addColumn('object_identity_id', 'integer', array('unsigned' => true, 'notnull' => false));
        $table->addColumn('field_name', 'string', array('length' => 50, 'notnull' => false));
        $table->addColumn('ace_order', 'smallint', array('unsigned' => true));
        $table->addColumn('security_identity_id', 'integer', array('unsigned' => true));
        $table->addColumn('mask', 'integer');
        $table->addColumn('granting', 'boolean');
        $table->addColumn('granting_strategy', 'string', array('length' => 30));
        $table->addColumn('audit_success', 'boolean', array('default' => 0));
        $table->addColumn('audit_failure', 'boolean', array('default' => 0));

        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('class_id', 'object_identity_id', 'field_name', 'ace_order'));
        $table->addIndex(array('class_id', 'object_identity_id', 'security_identity_id'));

        $table->addForeignKeyConstraint($this->getTable($this->options['class_table_name']), array('class_id'), array('id'), array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
        $table->addForeignKeyConstraint($this->getTable($this->options['oid_table_name']), array('object_identity_id'), array('id'), array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
        $table->addForeignKeyConstraint($this->getTable($this->options['sid_table_name']), array('security_identity_id'), array('id'), array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
    }

    /**
     * Adds the object identity table to the schema
     *
     * @return void
     */
    protected function addObjectIdentitiesTable()
    {
        $table = $this->createTable($this->options['oid_table_name']);

        $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => 'auto'));
        $table->addColumn('class_id', 'integer', array('unsigned' => true));
        $table->addColumn('object_identifier', 'string', array('length' => 100));
        $table->addColumn('parent_object_identity_id', 'integer', array('unsigned' => true, 'notnull' => false));
        $table->addColumn('entries_inheriting', 'boolean', array('default' => 0));

        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('object_identifier', 'class_id'));
        $table->addIndex(array('parent_object_identity_id'));

        $table->addForeignKeyConstraint($table, array('parent_object_identity_id'), array('id'), array('onDelete' => 'RESTRICT', 'onUpdate' => 'RESTRICT'));
    }

    /**
     * Adds the object identity relation table to the schema
     *
     * @return void
     */
    protected function addObjectIdentityAncestorsTable()
    {
        $table = $this->createTable($this->options['oid_ancestors_table_name']);

        $table->addColumn('object_identity_id', 'integer', array('unsigned' => true));
        $table->addColumn('ancestor_id', 'integer', array('unsigned' => true));

        $table->setPrimaryKey(array('object_identity_id', 'ancestor_id'));

        $oidTable = $this->getTable($this->options['oid_table_name']);
        $table->addForeignKeyConstraint($oidTable, array('object_identity_id'), array('id'), array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
        $table->addForeignKeyConstraint($oidTable, array('ancestor_id'), array('id'), array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
    }

    /**
     * Adds the security identity table to the schema
     *
     * @return void
     */
    protected function addSecurityIdentitiesTable()
    {
        $table = $this->createTable($this->options['sid_table_name']);

        $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => 'auto'));
        $table->addColumn('identifier', 'string', array('length' => 200));
        $table->addColumn('username', 'boolean', array('default' => 0));

        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('identifier', 'username'));
    }
}