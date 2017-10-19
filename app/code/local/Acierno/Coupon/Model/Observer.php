<?php
/**
 * Acierno Coupon
 *
 **/


/**
 * Class Acierno_Coupon_Model_Observer
 *
 * Observer for the save in Admin/configuration
 * @author Michele Acierno <michele.acierno@thinkopen.it>
 * @version 0.1.0
 * @package Cms
 */
class Acierno_Coupon_Model_Observer extends Mage_Core_Model_Session_Abstract
{
    /**
     * observersection
     *
     * Main method of the module, triggered at the save of
     * configuration. Caches the data from the admin/configuration.
     * Then goes checkes if all the date are been compiled.
     * After Initialize a coupon generator for the specific
     * promotion selected in the configuration and generates
     * as many coupon for this promotion as mush have been
     * set.
     *
     * In the end sends them to the email specified, with the
     * selected template and selected email.
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function observersection(Varien_Event_Observer $observer)
    {

        $config = Mage::getStoreConfig('aciernocoupon/general');

        //Check if the module is enabled, if not deploy an error
        //and goes back

        if (!is_null($config['enabled']) && $config['enabled']==1){

            //Check if all the configuration are set, if not deploys
            //an error and goes back

            if (!is_null($config['emailto']) && !is_null($config['code']) &&
                 !is_null($config['amount'])&&  !is_null($config['emailtemplate'])){

                $rule_id = $config['code'];
                $amount  = $config['amount'];
                // Get the rule in question
                $rule = Mage::getModel('salesrule/rule')->load($rule_id);
                $generator = Mage::getModel('salesrule/coupon_massgenerator');

                $parameters = array(
                    'count'=>$amount,
                    'format'=>'alphanumeric',
                    'dash_every_x_characters'=>'',
                    'prefix'=>'',
                    'suffix'=>'',
                    'length'=>8
                );

                if( !empty($parameters['format']) ){
                    switch( strtolower($parameters['format']) ){
                        case 'alphanumeric':
                        case 'alphanum':
                            $generator->setFormat( Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC );
                            break;
                        case 'alphabetical':
                        case 'alpha':
                            $generator->setFormat( Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL );
                            break;
                        case 'numeric':
                        case 'num':
                            $generator->setFormat( Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_NUMERIC );
                            break;
                    }
                }

                $generator->setDash( !empty($parameters['dash_every_x_characters'])? (int) $parameters['dash_every_x_characters'] : 0);
                $generator->setLength( !empty($parameters['length'])? (int) $parameters['length'] : 6);
                $generator->setPrefix( !empty($parameters['prefix'])? $parameters['prefix'] : '');
                $generator->setSuffix( !empty($parameters['suffix'])? $parameters['suffix'] : '');

                // Set the generator, and coupon type so it's able to generate
                $rule->setCouponCodeGenerator($generator);
                $rule->setCouponType( Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO );

                // Get as many coupons as you required
                $count = !empty($parameters['count'])? (int) $parameters['count'] : 1;
                $codes = array();
                $html = "";
                for( $i = 0; $i < $count; $i++ ){
                    $coupon = $rule->acquireCoupon();
                    $coupon->setUsageLimit(1);
                    $coupon->setTimesUsed(0);
                    $coupon->setType(1);
                    $coupon->save();
                    $code = $coupon->getCode();
                    $codes[] = $code;
                    $html = $html.$code."<br>";
                }

                try{
                $storeId = Mage::app()->getStore()->getStoreId();
                $emailTemplate = Mage::getModel('core/email_template')->loadByCode($config['emailtemplate']);
                $vars = array('custom_var1' => $codes, 'custom_var2' => $config['code']);
                $emailTemplate->getProcessedTemplate($vars);
                $emailTemplate->setSenderEmail(Mage::getStoreConfig
                ('trans_email/ident_general/email', $storeId));
                $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name', $storeId));
                $emailTemplate->send($config['emailto'],$config['emailto'], $vars);
                Mage::log($config['emailto'].' ha ricevuto '.$config['amount'].' coupon',
                null, 'coupon.txt', true);
                }catch(Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Error sending the email'));
                }
                return true;
            }
            Mage::getSingleton('adminhtml/session')->addError($this->__('Some required fields are missing'));
            return false;
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Module not enabled'));
        return false;
    }
}