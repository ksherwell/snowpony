<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model\Import;

use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor;
use Magento\ImportExport\Model\Import;
use Magento\Framework\Filesystem;
use Magento\Store\Model\Store;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;

class SGProduct extends \Magento\CatalogImportExport\Model\Import\Product
{
    /**
     * Size of bunch - part of products to save in one step.
     */
    const ENTITY_TYPE_ID = 'catalog_product';

    /**
     * Column product category.
     */
    //    const COL_CATEGORY = 'categories';

    /**
     * Column product sku.
     */
    //    const COL_SKU = 'item_shop_code';

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /**
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [];

    /**
     * @var \BBApps\DataImporter\Model\CustomProduct
     */
    private $productImport;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 100.0.3
     */
    protected $scopeConfig;

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * Provide ability to process and save images during import.
     *
     * @var MediaGalleryProcessor
     */
    private $mediaProcessor;

    /**
     * Catalog config.
     *
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * Container for filesystem object.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Escaped separator value for regular expression.
     * The value is based on PSEUDO_MULTI_LINE_SEPARATOR constant.
     * @var string
     */
    private $multiLineSeparatorForRegexp;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     * @since 100.0.3
     */
    protected $productUrl;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * Map between import file fields and system fields/attributes.
     *
     * @var array
     */
    protected $_fieldsMap = [
        'image'                      => 'base_image',
        'image_label'                => "base_image_label",
        'thumbnail'                  => 'thumbnail_image',
        'thumbnail_label'            => 'thumbnail_image_label',
        parent::COL_MEDIA_IMAGE      => 'additional_images',
        '_media_image_label'         => 'additional_image_labels',
        '_media_is_disabled'         => 'hide_from_product_page',
        parent::COL_STORE            => 'store_view_code',
        parent::COL_ATTR_SET         => 'attribute_set_code',
        parent::COL_TYPE             => 'product_type',
        parent::COL_PRODUCT_WEBSITES => 'product_websites',
        'status'                     => 'product_online',
        'news_from_date'             => 'new_from_date',
        'news_to_date'               => 'new_to_date',
        'options_container'          => 'display_product_options_in',
        'minimal_price'              => 'map_price',
        'msrp'                       => 'msrp_price',
        'msrp_enabled'               => 'map_enabled',
        'special_from_date'          => 'special_price_from_date',
        'special_to_date'            => 'special_price_to_date',
        'min_qty'                    => 'out_of_stock_qty',
        'backorders'                 => 'allow_backorders',
        'min_sale_qty'               => 'min_cart_qty',
        'max_sale_qty'               => 'max_cart_qty',
        'notify_stock_qty'           => 'notify_on_stock_below',
        '_related_sku'               => 'related_skus',
        '_related_position'          => 'related_position',
        '_crosssell_sku'             => 'crosssell_skus',
        '_crosssell_position'        => 'crosssell_position',
        '_upsell_sku'                => 'upsell_skus',
        '_upsell_position'           => 'upsell_position',
        'meta_keyword'               => 'meta_keywords',
    ];

