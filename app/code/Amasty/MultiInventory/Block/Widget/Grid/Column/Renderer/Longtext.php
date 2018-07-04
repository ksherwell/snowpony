<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Widget\Grid\Column\Renderer;

class Longtext extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Longtext
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $url;

    /**
     * Longtext constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\UrlInterface $url,
        array $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * Add url for Warehouse
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        $code = $row->getData('code');
        $url = $this->url->getUrl(
            'amasty_multi_inventory/warehouse/edit',
            ['warehouse_id' => $row->getData('warehouse_id')]
        );
        $text = parent::render($row);
        $text .= sprintf(' (<a href="%s">%s</a>)', $url, $code);

        return $text;
    }
}
