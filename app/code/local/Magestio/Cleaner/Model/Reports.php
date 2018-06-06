<?php
class Magestio_Cleaner_Model_Reports extends Mage_Core_Model_Abstract
{

    const XML_PATH_ENABLED  = 'magestio_cleaner/reports/enabled';
    const XML_PATH_LIFETIME = 'magestio_cleaner/reports/lifetime';
    const XML_PATH_LOGFILE  = 'magestio_cleaner/general/log';

    const REPORTS_DIR   = 'var/report';

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
            $reports = $this->_helper->scanReports(self::REPORTS_DIR);
            $now = time();
            $lifeTime = Mage::getStoreConfig(self::XML_PATH_LIFETIME) * 24 * 60 * 60;
            foreach ($reports as $report) {
                $fileTime = filemtime($report);
                if ($now - $fileTime >= $lifeTime) {
                    try {
                        unlink($report);
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), Zend_Log::INFO, $this->getLogFile(), true);
                    }
                }
            }
        }
    }

}
