<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Setup\EavSetup;
use BBApps\DataImporter\Helper\Data;
use BBApps\DataImporter\Model\Csv;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use BBApps\DataImporter\Model\Attribute;

class AssignAttribute extends \BBApps\DataImporter\Command\AbstractImport
{
    protected $commandName = 'assign-attribute';
    protected $commandDescription = 'Assign Attribute';
    protected $fileName = 'assign_attributes.csv';
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var EavSetup
     */
    private $eavSetup;
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        Csv $csv,
        Data $helper,
        EavSetup $eavSetup,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($csv);

        $this->helper = $helper;
        $this->eavSetup = $eavSetup;
        $this->attributeRepository = $attributeRepository;
    }

    protected function isFileRequired()
    {
        return false;
    }

    protected function processRow($rowData, InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        if (!empty($rowData['attribute_code'])) {
            $attributeCode = $this->helper->formatAttributeCode($rowData['attribute_code']);

            $attribute = null;
            try {
                $attribute = $this->attributeRepository->get($attributeCode);
            } catch (\Exception $e) {
                // do nothing
            }

            if ($attribute) {
                $attributeSets = [Attribute::DEFAULT_ATTRIBUTE_SET];
                if (!empty($rowData['attribute_set'])) {
                    $attributeSets = explode('|', $rowData['attribute_set']);
                }
                if (in_array(Attribute::ALL_ATTRIBUTE_SETS_VALUE, array_map('strtolower', $attributeSets))) {
                    $attributeSets = $this->helper->getListAttributeSets();
                }

                $attributeGroup = Attribute::DEFAULT_ATTRIBUTE_GROUP;
                if (!empty($rowData['attribute_group_name'])) {
                    $attributeGroup = $rowData['attribute_group_name'];
                }

                // assign to attribute set
                foreach ($attributeSets as $attributeSet) {
                    try {
                        $groupValue = $this->helper->getGroupValue($attributeSet, $attributeGroup);

                        $this->eavSetup->addAttributeToGroup(
                            ProductAttributeInterface::ENTITY_TYPE_CODE,
                            $attributeSet,
                            $groupValue,
                            $attribute->getAttributeCode()
                        );
                        $output->writeln('Attribute ' . $attributeCode . ' Is Assigned To Group ' . $attributeGroup
                            . ' Successfully');
                    } catch (\Exception $e) {
                        // do nothing
                    }
                }

                $output->writeln('Attribute ' . $attributeCode . ' Updated Successfully');
            } else {
                $output->writeln('Attribute ' . $attributeCode . ' Updated Un-successfully.');
            }
        }
    }
}
