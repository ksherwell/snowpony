<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Attribute extends \BBApps\DataImporter\Command\AbstractImport
{
    protected $commandName = 'import-attribute';
    protected $commandDescription = 'Import Attribute';
    protected $fileName = 'import_product_attributes.csv';

    /**
     * @var \BBApps\DataImporter\Model\Attribute
     */
    private $attribute;

    public function __construct(
        \BBApps\DataImporter\Model\Csv $csv,
        \BBApps\DataImporter\Model\Attribute $attribute
    ) {
        parent::__construct($csv);

        $this->attribute = $attribute;
    }

    protected function isFileRequired()
    {
        return false;
    }

    protected function processRow($rowData, InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        if (!empty($rowData['attribute_code'])) {
            $attributeCode = $rowData['attribute_code'];
            if ($this->attribute->import($rowData)) {
                $output->writeln('Attribute ' . $attributeCode . ' Imported Successfully');
            } else {
                $output->writeln(
                    'Attribute ' . $attributeCode . ' Imported Un-successfully. Check the error in log file'
                );
            }
        }
    }
}
