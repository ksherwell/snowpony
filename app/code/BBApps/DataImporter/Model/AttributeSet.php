<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\ImportExport\Model\AbstractModel;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Catalog\Api\AttributeSetManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use BBApps\DataImporter\Helper\Data as ImportHelper;

class AttributeSet extends AbstractModel
{
    /**
     * @var ImportHelper
     */
    private $helper;

    /**
     * @var AttributeSetManagementInterface
     */
    private $attributeSetManagement;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Product
     */
    private $product;

    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        AttributeSetManagementInterface $attributeSetManagement,
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SetFactory $attributeSetFactory,
        Product $product,
        ImportHelper $helper,
        array $data = []
    ) {
        parent::__construct($logger, $filesystem, $data);

        $this->helper = $helper;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->product = $product;
    }

    public function import($data)
    {
        if (!empty($data['name'])) {
            $name = trim($data['name']);
            $parent = trim($data['parent']);
            $attributeSet = $this->attributeSetFactory->create();
            $attributeSet->setName($name);

            try {
                $skeletonId = $this->getSkeletonId($parent);
                $this->attributeSetManagement->create($attributeSet, $skeletonId);
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
                return false;
            }
        }

        return true;
    }

    private function getSkeletonId($parentName)
    {
        if ($parentName) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('entity_type_code', ProductAttributeInterface::ENTITY_TYPE_CODE);
                //->addFilter('attribute_set_name', $parentName); // could not use other filter in eav attribute set
            $attributeSet = $this->attributeSetRepository->getList($searchCriteria->create());

            if ($attributeSet->getTotalCount() > 0) {
                foreach ($attributeSet->getItems() as $item) {
                    if ($item->getAttributeSetName() == $parentName) { // loop to check
                        return $item->getAttributeSetId();
                    }
                }
            }
        }

        return $this->product->getDefaultAttributeSetId();
    }
}