    /**
     * CustomProduct constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\ImportExport\Model\Import\Config $importConfig
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory
     * @param \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac
     * @param DateTime\TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     * @param \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor
     * @param \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor
     * @param \Magento\CatalogImportExport\Model\Import\Product\Validator $validator
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param TransactionManagerInterface $transactionManager
     * @param \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor $taxClassProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param array $data
     * @param array $dateAttrCodes
     * @param CatalogConfig|null $catalogConfig
     * @param MediaGalleryProcessor|null $mediaProcessor
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        array $data = [],
        array $dateAttrCodes = [],
        CatalogConfig $catalogConfig = null,
        MediaGalleryProcessor $mediaProcessor = null
    ) {
        $this->_eventManager     = $eventManager;
        $this->_catalogData      = $catalogData;
        $this->filesystem        = $filesystem;
        $this->catalogConfig     = $catalogConfig ? : \Magento\Framework\App\ObjectManager::getInstance()
            ->get(CatalogConfig::class);
        $this->mediaProcessor    = $mediaProcessor ? : \Magento\Framework\App\ObjectManager::getInstance()
            ->get(MediaGalleryProcessor::class);
        $this->_resourceHelper   = $resourceHelper;
        $this->_importExportData = $importExportData;
        $this->_dataSourceModel  = $importData;
        $this->_connection       = $resource->getConnection();

        $this->jsonHelper          = $jsonHelper;
        $this->_resourceFactory    = $resourceFactory;
        $this->errorAggregator     = $errorAggregator;
        $this->_setColFactory      = $setColFactory;
        $this->_importConfig       = $importConfig;
        $this->validator           = $validator;
        $this->_productTypeFactory = $productTypeFactory;
        $this->taxClassProcessor   = $taxClassProcessor;
        $this->_proxyProdFactory   = $proxyProdFactory;
        $this->_uploaderFactory    = $uploaderFactory;
        $this->_mediaDirectory     = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_linkFactory        = $linkFactory;
        $this->_stockResItemFac    = $stockResItemFac;
        $this->stockConfiguration  = $stockConfiguration;
        $this->stockRegistry       = $stockRegistry;
        $this->stockStateProvider  = $stockStateProvider;
        $this->indexerRegistry     = $indexerRegistry;
        $this->productUrl          = $productUrl;
        $this->scopeConfig         = $scopeConfig;
        $this->_logger             = $logger;

        $this->_optionEntity     = isset(
            $data['option_entity']
        ) ? $data['option_entity'] : $optionFactory->create(
            ['data' => ['product_entity' => $this]]
        );
        $this->categoryProcessor = $categoryProcessor;
        $this->skuProcessor      = $skuProcessor;
        $this->storeResolver     = $storeResolver;

        $entityType = $config->getEntityType(self::ENTITY_TYPE_ID);

        $this->_entityTypeId = $entityType->getEntityTypeId();

        $this->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus();
        $this->validator->init($this);
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'sg_product';
    }

    /**
     * Initialize product type models.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initTypeModels()
    {
        $productTypes = $this->_importConfig->getEntityTypes(self::ENTITY_TYPE_ID);
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            $params = [$this, $productTypeName];
            if (! ($model = $this->_productTypeFactory->create($productTypeConfig['model'], ['params' => $params]))
            ) {
                throw new LocalizedException(
                    __('Entity type model \'%1\' is not found', $productTypeConfig['model'])
                );
            }
            if (! $model instanceof \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType) {
                throw new LocalizedException(
                    __(
                        'Entity type model must be an instance of '
                        . \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class
                    )
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$productTypeName] = $model;
            }
            $this->_fieldsMap         = array_merge($this->_fieldsMap, $model->getCustomFieldsMapping());
            $this->_specialAttributes = array_merge($this->_specialAttributes, $model->getParticularAttributes());
        }
        $this->_initErrorTemplates();
        // remove doubles
        $this->_specialAttributes = array_unique($this->_specialAttributes);

        return $this;
    }

    /**
     * Create Product entity from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _importData()
    {
        $this->_validatedRows = null;
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteProducts();
        } elseif (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->_replaceFlag = true;
            $this->_replaceProducts();
        } else {
            $this->_saveProductsData();
        }
        $this->_eventManager->dispatch('catalog_product_import_finish_before', ['adapter' => $this]);
        return true;
    }

    /**
     * Gather and save information about product entities.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws LocalizedException
     */
    protected function _saveProducts()
    {
        $priceIsGlobal   = $this->_catalogData->isPriceGlobal();
        $productLimit    = null;
        $productsQty     = null;
        $entityLinkField = $this->getProductEntityLinkField();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn          = [];
            $entityRowsUp          = [];
            $attributes            = [];
            $this->websitesCache   = [];
            $this->categoriesCache = [];
            $tierPrices            = [];
            $mediaGallery          = [];
            $labelsForUpdate       = [];
            $uploadedImages        = [];
            $previousType          = null;
            $prevAttributeSet      = null;
            $existingImages        = $this->getExistingImages($bunch);

            foreach ($bunch as $rowNum => $rowData) {
                // reset category processor's failed categories array
                $this->categoryProcessor->clearFailedCategories();

                if (! $this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);

                $rowData[self::URL_KEY] = $this->getUrlKey($rowData);

                $rowSku = $rowData[self::COL_SKU];

                if (null === $rowSku) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                } elseif (self::SCOPE_STORE == $rowScope) {
                    // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE]     = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                    $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                }

                // 1. Entity phase
                if ($this->isSkuExist($rowSku)) {
                    // existing row
                    if (isset($rowData['attribute_set_code'])) {
                        $attributeSetId = $this->catalogConfig->getAttributeSetId(
                            $this->getEntityTypeId(),
                            $rowData['attribute_set_code']
                        );

                        // wrong attribute_set_code was received
                        if (! $attributeSetId) {
                            throw new LocalizedException(
                                __(
                                    'Wrong attribute set code "%1", please correct it and try again.',
                                    $rowData['attribute_set_code']
                                )
                            );
                        }
                    } else {
                        $attributeSetId = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    }

                    $entityRowsUp[] = [
                        'updated_at'       => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        'attribute_set_id' => $attributeSetId,
                        $entityLinkField   => $this->getExistingSku($rowSku)[$entityLinkField]
                    ];
                } else {
                    if (! $productLimit || $productsQty < $productLimit) {
                        $entityRowsIn[strtolower($rowSku)] = [
                            'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
                            'type_id'          => $this->skuProcessor->getNewSku($rowSku)['type_id'],
                            'sku'              => $rowSku,
                            'has_options'      => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
                            'created_at'       => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                            'updated_at'       => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        ];
                        $productsQty++;
                    } else {
                        $rowSku = null;
                        // sign for child rows to be skipped
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                }

                if (! array_key_exists($rowSku, $this->websitesCache)) {
                    $this->websitesCache[$rowSku] = [];
                }
                // 2. Product-to-Website phase
                if (! empty($rowData[self::COL_PRODUCT_WEBSITES])) {
                    $websiteCodes = explode($this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES]);
                    foreach ($websiteCodes as $websiteCode) {
                        $websiteId                                = $this->storeResolver->getWebsiteCodeToId($websiteCode);
                        $this->websitesCache[$rowSku][$websiteId] = true;
                    }
                }

                // 3. Categories phase
                if (! array_key_exists($rowSku, $this->categoriesCache)) {
                    $this->categoriesCache[$rowSku] = [];
                }
                $rowData['rowNum'] = $rowNum;
                $categoryIds       = $this->processRowCategories($rowData);
                foreach ($categoryIds as $id) {
                    $this->categoriesCache[$rowSku][$id] = true;
                }
                unset($rowData['rowNum']);

                // 4.1. Tier prices phase
                if (! empty($rowData['_tier_price_website'])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups'        => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                        'qty'               => $rowData['_tier_price_qty'],
                        'value'             => $rowData['_tier_price_price'],
                        'website_id'        => self::VALUE_ALL == $rowData['_tier_price_website'] ||
                        $priceIsGlobal ? 0 : $this->storeResolver->getWebsiteCodeToId($rowData['_tier_price_website']),
                    ];
                }

                if (! $this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                // 5. Media gallery phase
                $disabledImages = [];
                list($rowImages, $rowLabels) = $this->getImagesFromRow($rowData);
                $storeId = ! empty($rowData[self::COL_STORE])
                    ? $this->getStoreIdByCode($rowData[self::COL_STORE])
                    : Store::DEFAULT_STORE_ID;
                if (isset($rowData['_media_is_disabled']) && strlen(trim($rowData['_media_is_disabled']))) {
                    $disabledImages = array_flip(
                        explode($this->getMultipleValueSeparator(), $rowData['_media_is_disabled'])
                    );
                    if (empty($rowImages)) {
                        foreach (array_keys($disabledImages) as $disabledImage) {
                            $rowImages[self::COL_MEDIA_IMAGE][] = $disabledImage;
                        }
                    }
                }
                $rowData[self::COL_MEDIA_IMAGE] = [];

                /*
                 * Note: to avoid problems with undefined sorting, the value of media gallery items positions
                 * must be unique in scope of one product.
                 */
                $position = 0;
                foreach ($rowImages as $column => $columnImages) {
                    foreach ($columnImages as $columnImageKey => $columnImage) {
                        if (! isset($uploadedImages[$columnImage])) {
                            $uploadedFile = $this->uploadMediaFiles($columnImage, true);
                            $uploadedFile = $uploadedFile ? : $this->getSystemFile($columnImage);
                            if ($uploadedFile) {
                                $uploadedImages[$columnImage] = $uploadedFile;
                            } else {
                                //                                $this->addRowError(
                                //                                    ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                //                                    $rowNum,
                                //                                    null,
                                //                                    null,
                                //                                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                //                                );
                            }
                        } else {
                            $uploadedFile = $uploadedImages[$columnImage];
                        }

                        if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                            $rowData[$column] = $uploadedFile;
                        }

                        if ($uploadedFile && ! isset($mediaGallery[$storeId][$rowSku][$uploadedFile])) {
                            if (isset($existingImages[$rowSku][$uploadedFile])) {
                                if (isset($rowLabels[$column][$columnImageKey])
                                    && $rowLabels[$column][$columnImageKey] != $existingImages[$rowSku][$uploadedFile]['label']
                                ) {
                                    $labelsForUpdate[] = [
                                        'label'     => $rowLabels[$column][$columnImageKey],
                                        'imageData' => $existingImages[$rowSku][$uploadedFile]
                                    ];
                                }
                            } else {
                                if ($column == self::COL_MEDIA_IMAGE) {
                                    $rowData[$column][] = $uploadedFile;
                                }
                                $mediaGallery[$storeId][$rowSku][$uploadedFile] = [
                                    'attribute_id' => $this->getMediaGalleryAttributeId(),
                                    'label'        => isset($rowLabels[$column][$columnImageKey]) ? $rowLabels[$column][$columnImageKey] : '',
                                    'position'     => ++$position,
                                    'disabled'     => isset($disabledImages[$columnImage]) ? '1' : '0',
                                    'value'        => $uploadedFile,
                                ];
                            }
                        }
                    }
                }

                // 6. Attributes phase
                $rowStore    = (self::SCOPE_STORE == $rowScope)
                    ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
                    : 0;
                $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                if (! is_null($productType)) {
                    $previousType = $productType;
                }
                if (isset($rowData[self::COL_ATTR_SET])) {
                    $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                }
                if (self::SCOPE_NULL == $rowScope) {
                    // for multiselect attributes only
                    if (! is_null($prevAttributeSet)) {
                        $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                    }
                    if (is_null($productType) && ! is_null($previousType)) {
                        $productType = $previousType;
                    }
                    if (is_null($productType)) {
                        continue;
                    }
                }

                $productTypeModel = $this->_productTypeModels[$productType];
                if (! empty($rowData['tax_class_name'])) {
                    $rowData['tax_class_id'] =
                        $this->taxClassProcessor->upsertTaxClass($rowData['tax_class_name'], $productTypeModel);
                }

                if ($this->getBehavior() == Import::BEHAVIOR_APPEND ||
                    empty($rowData[self::COL_SKU])
                ) {
                    $rowData = $productTypeModel->clearEmptyData($rowData);
                }

                $rowData = $productTypeModel->prepareAttributesWithDefaultValueForSave(
                    $rowData,
                    ! $this->isSkuExist($rowSku)
                );
                $product = $this->_proxyProdFactory->create(['data' => $rowData]);

                foreach ($rowData as $attrCode => $attrValue) {
                    $attribute = $this->retrieveAttributeByCode($attrCode);

                    if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                        // skip attribute processing for SCOPE_NULL rows
                        continue;
                    }
                    $attrId    = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds  = [0];

                    if (
                        'datetime' == $attribute->getBackendType()
                        && (
                            in_array($attribute->getAttributeCode(), $this->dateAttrCodes)
                            || $attribute->getIsUserDefined()
                        )
                    ) {
                        $attrValue = $this->dateTime->formatDate($attrValue, false);
                    } elseif ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                        $attrValue = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            $this->_localeDate->date($attrValue)->getTimestamp()
                        );
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (! isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->storeResolver->getStoreIdToWebsiteStoreIds($rowStore);
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = [$rowStore];
                        }
                        if (! $this->isSkuExist($rowSku)) {
                            $storeIds[] = 0;
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        if (! isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                        }
                    }
                    // restore 'backend_model' to avoid 'default' setting
                    $attribute->setBackendModel($backModel);
                }
            }

            foreach ($bunch as $rowNum => $rowData) {
                if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    unset($bunch[$rowNum]);
                }
            }

            $this->saveProductEntity(
                $entityRowsIn,
                $entityRowsUp
            )->_saveProductWebsites(
                $this->websitesCache
            )->_saveProductCategories(
                $this->categoriesCache
            )->_saveProductTierPrices(
                $tierPrices
            )->_saveMediaGallery(
                $mediaGallery
            )->_saveProductAttributes(
                $attributes
            )->updateMediaGalleryLabels(
                $labelsForUpdate
            );

            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after',
                ['adapter' => $this, 'bunch' => $bunch]
            );
        }

        return $this;
    }

    /**
     * Update media gallery labels
     *
     * @param array $labels
     * @return void
     */
    private function updateMediaGalleryLabels(array $labels)
    {
        if (empty($labels)) {
            return;
        }
        $this->mediaProcessor->updateMediaGalleryLabels($labels);
    }

    /**
     * Save product media gallery.
     *
     * @param array $mediaGalleryData
     * @return $this
     */
    protected function _saveMediaGallery(array $mediaGalleryData)
    {
        if (empty($mediaGalleryData)) {
            return $this;
        }
        $this->mediaProcessor->saveMediaGallery($mediaGalleryData);

        return $this;
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (! $this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Get existing product data for specified SKU
     *
     * @param string $sku
     * @return array
     */
    private function getExistingSku($sku)
    {
        return $this->_oldSku[strtolower($sku)];
    }

    /**
     * Check if product exists for specified SKU
     *
     * @param string $sku
     * @return bool
     */
    private function isSkuExist($sku)
    {
        $sku = strtolower($sku);
        return isset($this->_oldSku[$sku]);
    }

    /**
     * Try to find file by it's path.
     *
     * @param string $fileName
     * @return string
     */
    private function getSystemFile($fileName)
    {
        $filePath = 'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . $fileName;
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $read */
        $read = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        return $read->isExist($filePath) && $read->isReadable($filePath) ? $fileName : '';
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            // check that row is already validated
            return ! $this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;

        $rowScope = $this->getRowScope($rowData);
        $sku      = $rowData[self::COL_SKU];

        // BEHAVIOR_DELETE and BEHAVIOR_REPLACE use specific validation logic
        if (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && ! $this->isSkuExist($sku)) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
        }
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && ! $this->isSkuExist($sku)) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }

        //        if (! $this->validator->isValid($rowData)) {
        //            foreach ($this->validator->getMessages() as $message) {
        //                $this->addRowError($message, $rowNum, $this->validator->getInvalidAttribute());
        //            }
        //        }

        if (null === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
        } elseif (false === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_ROW_IS_ORPHAN, $rowNum);
        } elseif (self::SCOPE_STORE == $rowScope
            && ! $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
        ) {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_STORE, $rowNum);
        }

        // SKU is specified, row is SCOPE_DEFAULT, new product block begins
        $this->_processedEntitiesCount++;

        if ($this->isSkuExist($sku) && Import::BEHAVIOR_REPLACE !== $this->getBehavior()) {
            // can we get all necessary data from existent DB product?
            // check for supported type of existing product
            if (isset($this->_productTypeModels[$this->getExistingSku($sku)['type_id']])) {
                $this->skuProcessor->addNewSku(
                    $sku,
                    $this->prepareNewSkuData($sku)
                );
            } else {
                $this->addRowError(ValidatorInterface::ERROR_TYPE_UNSUPPORTED, $rowNum);
            }
        } else {
            // validate new product type and attribute set
            if (! isset($rowData[self::COL_TYPE]) || ! isset($this->_productTypeModels[$rowData[self::COL_TYPE]])) {
                $this->addRowError(ValidatorInterface::ERROR_INVALID_TYPE, $rowNum);
            } elseif (! isset(
                    $rowData[self::COL_ATTR_SET]
                ) || ! isset(
                    $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]]
                )
            ) {
                $this->addRowError(ValidatorInterface::ERROR_INVALID_ATTR_SET, $rowNum);
            } elseif (is_null($this->skuProcessor->getNewSku($sku))) {
                $this->skuProcessor->addNewSku(
                    $sku,
                    [
                        'row_id'        => null,
                        'entity_id'     => null,
                        'type_id'       => $rowData[self::COL_TYPE],
                        'attr_set_id'   => $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]],
                        'attr_set_code' => $rowData[self::COL_ATTR_SET],
                    ]
                );
            }
        }

        if (! $this->getErrorAggregator()->isRowInvalid($rowNum)) {
            $newSku = $this->skuProcessor->getNewSku($sku);
            // set attribute set code into row data for followed attribute validation in type model
            $rowData[self::COL_ATTR_SET] = $newSku['attr_set_code'];

            /** @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType $productTypeValidator */
            // isRowValid can add error to general errors pull if row is invalid
            $productTypeValidator = $this->_productTypeModels[$newSku['type_id']];
            $productTypeValidator->isRowValid(
                $rowData,
                $rowNum,
                ! ($this->isSkuExist($sku) && Import::BEHAVIOR_REPLACE !== $this->getBehavior())
            );
        }
        // validate custom options
        $this->getOptionEntity()->validateRow($rowData, $rowNum);

        //        if ($this->isNeedToValidateUrlKey($rowData)) {
        $urlKey     = strtolower($this->getUrlKey($rowData));
        $storeCodes = empty($rowData[self::COL_STORE_VIEW_CODE])
            ? array_flip($this->storeResolver->getStoreCodeToId())
            : explode($this->getMultipleValueSeparator(), $rowData[self::COL_STORE_VIEW_CODE]);
        foreach ($storeCodes as $storeCode) {
            $storeId          = $this->storeResolver->getStoreCodeToId($storeCode);
            $productUrlSuffix = $this->getProductUrlSuffix($storeId);
            $urlPath          = $urlKey . $productUrlSuffix;
            if (empty($this->urlKeys[$storeId][$urlPath])
                || ($this->urlKeys[$storeId][$urlPath] == $sku)
            ) {
                $this->urlKeys[$storeId][$urlPath]    = $sku;
                $this->rowNumbers[$storeId][$urlPath] = $rowNum;
            } else {
                //                $message = sprintf(
                //                    $this->retrieveMessageTemplate(ValidatorInterface::ERROR_DUPLICATE_URL_KEY),
                //                    $urlKey,
                //                    $this->urlKeys[$storeId][$urlPath]
                //                );
                //                $this->addRowError(
                //                    ValidatorInterface::ERROR_DUPLICATE_URL_KEY,
                //                    $rowNum,
                //                    $rowData[self::COL_NAME],
                //                    $message
                //                );
            }
        }
        //        }
        return ! $this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Set valid attribute set and product type to rows with all scopes
     * to ensure that existing products doesn't changed.
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        $rowData = $this->_customFieldsMapping($rowData);

        $rowData = \Magento\ImportExport\Model\Import\Entity\AbstractEntity::_prepareRowForDb($rowData);

        static $lastSku = null;

        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            return $rowData;
        }

        $lastSku = $rowData[self::COL_SKU];

        if ($this->isSkuExist($lastSku)) {
            $newSku                      = $this->skuProcessor->getNewSku($lastSku);
            $rowData[self::COL_ATTR_SET] = $newSku['attr_set_code'];
            $rowData[self::COL_TYPE]     = $newSku['type_id'];
        }

        return $rowData;
    }

    /**
     * Get existing images for current bunch
     *
     * @param array $bunch
     * @return array
     */
    protected function getExistingImages($bunch)
    {
        return $this->mediaProcessor->getExistingImages($bunch);
    }

    /**
     * Uploading files into the "catalog/product" media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @param bool $renameFileOff
     * @return string
     */
    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        try {
            $res = $this->_getUploader()->move($fileName, $renameFileOff);
            return $res['file'];
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return '';
        }
    }

    /**
     * Prepare new SKU data
     *
     * @param string $sku
     * @return array
     */
    private function prepareNewSkuData($sku)
    {
        $data = [];
        foreach ($this->getExistingSku($sku) as $key => $value) {
            $data[$key] = $value;
        }

        $data['attr_set_code'] = $this->_attrSetIdToName[$this->getExistingSku($sku)['attr_set_id']];

        return $data;
    }

    /**
     * Validate data rows and save bunches to DB.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveValidatedBunches()
    {
        $source          = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows       = [];
        $startNewBunch   = false;
        $nextRowBackup   = [];
        $maxDataSize     = $this->_resourceHelper->getMaxDataSize();
        $bunchSize       = $this->_importExportData->getBunchSize();

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || ! $source->valid()) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows       = $nextRowBackup;
                $currentDataSize = strlen($this->getSerializer()->serialize($bunchRows));
                $startNewBunch   = false;
                $nextRowBackup   = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                $rowData = $this->_customFieldsMapping($rowData);

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize           += $rowSize;
                    }
                }
                $source->next();
            }
        }

        //        $this->checkUrlKeyDuplicates();
        $this->getOptionEntity()->validateAmbiguousData();
        return $this;
        //        return \Magento\ImportExport\Model\Import\Entity\AbstractEntity::_saveValidatedBunches();
    }

    /**
     * Get Serializer instance
     *
     * Workaround. Only way to implement dependency and not to break inherited child classes
     *
     * @return Json
     * @deprecated 100.2.0
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = ObjectManager::getInstance()->get(Json::class);
        }
        return $this->serializer;
    }

    /**
     * Custom fields mapping for changed purposes of fields and field names.
     *
     * @param array $rowData
     *
     * @return array
     */
    public function _customFieldsMapping($rowData)
    {
        if (! $rowData[self::COL_TYPE]) {
            $rowData[self::COL_TYPE] = 'simple';
        }

        if ($rowData['price'] == '#N/A' || $rowData['price'] == '') {
            $rowData['price'] = 0;
        }

        if ($rowData['qty'] == '') {
            $rowData['qty'] = '1000';
        }

        if ($rowData['image'] != '') {
            $rowData['base_image'] = $rowData['small_image'] = $rowData['thumbnail_image'] = str_replace('media/images/',
                '', $rowData['image']);
        }

        $rowData['weight']                      = 1;
        $rowData['manage_stock']                = 1;
        $rowData['use_config_min_qty']          = 1;
        $rowData['use_config_max_sale_qty']     = 1;
        $rowData['use_config_min_sale_qty']     = 1;
        $rowData['use_config_backorders']       = 1;
        $rowData['use_config_notify_stock_qty'] = 1;
        $rowData['use_config_enable_qty_inc']   = 1;
        $rowData['is_in_stock']                 = 1;

        if ($rowData['product_websites'] == '') {
            $rowData['product_websites'] = 'base';
        }

        if (! $rowData['categories']) {
            $rowData['categories'] = '';
        }

        if ($rowData['additional_attributes']) {
            $rowData['size'] = $rowData['additional_attributes'];
        }

        if ($rowData[self::COL_TYPE] == 'simple') {
            $rowData[self::COL_VISIBILITY] = 'Not Visible Individually';
        } elseif ($rowData[self::COL_TYPE] == 'configurable') {
            $rowData[self::COL_VISIBILITY] = 'Catalog, Search';
        }

        $rowData['url_key'] = isset($rowData['size']) ? $this->productUrl->formatUrlKey($rowData[self::COL_NAME] . '-' .
            $rowData['size']) : $this->productUrl->formatUrlKey($rowData[self::COL_NAME]);

        if (! $rowData[$this->_fieldsMap[self::COL_ATTR_SET]]) {
            $rowData[$this->_fieldsMap[self::COL_ATTR_SET]] = 'Default';
        }

        foreach ($this->_fieldsMap as $systemFieldName => $fileFieldName) {
            if (array_key_exists($fileFieldName, $rowData)) {
                $rowData[$systemFieldName] = $rowData[$fileFieldName];
            }
        }

        $rowData = $this->_parseAdditionalAttributes($rowData);

        $rowData = $this->_setStockUseConfigFieldsValues($rowData);
        if (array_key_exists('status', $rowData)
            && $rowData['status'] != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        ) {
            if ($rowData['status'] == 'yes') {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            } elseif (! empty($rowData['status']) || $this->getRowScope($rowData) == self::SCOPE_DEFAULT) {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            }
        }

        return $rowData;
    }

    /**
     * Parse attributes names and values string to array.23456789
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _parseAdditionalAttributes($rowData)
    {
        if (empty($rowData['additional_attributes'])) {
            return $rowData;
        }
        $rowData = array_merge($rowData, $this->parseAdditionalAttributes($rowData['additional_attributes']));
        return $rowData;
    }

    /**
     * Retrieves additional attributes in format:
     * [
     *      code1 => value1,
     *      code2 => value2,
     *      ...
     *      codeN => valueN
     * ]
     *
     * @param string $additionalAttributes Attributes data that will be parsed
     * @return array
     */
    private function parseAdditionalAttributes($additionalAttributes)
    {
        return empty($this->_parameters[Import::FIELDS_ENCLOSURE])
            ? $this->parseAttributesWithoutWrappedValues($additionalAttributes)
            : $this->parseAttributesWithWrappedValues($additionalAttributes);
    }

    /**
     * Parses data and returns attributes in format:
     * [
     *      code1 => value1,
     *      code2 => value2,
     *      ...
     *      codeN => valueN
     * ]
     *
     * @param string $attributesData Attributes data that will be parsed. It keeps data in format:
     *      code=value,code2=value2...,codeN=valueN
     * @return array
     */
    private function parseAttributesWithoutWrappedValues($attributesData)
    {
        $attributeNameValuePairs = explode($this->getMultipleValueSeparator(), $attributesData);
        $preparedAttributes      = [];
        $code                    = '';
        foreach ($attributeNameValuePairs as $attributeData) {
            //process case when attribute has ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR inside its value
            if (strpos($attributeData, self::PAIR_NAME_VALUE_SEPARATOR) === false) {
                if (! $code) {
                    continue;
                }
                $preparedAttributes[$code] .= $this->getMultipleValueSeparator() . $attributeData;
                continue;
            }
            list($code, $value) = explode(self::PAIR_NAME_VALUE_SEPARATOR, $attributeData, 2);
            $code                      = mb_strtolower($code);
            $preparedAttributes[$code] = $value;
        }
        return $preparedAttributes;
    }

    /**
     * Parses data and returns attributes in format:
     * [
     *      code1 => value1,
     *      code2 => value2,
     *      ...
     *      codeN => valueN
     * ]
     * All values have unescaped data except mupliselect attributes,
     * they should be parsed in additional method - parseMultiselectValues()
     *
     * @param string $attributesData Attributes data that will be parsed. It keeps data in format:
     *      code="value",code2="value2"...,codeN="valueN"
     *  where every value is wrapped in double quotes. Double quotes as part of value should be duplicated.
     *  E.g. attribute with code 'attr_code' has value 'my"value'. This data should be stored as attr_code="my""value"
     *
     * @return array
     */
    private function parseAttributesWithWrappedValues($attributesData)
    {
        $attributes = [];
        preg_match_all('~((?:[a-zA-Z0-9_])+)="((?:[^"]|""|"' . $this->getMultiLineSeparatorForRegexp() . '")+)"+~',
            $attributesData,
            $matches
        );
        foreach ($matches[1] as $i => $attributeCode) {
            $attribute                                 = $this->retrieveAttributeByCode($attributeCode);
            $value                                     = 'multiselect' != $attribute->getFrontendInput()
                ? str_replace('""', '"', $matches[2][$i])
                : '"' . $matches[2][$i] . '"';
            $attributes[mb_strtolower($attributeCode)] = $value;
        }
        return $attributes;
    }

    /**
     * Retrieves escaped PSEUDO_MULTI_LINE_SEPARATOR if it is metacharacter for regular expression
     *
     * @return string
     */
    private function getMultiLineSeparatorForRegexp()
    {
        if (! $this->multiLineSeparatorForRegexp) {
            $this->multiLineSeparatorForRegexp = in_array(self::PSEUDO_MULTI_LINE_SEPARATOR, str_split('[\^$.|?*+(){}'))
                ? '\\' . self::PSEUDO_MULTI_LINE_SEPARATOR
                : self::PSEUDO_MULTI_LINE_SEPARATOR;
        }
        return $this->multiLineSeparatorForRegexp;
    }

    /**
     * Set values in use_config_ fields.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _setStockUseConfigFieldsValues($rowData)
    {
        $useConfigFields = [];
        foreach ($rowData as $key => $value) {
            $useConfigName = self::INVENTORY_USE_CONFIG_PREFIX . $key;
            if (isset($this->defaultStockData[$key])
                && isset($this->defaultStockData[$useConfigName])
                && ! empty($value)
                && empty($rowData[$useConfigName])
            ) {
                $useConfigFields[$useConfigName] = ($value == self::INVENTORY_USE_CONFIG) ? 1 : 0;
            }
        }
        $rowData = array_merge($rowData, $useConfigFields);
        return $rowData;
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function processRowCategories($rowData)
    {
        $categoriesString = empty($rowData[self::COL_CATEGORY]) ? '' : $rowData[self::COL_CATEGORY];
        $categoriesString = $this->extractCategory($categoriesString);
        $categoryIds      = [];
        if (! empty($categoriesString)) {
            $categoryIds = $this->categoryProcessor->upsertCategories(
                $categoriesString,
                $this->getMultipleValueSeparator()
            );
            foreach ($this->categoryProcessor->getFailedCategories() as $error) {
                $this->errorAggregator->addError(
                    AbstractEntity::ERROR_CODE_CATEGORY_NOT_VALID,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
                    $rowData['rowNum'],
                    self::COL_CATEGORY,
                    __('Category "%1" has not been created.', $error['category'])
                    . ' ' . $error['exception']->getMessage()
                );
            }
        }
        return $categoryIds;
    }

    public function extractCategory($categoriesString)
    {
        $categories = explode(',', $categoriesString);
        if (count($categories)) {
            foreach ($categories as $m => $category) {
                if ($category != '') {
                    $children = explode('/', $category);
                    if (count($children)) {
                        if ($children[0] == 'products') {
                            $children[0] = 'Shop Categories';
                        }
                        foreach ($children as $k => $child) {
                            $children[$k] = ucfirst(str_replace('-', ' ', $child));
                        }
                        array_unshift($children, 'Default Category');
                        $categories[$m] = implode('/', $children);
                    }
                }
            }
            $categories = implode(',', $categories);

            return $categories;
        }

        return '';
    }
}
