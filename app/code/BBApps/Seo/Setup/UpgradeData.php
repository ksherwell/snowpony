<?php
/**
 *
 */
namespace BBApps\Seo\Setup;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * UpgradeData constructor.
     * @param BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2', '<')) {

            $cmsBlocks = [
                [
                    'title'      => 'SG Bing UET tag',
                    'identifier' => 'bing_uet_tag',
                    'is_active'  => 1,
                    'stores'     => [1],
                    'content'    => ""
                ],
                [
                    'title'      => 'B&P - Bing UET tag',
                    'identifier' => 'bing_uet_tag',
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
