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