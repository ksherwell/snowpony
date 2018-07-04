<?php
namespace BBApps\DataImporter\Cron;

use Psr\Log\LoggerInterface;
use BBApps\DataImporter\Model\Csv;
use BBApps\DataImporter\Model\TrackingNumber as TrackingNumberModel;

class TrackingNumber
{
    const IMPORT_EXPORT_DIR = 'importexport/';

    protected $fileName = 'import_tracking_number.csv';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var TrackingNumberModel
     */
    private $trackingNumber;

    public function __construct(
        LoggerInterface $logger,
        Csv $csv,
        TrackingNumberModel $trackingNumber
    ) {
        $this->logger = $logger;
        $this->csv = $csv;
        $this->trackingNumber = $trackingNumber;
    }

    public function execute()
    {
        try {
            $sourceCsv = $this->csv->createSourceCsvModel(self::IMPORT_EXPORT_DIR . $this->fileName);
            foreach ($sourceCsv as $rowNum => $rowData) {
                $this->trackingNumber->import($rowData);
            }

            //#todo: move file to archive folder
        } catch (\Exception $e) {
            $this->logger->log('100', $e->getMessage());
        }
    }
}
