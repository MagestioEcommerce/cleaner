<?php
class Magestio_Cleaner_Model_Images extends Mage_Core_Model_Abstract
{

    const XML_PATH_ENABLED  = 'magestio_cleaner/images/enabled';
    const XML_PATH_LOGFILE  = 'magestio_cleaner/general/log';
    const XML_PATH_IMPORT   = 'magestio_cleaner/images/import';

    const CATALOG_PRODUCT   = 'media/catalog/product';
    const MEDIA_IMPORT      = 'media/import';
    const CACHE             = '/cache/';
    const PLACEHOLDER       = '/placeholder/';

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
            $images_to_delete = $this->_getObsoleteImages();
            $this->_deteteImages($images_to_delete);

            if (Mage::getStoreConfig(self::XML_PATH_IMPORT)) {
                $this->deleteImportImages();
            }
        }
    }

    public function _getDbImages()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');

        $media_table = $resource->getTableName('catalog/product_attribute_media_gallery');
        $eav_table = $resource->getTableName('eav/attribute');
        $varchar_table = $resource->getTableName('catalog_product_entity_varchar');
        $entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();

        $sql = "SELECT value
                FROM  `$media_table`
                WHERE attribute_id
                IN (SELECT attribute_id FROM `$eav_table` WHERE `attribute_code` in ('media_gallery') AND entity_type_id = $entity_type_id)";

        $resultGallery = $connection->fetchCol($sql);

        $sql = "SELECT value
                FROM  `$varchar_table`
                WHERE attribute_id
                IN (SELECT attribute_id FROM `$eav_table` WHERE `attribute_code` in ('image','small_image','thumbnail') AND entity_type_id = $entity_type_id)";

        $resultVarchar = $connection->fetchCol($sql);

        return array_unique(array_merge($resultGallery, $resultVarchar), SORT_REGULAR);
    }

    public function _getObsoleteImages()
    {
        $imagesOnDb = $this->_getDbImages();
        $filesystem_images = $this->_helper->scan(self::CATALOG_PRODUCT);

        $skip = strlen($this->_helper->getBaseDir() . DS . self::CATALOG_PRODUCT);
        $imagesOnDisk = array();
        foreach ($filesystem_images as $img) { 

            // Check if is cache image
            if (strpos($img, self::CACHE)) {
                if (strpos($img, self::PLACEHOLDER) === false) {
                    $chunks = explode('/', $img);
                    $filename = '/'.$chunks[count($chunks) - 3] .'/'. $chunks[count($chunks) - 2] .'/'. $chunks[count($chunks) - 1];
                    if (!in_array($filename, $imagesOnDb)) {
                        $imagesOnDisk[] = substr($img, $skip);
                    }
                    continue;
                }
            }

            // Check if placeholder image
            if (strpos($img, self::PLACEHOLDER) === false) {
                $imagesOnDisk[] = substr($img, $skip);
            }

        }

        $images_to_delete = array_diff($imagesOnDisk, $imagesOnDb);
        $images_to_delete = array_values($images_to_delete);
        return $images_to_delete;
    }

    protected function _deteteImages($images_to_delete)
    {
        foreach ($images_to_delete as $x) {
            try {
                unlink($this->_helper->getBaseDir() . DS . self::CATALOG_PRODUCT . $x);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::INFO, $this->getLogFile(), true);
            }
        }
    }

    protected function deleteImportImages()
    {
        $importImages = $this->_helper->scan(self::MEDIA_IMPORT);
        foreach ($importImages as $importImage) {
            try {
                unlink($importImage);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::INFO, $this->getLogFile(), true);
            }
        }
    }

}
