<?php
class Magestio_Cleaner_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    /**
     * Magento Root full path.
     *
     * @var null|string
     */
    protected $_baseDir = null;

    /**
     * Returns Magento Root full path.
     *
     * @return string
     */
    public function getBaseDir()
    {
        if ($this->_baseDir === null) {
            $this->_baseDir = Mage::getBaseDir();
        }
        return $this->_baseDir;
    }

    /**
     * Retuns with files in given directory 
     *
     * @param string $path
     * @return array
     */
    public function scan($path)
    {
        $iterator = $this->_scan($path);

        $files = array();
        foreach ($iterator as $file) {
            if (preg_match('/^.+\.(jpe?g|gif|png)$/i', $file)) {
                $files[] = $file;
            }
        }
        
        return $files;

    }

    /**
     * Retuns with files in given directory
     *
     * @param string $path
     * @return array
     */
    public function scanSessions($path)
    {
        $iterator = $this->_scan($path);

        $files = array();
        foreach ($iterator as $file) {
            if (preg_match('/^.+\/sess_/', $file)) {
                $files[] = $file;
            }
        }

        return $files;

    }

    /**
     * Retuns with files in given directory
     *
     * @param string $path
     * @return array
     */
    public function scanReports($path)
    {
        $iterator = $this->_scan($path);

        $files = array();
        foreach ($iterator as $file) {
            $files[] = $file;
        }

        return $files;

    }

    /**
     * Retuns with files in given directory
     *
     * @param string $path
     * @return array
     */
    public function scanLogFiles($path)
    {
        $iterator = $this->_scan($path);

        $files = array();
        foreach ($iterator as $file) {
            if (preg_match('/^.+\.(log)$/', $file)) {
                $files[] = $file;
            }
        }

        return $files;

    }

    protected function _scan($path)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->getBaseDir() . DS . $path,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME
            )
        );

        return $iterator;
    }

}
	 