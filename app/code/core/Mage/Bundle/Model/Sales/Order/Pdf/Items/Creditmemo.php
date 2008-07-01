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
 * @category   Mage
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Creditmemo Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Model_Sales_Order_Pdf_Items_Creditmemo extends Mage_Bundle_Model_Sales_Order_Pdf_Items_Abstract
{
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $items = $this->getChilds($item);

        $bundleOptions = $this->getBundleOptions($item->getOrderItem());

        $_prevOptionLabel = '';

        $shift{0} = 0;

        foreach ($items as $_item) {

            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 7);

            if (empty($_option['value'])) {
                $_option = array_shift($bundleOptions);
            }

            if ($_item->getOrderItem()->getParentItem()) {
                if ($_prevOptionLabel != $_option['label']) {
                    $page->drawText($_option['label'], 65, $pdf->y, 'UTF-8');
                    $_prevOptionLabel = $_option['label'];
                    $pdf->y -= 10;
                }
            }

            if ($this->canShowPriceInfo($_item)) {
                $page->drawText($_item->getQty()*1, 35, $pdf->y, 'UTF-8');
            }


            if ($_item->getOrderItem()->getParentItem()) {
                $feed = 65;
            } else {
                $feed = 60;
            }
            if (strlen($_item->getName()) > 60) {
                $drawTextValue = explode(" ", $_item->getName());
                $drawTextParts = array();
                $i = 0;
                foreach ($drawTextValue as $drawTextPart) {
                    if (!empty($drawTextParts{$i}) &&
                        (strlen($drawTextParts{$i}) + strlen($drawTextPart)) < 60 ) {
                        $drawTextParts{$i} .= ' '. $drawTextPart;
                    } else {
                        $i++;
                        $drawTextParts{$i} = $drawTextPart;
                    }
                }
                $shift{0} = 0;
                foreach ($drawTextParts as $drawTextPart) {
                    $page->drawText($drawTextPart, $feed, $pdf->y-$shift{0}, 'UTF-8');
                    $shift{0} += 10;
                }

            } else {
                $page->drawText($item->getName(), $feed, $pdf->y, 'UTF-8');
            }

            if (strlen($_item->getSku()) > 30) {
                $drawTextValue = str_split($_item->getSku(), 30);
                $shift{2} = 0;
                foreach ($drawTextValue as $drawTextPart) {
                    $page->drawText($drawTextPart, 265, $pdf->y-$shift{2}, 'UTF-8');
                    $shift{2} += 10;
                }

            } else {
                $page->drawText($_item->getSku(), 265, $pdf->y);
            }

            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $page->setFont($font, 7);

            if ($this->canShowPriceInfo($_item)) {
                $page->drawText($order->formatPriceTxt($item->getTaxAmount()), 380, $pdf->y, 'UTF-8');
                $page->drawText($order->formatPriceTxt(-$item->getDiscountAmount()), 430, $pdf->y, 'UTF-8');
                $page->drawText($order->formatPriceTxt($item->getRowTotal()), 480, $pdf->y, 'UTF-8');

                $row_total = $order->formatPriceTxt($item->getRowTotal()+$item->getTaxAmount()-$item->getDiscountAmount());

                $page->drawText($row_total, 565-$pdf->widthForStringUsingFontSize($row_total, $font, 7), $pdf->y, 'UTF-8');
            }

            $pdf->y -=max($shift)+10;
        }

        if ($item->getOrderItem()->getProductOptions() || $item->getOrderItem()->getDescription()) {

            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 7);

            $shift{1} = 10;
            $options = $item->getOrderItem()->getProductOptions();
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $optionTxt = strip_tags($option['label']).':'.strip_tags($option['value']);
                    if (strlen($optionTxt) > 80) {
                        $optionTxt = str_split($optionTxt, 80);
                        foreach ($optionTxt as $_option) {
                            $page->drawText($_option, 65, $pdf->y-$shift{1}, 'UTF-8');
                            $shift{1} += 10;
                        }
                    } else {
                        $page->drawText($optionTxt, 65, $pdf->y-$shift{1}, 'UTF-8');
                        $shift{1} += 10;
                    }
                }
            }

            foreach ($this->_parseDescription() as $description){
                $page->drawText(strip_tags($description), 65, $pdf->y-$shift{1}, 'UTF-8');
                $shift{1} += 10;
            }

            $pdf->y -= max($shift)+10;
        }
    }
}