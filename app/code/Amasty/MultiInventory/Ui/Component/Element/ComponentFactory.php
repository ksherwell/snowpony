<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Element;

/**
 * Class Component
 * @package Amasty\MultiInventory\View
 */
class ComponentFactory extends \Magento\Framework\View\Element\UiComponentFactory
{

    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $identifier
     * @param null $name
     * @param array $arguments
     * @return \Magento\Framework\View\Element\UiComponentInterface|mixed
     */
    public function create($identifier, $name = null, array $arguments = [])
    {
        if (!$this->getClass()) {
            return parent::create($identifier, $name, $arguments);
        } else {
            $rawComponentData = $this->componentManager->createRawComponentData($name);
            list($className, $componentArguments) = $this->argumentsResolver($identifier, $rawComponentData);
            $processedArguments = array_replace_recursive($componentArguments, $arguments);

            $component = $this->objectManager->create(
                $this->getClass(),
                $processedArguments
            );
            return $component;
        }
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
}
