<?php
/**
 *
 */

namespace BBApps\Seo\Setup;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * InstallData constructor.
     * @param BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $cmsBlocks = [
            [
                'title'      => 'SG GTM code',
                'identifier' => 'gtm_code',
                'is_active'  => 1,
                'stores'     => [1],
                'content'    => ""
            ],
            [
                'title'      => 'SG GTM noscript code',
                'identifier' => 'gtm_code_noscript',
                'is_active'  => 1,
                'stores'     => [1],
                'content'    => ""
            ],
            [
                'title'      => 'B&P - GTM code',
                'identifier' => 'gtm_code',
                'is_active'  => 1,
                'stores'     => [3],
                'content'    => ""
            ],
            [
                'title'      => 'B&P - GTM noscript code',
                'identifier' => 'gtm_code_noscript',
                'is_active'  => 1,
                'stores'     => [3],
                'content'    => ""
            ]
        ];

        /**
         * Insert default and system pages
         */
        foreach ($cmsBlocks as $data) {
            $this->createBlock()->setData($data)->save();
        }

        $setup->endSetup();
    }

    /**
     * @return mixed
     */
    public function createBlock()
    {
        return $this->blockFactory->create();
    }
}
