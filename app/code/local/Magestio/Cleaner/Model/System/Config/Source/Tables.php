<?php

class Magestio_Cleaner_Model_System_Config_Source_Tables
{

    protected static $_options;

    const CRON_DAILY    = 'D';
    const CRON_WEEKLY   = 'W';
    const CRON_MONTHLY  = 'M';

    protected $tables = array(
        'dataflow_batch_export',
        'dataflow_batch_import',
        'log_customer',
        'log_quote',
        'log_summary',
        'log_summary_type',
        'log_url',
        'log_url_info',
        'log_visitor',
        'log_visitor_info',
        'log_visitor_online',
        'report_viewed_product_index',
        'report_compared_product_index',
        'report_event',
        'catalog_compare_item'
    );

    public function toOptionArray()
    {
        $options = array();
        foreach ($this->tables as $table) {
            $options[] = array(
                'label' => $table,
                'value' => $table
            );
        }
        return $options;
    }

}
