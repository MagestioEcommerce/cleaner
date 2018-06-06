<?php
class Magestio_Cleaner_Model_Database extends Mage_Core_Model_Abstract
{

    const XML_PATH_ENABLED   = 'magestio_cleaner/database/enabled';
    const XML_PATH_TABLES    = 'magestio_cleaner/database/tables';
    const XML_PATH_FREQUENCY = 'magestio_cleaner/database/frequency';
    const XML_PATH_LOGFILE   = 'magestio_cleaner/general/log';

    protected $_helper;

    public function _construct()
    {
        $this->_helper = Mage::helper('cleaner');
    }

    public function getLogFile()
    {
        return Mage::getStoreConfig(self::XML_PATH_LOGFILE);
    }

    public function run()
    {
        if (Mage::getStoreConfig(self::XML_PATH_ENABLED)) {

            $frequency = Mage::getStoreConfig(self::XML_PATH_FREQUENCY);
            $today = new DateTime('midnight');

            switch ($frequency) {
                case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY:
                    $delete = true;
                    break;
                case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY:
                    $delete = ($today->format('N') == 1);
                    break;
                case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY:
                    $delete = ($today->format('j') == 1);
                    break;
            }

            if ($delete) {
                $tables = Mage::getStoreConfig(self::XML_PATH_TABLES);
                $tables = explode(',', $tables);

                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');

                foreach ($tables as $table) {
                    try {
                        $writeConnection->query("truncate table $table");
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), Zend_Log::INFO, $this->getLogFile(), true);
                    }
                }
            }
        }
    }

}
