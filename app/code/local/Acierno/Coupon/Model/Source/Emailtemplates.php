<?php
/**
 * Acierno Coupon
 *
 **/

/**
 * Class Acierno_Coupon_Model_Source_Emailtemplates
 *
 * Dummy class, does nothing
 * TODO: Delete it
 */
class Acierno_Coupon_Model_Source_Emailtemplates
{
    public function toOptionArray()
    {

        //TODO
        /*
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('acierno_carousell')->__('Disabled')),
            array(
                'value' => 1,
                'label' => Mage::helper('acierno_carousell')->__('Enabled'))
        );

        */
    }


    public function toGridArray()
    {
        foreach ($this->toOptionArray() as $option) {
            $array[$option['value']] = $option['label'];
        }
        return $array;
    }

}