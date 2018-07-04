<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ID = 'warehouse_id';
    const TITLE = 'title';
    const CODE = 'code';
    const STORES = 'stores';
    const COUNTRY = 'country';
    const STATE = 'state';
    const CITY = 'city';
    const ADDRESS = 'address';
    const ZIP = 'zip';
    const PHONE = 'phone';
    const EMAIL = 'email';
    const DESCRIPTION = 'description';
    const MANAGE = 'manage';
    const PRIORITY = 'priority';
    const IS_GENERAL = 'is_general';
    const ORDER_EMAIL_NOTIFICATION = 'order_email_notification';
    const LOW_STOCK_NOTIFICATION = 'low_stock_notification';
    const STOCK_ID = 'stock_id';
    const CREATE_TIME = 'create_time';
    const UPDATE_TIME = 'update_time';
    const IS_SHIPPING = 'is_shipping';
    const BACKORDERS = 'backorders';
    const SHIPPINGS = 'shippings';
    const CUSTOMER_GROUPS = 'customer_groups';
    const ITEMS = 'items';
    const REMOVE_ITEMS = 'remove_items';
    const CACHE_TAG = 'warehouse';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getState();
    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getZip();

    /**
     * @return string
     */
    public function getPhone();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return bool
     */
    public function getManage();

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @return bool
     */
    public function getIsGeneral();

    /**
     * @return string
     */
    public function getOrderEmailNotification();

    /**
     * @return string
     */
    public function getLowStockNotification();

    /**
     * @return int|null
     */
    public function getStockId();

    /**
     * @return string
     */
    public function getCreateTime();

    /**
     * @return string
     */
    public function getUpdateTime();

    /**
     * @return bool
     */
    public function getIsShipping();

    /**
     * @return \Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface[]
     */
    public function getCustomerGroups();

    /**
     * @param $id
     * @return int
     */
    public function setId($id);

    /**
     * @param $title
     * @return string
     */
    public function setTitle($title);

    /**
     * @param $code
     * @return string
     */
    public function setCode($code);

    /**
     * @param $country
     * @return string
     */
    public function setCountry($country);

    /**
     * @param $state
     * @return string
     */
    public function setState($state);

    /**
     * @param $city
     * @return string
     */
    public function setCity($city);

    /**
     * @param $address
     * @return string
     */
    public function setAddress($address);

    /**
     * @param $zip
     * @return string
     */
    public function setZip($zip);

    /**
     * @param $phone
     * @return string
     */
    public function setPhone($phone);

    /**
     * @param $email
     * @return string
     */
    public function setEmail($email);

    /**
     * @param $description
     * @return string
     */
    public function setDescription($description);

    /**
     * @param $manage
     * @return bool
     */
    public function setManage($manage);

    /**
     * @param $priority
     * @return int
     */
    public function setPriority($priority);

    /**
     * @param $value
     * @return int
     */
    public function setIsGeneral($value);

    /**
     * @param $notify
     * @return string
     */
    public function setOrderEmailNotification($notify);

    /**
     * @param $notify
     * @return string
     */
    public function setLowStockNotification($notify);

    /**
     * @param $id
     * @return int|null
     */
    public function setStockId($id);

    /**
     * @param $time
     * @return string
     */
    public function setCreateTime($time);

    /**
     * @param $time
     * @return string
     */
    public function setUpdateTime($time);

    /**
     * @param $bool
     * @return bool
     */
    public function setIsShipping($bool);

    /**
     * @param \Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface[]
     * @return $this
     */
    public function setCustomerGroups($groups);

    /**
     * @return int
     */
    public function getBackorders();

    /**
     * @param int $backorders
     *
     * @return $this
     */
    public function setBackorders($backorders);
}
