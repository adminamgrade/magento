<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2004-2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @return array
     */
    protected function _getBundleOptions($useCache = true)
    {
        $options = array();

        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $this->getProduct()->getTypeInstance();

        // get bundle options
        $optionsQuoteItemOption =  $this->getItem()->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = unserialize($optionsQuoteItemOption->getValue());
        if ($bundleOptionsIds) {
            /**
            * @var Mage_Bundle_Model_Mysql4_Option_Collection
            */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $this->getItem()->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                unserialize($selectionsQuoteItemOption->getValue())
            );

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $option = array('label' => $bundleOption->getTitle(), "value" => array());
                    foreach ($bundleOption->getSelections() as $bundleSelection) {
                        $option['value'][] = sprintf('%d', $this->_getSelectionQty($bundleSelection->getSelectionId())).' x '. $this->htmlEscape($bundleSelection->getName()). ' ' .Mage::helper('core')->currency($this->_getSelectionFinalPrice($bundleSelection));
                    }
                }
                $options[] = $option;
            }
        }
        return $options;
    }

    /**
     * Obtain final price of selection in a bundle product
     *
     * @param Mage_Catalog_Model_Product $selectionProduct
     * @return decimal
     */
    protected function _getSelectionFinalPrice($selectionProduct)
    {
        $bundleProduct = $this->getProduct();
        return $bundleProduct->getPriceModel()->getSelectionFinalPrice(
            $bundleProduct, $selectionProduct,
            $this->getQty(),
            $this->getSelectionQty($selectionProduct->getSelectionId())
        );
    }

    /**
     * Get selection quantity
     *
     * @param int $selectionId
     * @return decimal
     */
    protected function _getSelectionQty($selectionId)
    {
        if ($selectionQty = $this->getProduct()->getCustomOption('selection_qty_' . $selectionId)) {
            return $selectionQty->getValue();
        }
        return 0;
    }

    public function getOptionList()
    {
        return array_merge($this->_getBundleOptions(), parent::getOptionList());
    }
}
