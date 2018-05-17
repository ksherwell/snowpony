<?php
namespace Infortis\Base\Block\Product\ProductList\Featured;

/**
 * Interceptor class for @see \Infortis\Base\Block\Product\ProductList\Featured
 */
class Interceptor extends \Infortis\Base\Block\Product\ProductList\Featured implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Catalog\Model\Layer\Resolver $layerResolver, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Customer\Model\Session $modelSession, \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection, \Magento\Catalog\Helper\Output $catalogHelperOutput, \Magento\Catalog\Model\Layer\CategoryFactory $categoryLayerFactory, \Infortis\Base\Helper\Data $baseDataHelper, \Infortis\Base\Helper\Labels $baseLabelHelper, \Infortis\Infortis\Helper\Image $infortisImageHelper, \Magento\Catalog\Model\CategoryFactory $categoryFactory, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $modelSession, $productCollection, $catalogHelperOutput, $categoryLayerFactory, $baseDataHelper, $baseLabelHelper, $infortisImageHelper, $categoryFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($product, $imageId, $attributes = array())
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getImage');
        if (!$pluginInfo) {
            return parent::getImage($product, $imageId, $attributes);
        } else {
            return $this->___callPlugins('getImage', func_get_args(), $pluginInfo);
        }
    }
}
