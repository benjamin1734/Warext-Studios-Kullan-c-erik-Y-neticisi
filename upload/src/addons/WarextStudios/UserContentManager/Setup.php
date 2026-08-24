<?php

namespace WarextStudios\UserContentManager;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1(): void
    {
        $this->createTables();
    }

    public function upgrade1000100Step1(): void
    {
        $this->createCoreTables();
    }

    public function upgrade1001000Step1(): void
    {
        $this->createBulkOperationTable();
    }

    public function uninstallStep1(): void
    {
        $sm = $this->schemaManager();
        $sm->dropTable('xf_warext_ucm_bulk_operation');
        $sm->dropTable('xf_warext_ucm_warning_content');
        $sm->dropTable('xf_warext_ucm_action_log');
    }

    protected function createTables(): void
    {
        $this->createCoreTables();
        $this->createBulkOperationTable();
    }

    protected function createCoreTables(): void
    {
        $sm = $this->schemaManager();
        $sm->createTable('xf_warext_ucm_action_log', function (Create $table)
        {
            $table->addColumn('action_log_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('actor_user_id', 'int')->unsigned();
            $table->addColumn('target_user_id', 'int')->unsigned();
            $table->addColumn('content_type', 'varbinary', 25);
            $table->addColumn('content_id', 'int')->unsigned();
            $table->addColumn('action', 'varbinary', 50);
            $table->addColumn('reason', 'varchar', 255)->setDefault('');
            $table->addColumn('metadata', 'mediumblob')->nullable();
            $table->addColumn('log_date', 'int')->unsigned();
            $table->addPrimaryKey('action_log_id');
            $table->addKey(['target_user_id', 'log_date'], 'target_date');
            $table->addKey(['actor_user_id', 'log_date'], 'actor_date');
        });
        $sm->createTable('xf_warext_ucm_warning_content', function (Create $table)
        {
            $table->addColumn('warning_content_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('warning_id', 'int')->unsigned();
            $table->addColumn('content_type', 'varbinary', 25);
            $table->addColumn('content_id', 'int')->unsigned();
            $table->addColumn('link_date', 'int')->unsigned();
            $table->addPrimaryKey('warning_content_id');
            $table->addUniqueKey(['warning_id', 'content_type', 'content_id'], 'warning_content');
        });
    }

    protected function createBulkOperationTable(): void
    {
        $this->schemaManager()->createTable('xf_warext_ucm_bulk_operation', function (Create $table)
        {
            $table->addColumn('operation_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('operation_key', 'varbinary', 40);
            $table->addColumn('actor_user_id', 'int')->unsigned();
            $table->addColumn('target_user_id', 'int')->unsigned();
            $table->addColumn('content_type', 'varbinary', 25);
            $table->addColumn('action', 'varbinary', 50);
            $table->addColumn('filters', 'mediumblob')->nullable();
            $table->addColumn('options', 'mediumblob')->nullable();
            $table->addColumn('last_content_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('matched_count', 'int')->unsigned()->setDefault(0);
            $table->addColumn('processed_count', 'int')->unsigned()->setDefault(0);
            $table->addColumn('skipped_count', 'int')->unsigned()->setDefault(0);
            $table->addColumn('failed_count', 'int')->unsigned()->setDefault(0);
            $table->addColumn('status', 'varbinary', 20)->setDefault('queued');
            $table->addColumn('last_error', 'mediumblob')->nullable();
            $table->addColumn('lock_token', 'varbinary', 32)->setDefault('');
            $table->addColumn('lock_expires', 'int')->unsigned()->setDefault(0);
            $table->addColumn('created_date', 'int')->unsigned();
            $table->addColumn('updated_date', 'int')->unsigned();
            $table->addPrimaryKey('operation_id');
            $table->addUniqueKey('operation_key', 'operation_key');
            $table->addKey(['target_user_id', 'status', 'updated_date'], 'target_status');
            $table->addKey(['actor_user_id', 'status', 'updated_date'], 'actor_status');
        });
    }
}
