<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Amasty\MultiInventory\Model\Export\ConvertToXml;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Render
 */
class GridToXml extends Action
{
    /**
     * @var ConvertToXml
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    private $timezone;

    /**
     * GridToXml constructor.
     * @param Context $context
     * @param ConvertToXml $converter
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertToXml $converter,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        $this->timezone = $timezone;
    }

    /**
     * Export data provider to XML
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $filename = $this->getRequest()->getParam('filename');
        $name = $this->timezone->date()->format('Y_m_d_H_i_s');
        if (!$filename) {
            $filename = 'export_' . $name;
        }
        $filename .='.xml';

        return $this->fileFactory->create($filename, $this->converter->getFile($filename), DirectoryList::MEDIA);
    }
}
