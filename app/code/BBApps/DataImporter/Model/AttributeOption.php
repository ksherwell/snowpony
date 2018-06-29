<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\AbstractModel;

class AttributeOption extends AbstractModel
{
    const DEFAULT_ATTRIBUTE_SET = 'Default';
    const DEFAULT_ATTRIBUTE_GROUP = 'General';

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var \BBApps\DataImporter\Helper\ProductOptionHelper
     */
    private $productOptionHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    private $fileUploader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \BBApps\DataImporter\Helper\ProductOptionHelper $productOptionHelper,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Swatches\Helper\Data $swatchHelper,
        array $data = []
    ) {
        parent::__construct($logger, $filesystem, $data);

        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionHelper = $productOptionHelper;
        $this->attributeFactory = $attributeFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->swatchHelper = $swatchHelper;
    }

    /**
     * Import Attribute Option
     * Cannot replace the exist values
     *
     * @param $attributeOption
     * @param $attributeCode
     * @return bool
     */
    public function importAttributeOption($attributeOption, $attributeCode)
    {
        $attribute = null;
        try {
            /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute */
            $attribute = $this->productAttributeRepository->get($attributeCode);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        if ($attribute) {
            $optionArrBefore = $this->productOptionHelper->getOptionArrWithIds($attributeCode);
            //add option
            if ($_optionObj = $this->productOptionHelper->getAttributeOptionObject($attributeOption, false)) {
                $this->productOptionHelper->addOption($attributeCode, $_optionObj);
            }

            if (!empty($attributeOption['swatch'])) {
                $optionArrAfter = $this->productOptionHelper->getOptionArrWithIds($attributeCode, true);
                $newOptions = array_diff(array_keys($optionArrAfter), array_keys($optionArrBefore));
                if (empty($newOptions)) {
                    return false;
                }

                $newOptionId = array_pop($newOptions);
                $this->addOptionSwatch($attribute, $newOptionId, $attributeOption['swatch']);
            }

            return true;
        }

        return false;
    }

    /**
     * @param $attribute
     * @param $newOptionId
     * @param $swatchValue
     */
    public function addOptionSwatch($attribute, $newOptionId, $swatchValue)
    {
        if ($attribute) {
            $model = $this->attributeFactory->create();
            $model->load($attribute->getId());
            $swatchValue = $this->processSwatch($swatchValue);

            if ($swatchValue) {
                $swatches = [
                    'value' => [$newOptionId => $swatchValue]
                ];

                /**
                 * #todo: Cannot use it. It has a bug that create a new blank input when saving
                 * $this->productAttributeRepository->save($exist);
                 *
                 **/
                $model->setData('swatchvisual', $swatches)->save();
            }
        }
    }

    /**
     * Bulk Import Attribute Option
     *
     * @param $newData
     * @param $attributeCode
     * @return bool
     */
    public function bulkImportAttributeOption($newData, $attributeCode) // @codingStandardsIgnoreLine
    {
        $newData = $this->formatNewData($newData);
        $attribute = null;
        try {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->productAttributeRepository->get($attributeCode);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        if ($attribute && $attributeCode) {
            $isVisualSwatch = $this->swatchHelper->isVisualSwatch($attribute);
            $isTextSwatch = $this->swatchHelper->isTextSwatch($attribute);

            $optionArrBefore = $this->productOptionHelper->getOptionArrWithIds($attributeCode);

            $optionData = [];
            $swatchData = [];
            foreach ($optionArrBefore as $optionId => $option) {
                list($storeLabels, $swatchLabels) = $this->getOptionValues($option->getStoreLabels());
                if (!empty($storeLabels[0]) && array_key_exists($storeLabels[0], $newData)) { // check Admin value
                    $storeLabels = $newData[$storeLabels[0]];
                    if (!empty($storeLabels['swatch'])) {
                        $swatchLabels[0] = $this->processSwatch($storeLabels['swatch']);
                    }

                    unset($newData[$storeLabels[0]]);
                };
                if (isset($storeLabels['swatch'])) {
                    unset($storeLabels['swatch']);
                }
                $optionData['value'][$optionId] = $storeLabels;
                $optionData['order'][$optionId] = $option->getSortOrder();
                $swatchData['value'][$optionId] = $isTextSwatch? $swatchLabels: $swatchLabels[0];
            }

            if (!empty($newData)) {
                $index = count($optionArrBefore);
                foreach ($newData as $_storeLabels) {
                    $index++;
                    $optionId = 'option_' . $index;
                    if (isset($_storeLabels['swatch'])) {
                        $swatchData['value'][$optionId] = $this->processSwatch($_storeLabels['swatch']);
                        unset($_storeLabels['swatch']);
                    }
                    $optionData['value'][$optionId] = $_storeLabels;
                    $optionData['order'][$optionId] = $index;
                }
            }

            $model = $this->attributeFactory->create();
            $model->load($attribute->getId());
            if ($isVisualSwatch) {
                $model->setData('optionvisual', $optionData);
                $model->setData('swatchvisual', $swatchData);
            } elseif ($isTextSwatch) {
                $model->setData('optiontext', $optionData);
                $model->setData('swatchtext', $swatchData);
            } else {
                $model->setData('option', $optionData);
                $model->setData('swatch', $swatchData);
            };

            /**
             * #todo: Cannot use it. It has the bugs that create a new blank input and ignore the store labels when saving
             * $this->productAttributeRepository->save($exist);
             *
             **/
            $model->save();

            return true;
        }

        return false;
    }

    /**
     * format data
     * from '0' => ['0' => 'Admin Value', '1' => 'English Value', '2' => 'French Value', '3' => 'German Value']
     * to 'Admin Value' => ['0' => 'Admin Value', '1' => 'English Value', '2' => 'French Value', '3' => 'German Value']
     * @param $newData
     * @return array
     */
    private function formatNewData($newData)
    {
        $_formatData = [];
        foreach ($newData as $value) {
            if (!empty($value) && !empty($value[0])) { // $value[0] is Admin value that should be unique
                $_formatData[$value[0]] = $value;
            }
        }

        return $_formatData;
    }

    /**
     * get option values of types
     *
     * @param $storeLabels
     * @return array
     */
    private function getOptionValues($storeLabels)
    {
        $_storeOptions = [];
        $_swatchOptions = [];
        foreach ($storeLabels as $key => $storeLabel) {
            if (strpos($key, 'store') !== false) {
                $newKey = str_replace('store', '', $key);
                $_storeOptions[$newKey] = $storeLabel;
            }
            if (strpos($key, 'swatch') !== false) {
                $newKey = str_replace('swatch', '', $key);
                $_swatchOptions[$newKey] = $storeLabel;
            }
        }

        return [$_storeOptions, $_swatchOptions];
    }

    /**
     * process watch value, import it if it is images
     *
     * @param $swatchValue
     * @return string
     */
    public function processSwatch($swatchValue)
    {
        if (strpos($swatchValue, '#') === false) {
            $swatchValue = $this->uploadSwatchFiles($swatchValue);
        }

        return $swatchValue;
    }

    /**
     * Use @see \Magento\CatalogImportExport\Model\Import\Uploader
     * to upload image by http link or exist files in pub/media/import/
     *
     * @param string $fileName
     * @param bool $renameFileOff
     * @return string
     */
    public function uploadSwatchFiles($fileName, $renameFileOff = false)
    {
        try {
            $res = $this->getUploader()->move($fileName, $renameFileOff);
            return $res['file'];
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Returns an object for upload a media files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUploader()
    {
        if ($this->fileUploader === null) {
            $this->fileUploader = $this->uploaderFactory->create();

            $this->fileUploader->init();

            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

            $DS = DIRECTORY_SEPARATOR;

            $tmpPath = $dirAddon . $DS . $this->mediaDirectory->getRelativePath('import');

            if (!$this->fileUploader->setTmpDir($tmpPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }
            $destinationDir = "attribute/swatch";
            $destinationPath = $dirAddon . $DS . $this->mediaDirectory->getRelativePath($destinationDir);

            $this->mediaDirectory->create($destinationPath);
            if (!$this->fileUploader->setDestDir($destinationPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }
        return $this->fileUploader;
    }
}
