<?php
class Magestio_Cleaner_Model_Logfiles extends Mage_Core_Model_Abstract
{

    const XML_PATH_ENABLED   = 'magestio_cleaner/logfiles/enabled';
    const XML_PATH_FREQUENCY = 'magestio_cleaner/logfiles/frequency';
    const XML_PATH_LOGFILE   = 'magestio_cleaner/general/log';

    const LOGFILES_DIR   = 'var/log';

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
            $logfiles = $this->_helper->scanLogFiles(self::LOGFILES_DIR);
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
                foreach ($logfiles as $logfile) {
                    try {
                        unlink($logfile);
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), Zend_Log::INFO, $this->getLogFile(), true);
                    }
                }
            }
        }
    }

}
