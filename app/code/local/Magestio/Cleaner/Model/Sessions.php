<?php
class Magestio_Cleaner_Model_Sessions extends Mage_Core_Model_Abstract
{

    const XML_PATH_ENABLED  = 'magestio_cleaner/sessions/enabled';
    const XML_PATH_LIFETIME = 'magestio_cleaner/sessions/lifetime';
    const XML_PATH_LOGFILE  = 'magestio_cleaner/general/log';

    const SESSIONS_DIR   = 'var/session';

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
            $sessions = $this->_helper->scanSessions(self::SESSIONS_DIR);
            $now = time();
            $lifeTime = Mage::getStoreConfig(self::XML_PATH_LIFETIME) * 24 * 60 * 60;
            foreach ($sessions as $session) {
                $fileTime = filemtime($session);
                if ($now - $fileTime >= $lifeTime) {
                    try {
                        unlink($session);
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), Zend_Log::INFO, $this->getLogFile(), true);
                    }
                }
            }
        }
    }

}
