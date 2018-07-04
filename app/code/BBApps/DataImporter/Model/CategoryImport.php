<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\ImportExport\Model\AbstractModel;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use BBApps\DataImporter\Helper\Data as ImportHelper;

class CategoryImport extends AbstractModel
{
    const DEFAULT_ATTRIBUTE_SET = 'Default';
    const DEFAULT_ATTRIBUTE_GROUP = 'General';
    const DELIMITER_CATEGORY = '/';

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ImportHelper
     */
    private $helper;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var CollectionFactory
     */
    private $categoryColFactory;

    /**
     * @var array
     */
    private $categories;

    /**
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param CollectionFactory $categoryColFactory
     * @param ImportHelper $helper
     * @param array $data
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        CategoryRepositoryInterface $categoryRepository,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        CollectionFactory $categoryColFactory,
        ImportHelper $helper,
        array $data = []
    ) {
        parent::__construct($logger, $filesystem, $data);

        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->categoryColFactory = $categoryColFactory;
        $this->helper = $helper;
    }

    /**
     * #todo: category is assigned into root if "parent" hasn't created yet
     *
     * @param $data
     * @return bool
     */
    public function import($data)
    {
        $this->initCategories();
        $data = $this->_prepareData($data);

        /** @var Category $category */
        $category = $this->categoryFactory->create();

        $this->helper->setDataToObject($category, $data);
        $this->_setDataNotExist($category, $data);

        try {
            $this->categoryRepository->save($category);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    private function initCategories()
    {
        if (empty($this->categories)) {
            $collection = $this->categoryColFactory->create();
            $collection->addAttributeToSelect('name');
            /** @var $collection Collection **/
            foreach ($collection as $category) {
                /** @var $category Category **/
                $structure = explode(self::DELIMITER_CATEGORY, $category->getPath());
                $pathSize = count($structure); // @codingStandardsIgnoreLine

                if ($pathSize > 1) {
                    $path = [];
                    for ($i = 1; $i < $pathSize; $i++) {
                        $_category = $collection->getItemById((int)$structure[$i]);
                        /** @var $_category Category **/
                        $path[] = $_category->getName();
                    }
                    $index = implode(self::DELIMITER_CATEGORY, $path);
                    $this->categories[$index] = $category->getId();
                }
            }
        }

        return $this;
    }

    private function _prepareData($data)
    {
        // parent_id
        if (!empty($data['parent'])) {
            $parentPath = trim($data['parent'], '/');
            if (isset($this->categories[$parentPath])) {
                $data['parent_id'] = $this->categories[$parentPath];
            }
            unset($data['parent']);
        }

        return $data;
    }

    /**
     * set category data that is not exist in class @see CategoryInterface
     * @
     * @param \Magento\Catalog\Model\Category $category
     * @param $data
     */
    private function _setDataNotExist($category, $data)
    {
        if (!empty($data['description'])) {
            $category->setData('description', $data['description']);
        }
        if (!empty($data['display_mode'])) {
            $category->setData('display_mode', $data['display_mode']);
        }
        if (!empty($data['meta_title'])) {
            $category->setData('meta_title', $data['meta_title']);
        }
        if (!empty($data['meta_keywords'])) {
            $category->setData('meta_keywords', $data['meta_keywords']);
        }
        if (!empty($data['meta_description'])) {
            $category->setData('meta_description', $data['meta_description']);
        }
    }
}
