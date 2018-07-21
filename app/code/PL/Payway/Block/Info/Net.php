<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Block\Info;

use Magento\Framework\View\Element\Template;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
class Net extends \Magento\Payment\Block\Info
{
    protected $_template = 'PL_Payway::info/net.phtml';

    /**
     * @var \PL\Payway\Helper\Data
     */
    protected $paywayHelper;


    /**
     * Net constructor.
     * @param \PL\Payway\Helper\Data $paywayHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \PL\Payway\Helper\Data $paywayHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paywayHelper = $paywayHelper;
    }
}
