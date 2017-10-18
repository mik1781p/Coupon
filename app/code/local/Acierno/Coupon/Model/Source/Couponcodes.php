<?php
/**
 * Acierno Coupon
 *
 **/

/**
 * Class Acierno_Coupon_Model_Source_Couponcodes
 *
 * Source model for Coupons,gets all the promotion active.
 */
class Acierno_Coupon_Model_Source_Couponcodes
{

    /**
     * toOptionArray
     *
     * Return the array contaning all the promotion active
     * on Magento as for of array for the admin/configuration
     * display
     * @return array
     */
    public function toOptionArray()
    {

        $rules = Mage::getModel('salesrule/rule')->getCollection()
        ->addFieldToFilter('is_active',1);

        $options = array();

        foreach($rules as $rule){
            $options[]= array(
                'label' => $rule->getName(),
                'value' => $rule->getRuleId()
            );
        }
        return $options;

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