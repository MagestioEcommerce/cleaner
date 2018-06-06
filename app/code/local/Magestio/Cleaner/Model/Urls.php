<?php
class Magestio_Cleaner_Model_Urls extends Mage_Core_Model_Abstract
{

    const XML_PATH_ENABLED   = 'magestio_cleaner/urls/enabled';
    const XML_PATH_EXCEPT    = 'magestio_cleaner/urls/except';
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

            try {
                $except = Mage::getStoreConfig(self::XML_PATH_EXCEPT);
                if ((int)$except > 0) {
                    $this->repairUrlRewritesTable((int)$except);
                } else {
                    $this->repairUrlRewritesTable();
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::INFO, $this->getLogFile(), true);
            }

        }
    }

    /**
     * Remove duplicate records from 'core_url_rewrite' table
     *
     * @param int|bool $except
     */
    public function repairUrlRewritesTable($except = false)
    {
        try {
            //Get collection of all products
            $productCollection = Mage::getResourceModel('catalog/product_collection');
            $counter = 0;
            foreach($productCollection as $product) {
                //Get all url rewrites of each product
                $urlRewritesCollection = Mage::getResourceModel('core/url_rewrite_collection')
                    ->addFieldToFilter('product_id', array('eq' => $product->getId()))
                    ->addFieldToFilter('is_system', array('eq' => '0'))
                    ->setOrder('url_rewrite_id', 'DESC');
                // If 'except' argument exist, and valid -> apply it to $urlRewriteCollection
                if ($except !== false && $except > 0) {
                    $urlRewritesCollection->getSelect()->limit(null, $except);
                } elseif ($except !== false) {
                    throw new Exception('\'--except\' should be an integer.');
                }
                foreach ($urlRewritesCollection as $urlRewrite) {
                    try {
                        //Removing extra url rewrites
                        $urlRewrite->delete();
                        $counter++;
                    } catch(Exception $e) {
                        echo "An error was occurred: " . $e->getMessage() . PHP_EOL;
                        Mage::log($e->getMessage(), null, 'repair_url_rewrites.log');
                    }
                }
            }
            //Display result message
            $message = $counter . ' duplicating records was deleted';
            echo PHP_EOL . $message . PHP_EOL;
        } catch (Exception $e) {
            echo "An error was occurred: " . $e->getMessage() . PHP_EOL;
            Mage::log($e->getMessage(), null, 'repair_url_rewrites.log');
        }
    }

}
