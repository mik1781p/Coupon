#Coupon Module

##Description
The coupon module is designed for study purpose.
The main goal of this project is to generate as many coupon as 
specified for a specific promotion (already created by the user).

##Use
Is pretty simple to use this module: 
1) Import it in your magento installation 
2) Create a template email starting from the one specified in this repo.
3) In the configuration panel, go to "Acierno Coupon"
4) Specify ALL the fields required 
5) Press Save to generate and send the coupons for the specific promotion.
6) Enjoy! 


##Explaning the code
Pretty curious are we? Good. Lets start analyzing the code. Rememeber: This
has study purpose ONLY. 

###Configuration

    etc/modules/Acierno_Coupon.xml

as usual, let's start declaring our module as follows

    <?xml version="1.0"?>
    <config>
        <modules>
            <Acierno_Coupon>
                <active>true</active>
                <codePool>local</codePool>
            </Acierno_Coupon>
        </modules>
    </config>
    
The main goal of this declaration is to define WHERE magento must
search for our module if is Active (true), in our case codePool is set to "local".


    app/code/local/Acierno/Coupon/etc/config.xml

As we know, this is the main configuration file for our module.
The focus here will be set on the most important things, for the
full description of the config file i suggest you to go to our
"Hello World" module and guide.

       <events>
            <admin_system_config_changed_section_aciernocoupon>
                <observers>
                    <acierno_coupon>
                        <type>singleton</type>
                        <class>acierno_coupon/observer</class>
                        <method>observersection</method>
                    </acierno_coupon>
                </observers>
            </admin_system_config_changed_section_aciernocoupon>
        </events>
        
Straight to the point: this is the declaration for an Observer.
What is an Observer? Is a design pattern for the concept of 
observing. Magento has a huge emphasis on events and we want
to know, and act, when some certain events are triggered. This
particular case is about the save process in the Admin/configuration
for our module. We want to trigger our observer in that specific
moment. So we declare a new observer

    <observers>
    .
    ..
    .
    </observers>
    
in which we specify the module of this observer

    <acierno_coupon>
    
then the type, the class and method (this one is the method
that will be triggered for the event).

    <template>
        <email>
            <acierno_coupon_custom_email_template translate="label">
                <label>Custom Email</label>
                <file>custom_email.html</file>
                <type>html</type>
            </acierno_coupon_custom_email_template>
        </email>
    </template>
    
This, instead, is the declaration for our custom template for the emails.
To insert a custom template we must declare it in the configuration
file (as we are doing). We specify:

- template
- email
- the module and the reference 
- the label, the file (stored under locale/{leng}/email) and the type
    
The remaning of the config.xml file is pretty common.


    /app/local/Acierno/Coupon/etc/system.xml
    
The system file, as we know, is meant to define the configurations
and formation of the Admin/Configuration zone of the module. Here
we must specify some important things:

    <code translate="label">
        <label>Promotion Code</label>
        <sort_order>200</sort_order>
        <show_in_default>1</show_in_default>
        <show_in_website>1</show_in_website>
        <show_in_store>1</show_in_store>
        <frontend_type>select</frontend_type>
        <source_model>acierno_coupon/source_couponcodes</source_model>
        <tooltip>Is this module enabled?</tooltip>
    </code>

Here we specify that our source_model is a custom one. Why we
do that? Because we must retrive and format the promotion in 
a specific way, so in our Couponcodes (as we will seen soon) we
retrive, formatted, our information.

    <email_template translate="label    ">
        <label>Email Template</label>
        <frontend_type>select</frontend_type>
        <source_model>adminhtml/system_config_source_email_template</source_model>
        <sort_order>800</sort_order>
        <show_in_default>1</show_in_default>
        <show_in_website>1</show_in_website>
        <show_in_store>1</show_in_store>
    </email_template>
    
As before, this select is meant to lean on Magento. Infact
we want to retrive the templates registered under magento 
through magento! This is the most common (and fast) way of 
doing it. 

    <sender_email_identity translate="label">
        <label>Email Sender</label>
        <frontend_type>select</frontend_type>
        <source_model>adminhtml/system_config_source_email_identity</source_model>
        <sort_order>10</sort_order>
        <show_in_default>1</show_in_default>
        <show_in_website>1</show_in_website>
        <show_in_store>1</show_in_store>
    </sender_email_identity>
    
Maybe we want to specify some particular email to send this
coupons, or maybe we want to use a general one. Basically is
the same as before: magento grant us a fast way of doing it.


For adminhtml.xml we have no specific (neither great) implementation.
Is just a common adminhtml.xml file for the permission in the 
admin/configuration zone. 

###Source and Helper - data and sql

The second step is to initialize the helper(declared in the 
configuration file). As usual we will create a Dummy class 
(Data.php) and, to handle some configuration set in the 
system.xml file, we must create one specific Source file:

- Couponcodes.php


Acierno/Coupon/Model/Source/Couponcodes.php


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
        }
    
    
        public function toGridArray()
        {
            foreach ($this->toOptionArray() as $option) {
                $array[$option['value']] = $option['label'];
            }
            return $array;
        }
    

As a source file this particular .php lets us build a select with
all the active promotions set in Magento (by panel).

For futute implementation i've added (without any particular needs)
either the data-install and the sql install files. In this 
version they are pointless, but in future implementation may be
usefull. 


##The Observer

The main force and focus of this module is based on the  Observer.
As we said before an observer is an entity in waiting for a paricular
event (or more than one). When the event triggers, Magento throws
the event and those that are bind to that event take place in action.

Acierno/Coupon/Model/Observer.php

    <?php
    /**
     * Acierno Coupon
     *
     **/
    
    
    /**
     * Class Acierno_Coupon_Model_Observer
     *
     * Observer for the save in Admin/configuration
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
    
                //TODO: add  !is_null($config['emailtemplate']) at the end of testing
                if (!is_null($config['emailto']) && !is_null($config['code']) &&
                     !is_null($config['amount'])){
    
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
                    $vars = array('custom_var1' => $codes[0], ‘custom_var’ => $codes[1]);
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
    
Let's start with some specific explanation of the code: in the first
phase we make sure that we got all the params that we need, if this is
not the case we'll just return an error and go back to che Admin section.
Instead, if we got all the params, that's where the magic starts!
We start by retriving the specific promotion selected:


    $rule = Mage::getModel('salesrule/rule')->load($rule_id);
    $generator = Mage::getModel('salesrule/coupon_massgenerator');
    
The second step is to get a coupon generator, for this coupon
we'll set all the configuration params that we need 

    $parameters = array(
        'count'=>$amount,
        'format'=>'alphanumeric',
        'dash_every_x_characters'=>'',
        'prefix'=>'',
        'suffix'=>'',
        'length'=>8
    );
    
Including the amount of coupons that we shall create, the format,
the length and so on. 
After we associate the coupon code generator to the promotion and set
the type of coupon that we want

    $rule->setCouponCodeGenerator($generator);
    $rule->setCouponType( Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO );

After, we'll cycle on the count variable, creating as many coupon
as we asked. Each coupon is Aquired from the promotion by the 
coupon generator associated. We can set the usage limmit, the 
number of times it may be used and so on.

    $storeId = Mage::app()->getStore()->getStoreId();
    $emailTemplate = Mage::getModel('core/email_template')->loadByCode($config['emailtemplate']);
    $vars = array('custom_var1' => $codes[0], ‘custom_var’ => $codes[1]);
    
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
    
In the end, after the creation and association of the coupons is gone good, we 
procide with the templating of the email and the sending of it. After we log 
the activity and thats it! 

Hope this will be usefull and see you soon! 