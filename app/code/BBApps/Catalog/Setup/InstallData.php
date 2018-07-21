<?php
/**
 *
 */

namespace BBApps\Catalog\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * CategoryAttribute constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'info_content_1',
                [
                    'type'                       => 'text',
                    'label'                      => 'Additional Data 1',
                    'input'                      => 'textarea',
                    'required'                   => false,
                    'visible_in_advanced_search' => true,
                    'global'                     =>
                        \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible'                    => true,
                    'user_defined'               => true,
                    'searchable'                 => true,
                    'filterable'                 => false,
                    'comparable'                 => false,
                    'visible_on_front'           => true,
                    'unique'                     => false,
                    'group'                      => 'Content',
                    'is_used_in_grid'            => false,
                    'is_visible_in_grid'         => false,
                    'is_filterable_in_grid'      => false,
                    'used_in_product_listing'    => true,
                    'sort_order'                 => 4,
                    'wysiwyg_enabled'            => true,
                    'is_html_allowed_on_front'   => true
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'info_content_2',
                [
                    'type'                       => 'text',
                    'label'                      => 'Additional Data 2',
                    'input'                      => 'textarea',
                    'required'                   => false,
                    'visible_in_advanced_search' => true,
                    'global'                     =>
                        \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible'                    => true,
                    'user_defined'               => true,
                    'searchable'                 => true,
                    'filterable'                 => false,
                    'comparable'                 => false,
                    'visible_on_front'           => true,
                    'unique'                     => false,
                    'group'                      => 'Content',
                    'is_used_in_grid'            => false,
                    'is_visible_in_grid'         => false,
                    'is_filterable_in_grid'      => false,
                    'used_in_product_listing'    => true,
                    'sort_order'                 => 5,
                    'wysiwyg_enabled'            => true,
                    'is_html_allowed_on_front'   => true
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'info_content_3',
                [
                    'type'                       => 'text',
                    'label'                      => 'Additional Data 3',
                    'input'                      => 'textarea',
                    'required'                   => false,
                    'visible_in_advanced_search' => true,
                    'global'                     =>
                        \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible'                    => true,
                    'user_defined'               => true,
                    'searchable'                 => true,
                    'filterable'                 => false,
                    'comparable'                 => false,
                    'visible_on_front'           => true,
                    'unique'                     => false,
                    'group'                      => 'Content',
                    'is_used_in_grid'            => false,
                    'is_visible_in_grid'         => false,
                    'is_filterable_in_grid'      => false,
                    'used_in_product_listing'    => true,
                    'sort_order'                 => 6,
                    'wysiwyg_enabled'            => true,
                    'is_html_allowed_on_front'   => true
                ]
            );
        }

        $setup->endSetup();
    }
}
