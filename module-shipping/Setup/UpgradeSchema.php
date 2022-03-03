<?php

namespace Myparcelasia\Shipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<' )) {
            $this->addShippingEstimateColumns($installer);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<' )) {
            $this->addShipmentTable($installer);
            $this->addConsignmentTable($installer);
            $this->addInvoiceTable($installer);
        }

        if (version_compare($context->getVersion(), '1.0.6', '<' )) {
            $this->addConsignmentStatusColumn($installer);
        }

        $setup->endSetup();
    }

    private function addShippingEstimateColumns(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable('sales_order'),
            'mpa_shipping_estimate',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'MyParcel Asia Shipping Estimate',
            ]
        );

        $connection->addColumn(
            $installer->getTable('sales_order'),
            'mpa_shipping_estimate_quoted_at',
            [
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'MyParcel Asia Shipping Estimate Quoted At',
            ]
        );

        $connection->addIndex(
            $installer->getTable('sales_order'),
            $installer->getIdxName('sales_invoice_grid', ['mpa_shipping_estimate']),
            ['mpa_shipping_estimate']
        );

        $connection->addIndex(
            $installer->getTable('sales_order'),
            $installer->getIdxName('sales_invoice_grid', ['mpa_shipping_estimate_quoted_at']),
            ['mpa_shipping_estimate_quoted_at']
        );

        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            'mpa_shipping_estimate',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'MyParcel Asia Shipping Estimate',
            ]
        );

        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            'mpa_shipping_estimate_quoted_at',
            [
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'MyParcel Asia Shipping Estimate Quoted At',
            ]
        );

        $connection->addIndex(
            $installer->getTable('sales_order_grid'),
            $installer->getIdxName('sales_invoice_grid', ['mpa_shipping_estimate']),
            ['mpa_shipping_estimate']
        );

        $connection->addIndex(
            $installer->getTable('sales_order_grid'),
            $installer->getIdxName('sales_invoice_grid', ['mpa_shipping_estimate_quoted_at']),
            ['mpa_shipping_estimate_quoted_at']
        );
    }

    private function addShipmentTable(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $table = $connection->newTable($installer->getTable('mpa_shipping_shipment'));

        $table->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]);
        $table->addColumn('sender_name', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_email', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_mobile_number', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_address1', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_address2', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_postal_code', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_location_id', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('sender_location', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_city', Table::TYPE_TEXT, 255);
        $table->addColumn('sender_state', Table::TYPE_TEXT, 255);
        $table->addColumn('service_type', Table::TYPE_TEXT, 255);
        $table->addColumn('pick_up_ready_at', Table::TYPE_TIMESTAMP);
        $table->addColumn('pick_up_transportation', Table::TYPE_TEXT, 255);
        $table->addColumn('pick_up_trolley_required', Table::TYPE_BOOLEAN);
        $table->addColumn('pick_up_remark', Table::TYPE_TEXT, 255);
        $table->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT]);
        $table->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE]);

        $table->addIndex($installer->getIdxName('mpa_shipping_shipment', ['created_at']), ['created_at']);
        $table->addIndex($installer->getIdxName('mpa_shipping_shipment', ['updated_at']), ['updated_at']);

        $connection->dropTable($table->getName());
        $connection->createTable($table);
    }

    private function addConsignmentTable(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $table = $connection->newTable($installer->getTable('mpa_shipping_consignment'));

        $table->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]);
        $table->addColumn('number', Table::TYPE_TEXT, 255);
        $table->addColumn('parcel_type', Table::TYPE_TEXT, 255);
        $table->addColumn('content', Table::TYPE_TEXT, 255);
        $table->addColumn('pieces', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('weight', Table::TYPE_DECIMAL, '12,4', ['unsigned' => true]);
        $table->addColumn('rate', Table::TYPE_DECIMAL, '12,4', ['unsigned' => true]);
        $table->addColumn('value', Table::TYPE_DECIMAL, '12,4', ['unsigned' => true]);
        $table->addColumn('name', Table::TYPE_TEXT, 255);
        $table->addColumn('mobile', Table::TYPE_TEXT, 255);
        $table->addColumn('email', Table::TYPE_TEXT, 255);
        $table->addColumn('address1', Table::TYPE_TEXT, 255);
        $table->addColumn('address2', Table::TYPE_TEXT, 255);
        $table->addColumn('postcode', Table::TYPE_TEXT, 255);
        $table->addColumn('city', Table::TYPE_TEXT, 255);
        $table->addColumn('state', Table::TYPE_TEXT, 255);
        $table->addColumn('country', Table::TYPE_TEXT, 255);
        $table->addColumn('shipment_id', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('order_id', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT]);
        $table->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE]);

        $table->addIndex($installer->getIdxName('mpa_shipping_consignment', ['number']), ['number']);
        $table->addIndex($installer->getIdxName('mpa_shipping_consignment', ['created_at']), ['created_at']);
        $table->addIndex($installer->getIdxName('mpa_shipping_consignment', ['updated_at']), ['updated_at']);

        $table->addForeignKey(
            $installer->getFkName('mpa_shipping_consignment', 'shipment_id', 'mpa_shipping_shipment', 'id'),
            'shipment_id',
            $installer->getTable('mpa_shipping_shipment'),
            'id',
            Table::ACTION_CASCADE
        );
        $table->addForeignKey(
            $installer->getFkName('mpa_shipping_consignment', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $connection->dropTable($table->getName());
        $connection->createTable($table);

        $connection->addColumn(
            $installer->getTable('sales_order'),
            'mpa_shipping_consignment_number',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'MyParcel Asia Shipping Cosignment Number',
            ]
        );

        $connection->addIndex(
            $installer->getTable('sales_order'),
            $installer->getIdxName('sales_order', ['mpa_shipping_consignment_number']),
            ['mpa_shipping_consignment_number']
        );


        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            'mpa_shipping_consignment_number',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'MyParcel Asia Shipping Cosignment Number',
            ]
        );

        $connection->addIndex(
            $installer->getTable('sales_order_grid'),
            $installer->getIdxName('sales_invoice_grid', ['mpa_shipping_consignment_number']),
            ['mpa_shipping_consignment_number']
        );
    }

    private function addInvoiceTable(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $table = $connection->newTable($installer->getTable('mpa_shipping_invoice'));

        $table->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]);
        $table->addColumn('number', Table::TYPE_TEXT, 255);
        $table->addColumn('total', Table::TYPE_DECIMAL, '12,4', ['unsigned' => true]);
        $table->addColumn('shipment_id', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT]);
        $table->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE]);
        $table->addIndex($installer->getIdxName('mpa_shipping_invoice', ['number']), ['number']);
        $table->addIndex($installer->getIdxName('mpa_shipping_invoice', ['created_at']), ['created_at']);
        $table->addIndex($installer->getIdxName('mpa_shipping_invoice', ['updated_at']), ['updated_at']);
        $table->addForeignKey(
            $installer->getFkName('mpa_shipping_invoice', 'shipment_id', 'mpa_shipping_shipment', 'id'),
            'shipment_id',
            $installer->getTable('mpa_shipping_shipment'),
            'id',
            Table::ACTION_CASCADE
        );
        
        $connection->dropTable($table->getName());
        $connection->createTable($table);
    }

    private function addConsignmentStatusColumn(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable('mpa_shipping_consignment'),
            'status',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'MyParcel Asia Shipping Consignment Status',
            ]
        );

        $connection->addIndex(
            $installer->getTable('mpa_shipping_consignment'),
            $installer->getIdxName('mpa_shipping_consignment', ['status']),
            ['status']
        );
    }

}
