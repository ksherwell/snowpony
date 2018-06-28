<?php namespace BoostMyShop\AdvancedStock\Model\Pdf;

/**
 * Class StockTake
 *
 * @package   BoostMyShop\AdvancedStock\Model\Pdf
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StockTake extends AbstractPdf {


    /**
     * Retrieve PDF
     *
     * @param array $stockTakes
     * @return \Zend_Pdf
     */
    public function getPdf($stockTakes = [])
    {
        $this->_beforeGetPdf();
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        $orderBy = [
            'stai_sku' => 'desc',
            'stai_status' => 'desc'
        ];

        foreach($stockTakes as $stockTake){

            $page = $this->newPage();
            $this->insertLogo($page, null);
            $this->_drawInformation($page, $stockTake);
            $this->_drawHeader($page);

            foreach($stockTake->getItems(['order' => $orderBy]) as $item){

                $page = $this->_drawItem($item, $page, $stockTake);

            }

        }

        return $pdf;
    }

    /**
     * @param array $settings
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    /**
     * @param $item
     * @param $page
     * @param $stockTake
     * @return \Zend_Pdf_Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _drawItem($item, $page, $stockTake){

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        //columns headers
        $lines[0][] = ['text' => $item->getstai_expected_qty(), 'feed' => 30, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getstai_scanned_qty(), 'feed' => 90, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getstai_sku(), 'feed' => 180, 'align' => 'right'];
        $lines[0][] = ['text' => $item->getstai_name(), 'feed' => 200, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getstai_manufacturer(), 'feed' => 400, 'align' => 'right'];
        $lines[0][] = ['text' => $item->getstai_location(), 'feed' => 480, 'align' => 'right'];
        $lines[0][] = ['text' => $item->getStatusLabel(), 'feed' => 550, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page = $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;

        return $page;
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param \BoostMyShop\AdvancedStock\Model\StockTake $stockTake
     * @return \Zend_Pdf_Page
     */
    protected function _drawInformation($page, $stockTake){

        /* Add table head */
        $this->_setFontRegular($page, 12);
        $i = 0;

        $info = array();
        $info[] = __('Reference').' : '.$stockTake->getsta_name();
        $info[] = __('Warehouse').' : '.$stockTake->getWarehouseLabel();
        $info[] = __('Created at').' : '.$stockTake->getsta_created_at();
        $info[] = __('Progress').' : '.$stockTake->getsta_progress();
        $productSelectionMode = __('Mode').' : '.$stockTake->getProductSelectionLabel();

        $info[] = $productSelectionMode;
        $info[] = __('Notes').' : ';

        foreach($info as $line){
            $line = str_replace("\r", "", $line);
            if ($line) {
                $page->drawText($line, 25, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }
        }

        foreach(explode("\n", $stockTake->getsta_notes()) as $line) {

            $line = str_replace("\r", "", $line);
            if ($line) {
                $page->drawText($line, 35, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }

        }

        $this->y -= $i * 20;

        return $page;

    }

    /**
     * @param \Zend_Pdf_Page $page
     * @return \Zend_Pdf_Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _drawHeader(\Zend_Pdf_Page $page){

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;

        //columns headers
        $lines[0][] = ['text' => __('Qty Expected'), 'feed' => 30, 'align' => 'left'];
        $lines[0][] = ['text' => __('Qty Scanned'), 'feed' => 90, 'align' => 'left'];
        $lines[0][] = ['text' => __('Sku'), 'feed' => 180, 'align' => 'right'];
        $lines[0][] = ['text' => __('Name'), 'feed' => 200, 'align' => 'left'];
        $lines[0][] = ['text' => __('Manufacturer'), 'feed' => 400, 'align' => 'right'];
        $lines[0][] = ['text' => __('Location'), 'feed' => 480, 'align' => 'right'];
        $lines[0][] = ['text' => __('Status'), 'feed' => 550, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page = $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;

        return $page;

    }
}