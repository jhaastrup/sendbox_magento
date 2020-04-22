<?php
/**
* Copyright © 2020 Sendbox. All rights reserved.
* See COPYING.txt for license details.
* Author: Sendbox
* Contributor : Adejoke
*/


namespace Sendbox\Sendboxshipping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Get sendbox table
        $tableName = $installer->getTable('sendbox');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'app_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'App_id'
                )

                ->addColumn(
                    'client_secret',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'Client_secret'
                )
                ->addColumn(
                    'username',
                   Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'Username'
                ) ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'Email'
                )  
                ->addColumn(
                    'phone',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'Phone'
                ) 
                ->addColumn(
                    'state',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'State'
                )
                ->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'City'
                )
    
                ->addColumn(
                    'rates',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'Rates'
                )
    
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'Email'
                )
    
                ->addColumn(
                    'pickup_type',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                      'Pickup_type'
                )
    
                ->addColumn(
                    'auth_token',
                  Table::TYPE_TEXT,
                    2000,
                    ['nullable' => false, 'default' => ''],
                      'Auth_token'
                )
    
                ->addColumn(
                    'refresh_token',
                    Table::TYPE_TEXT,
                    2000,
                    ['nullable' => false, 'default' => ''],
                      'Refresh_token'
                )
                ->setComment('Sendbox Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
