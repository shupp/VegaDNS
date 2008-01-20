<?php

/**
 * Framework_Module_Domains_Add 
 * 
 * @uses        Framework_Module_Domains
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Module_Domains_Add 
 * 
 * Add Domains
 * 
 * @uses        Framework_Module_Domains
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Domains_Add extends VegaDNS_Auth_ACL
{
    /**
     * __default()
     * 
     * Display add form
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        $form = $this->addForm();
        $this->setData('form', $form->toHtml());
        // $this->pageTemplateFile = 'thickbox.tpl';
        $this->tplFile = 'add.tpl';
    }

    /**
     * addNow 
     * 
     * Actually add the domain
     * 
     * @access public
     * @return void
     */
    public function addNow()
    {
        $form = $this->addForm();
        if (!$form->validate()) {
            return $this->__default();
        }
    
        $domain = strtolower($_REQUEST['domain']);
    
        // Make sure the domain does not already exist.
        if ($this->vdns->domainExists($domain)) {
            $this->setData('message', "Error: domain $domain already exists");
            return $this->__default();
        }

        $domain_status = $this->user->isSeniorAdmin() ? 'active' : 'inactive';
        $domain_id = $this->vdns->addDomainRecord($domain, $domain_status);
        $this->vdns->addDefaultRecords($domain, $domain_id);
    
        // email the support address if an inactive domain is added
        $body = "$domain_status domain \"$domain\" added by {$this->session->email}\n\n";
        $body .= "\n\nThanks,\n\n";
        $body .= "VegaDNS";
    
        $supportemail = (string)Framework::$site->config->supportEmail;
        $supportname = (string)Framework::$site->config->supportName;
        mail($supportemail,
            "New $domain_status Domain Created",
            $body,
            "Return-path: $supportemail\r\nFrom: \"$supportname\" <$supportemail>");
    
        $this->setData('message', "Domain $domain added successfully!");
        // $this->setData('continueUrl', "./?module=Records&domain=".urlencode($domain));
        // $this->pageTemplateFile = 'thickbox.tpl';
        // $this->tplFile = 'addSuccess.tpl';
        header("Location: ./?module=Records&domain_id=$domain_id");
        return;
    }

    /**
     * addForm 
     * 
     * build add domain form
     * 
     * @access protected
     * @return void
     */
    protected function addForm()
    {
        // $form = new HTML_QuickForm('formLogin', 'post', './?module=Domains&event=addNow&modal=true', '', 'class="thickbox"');
        $form = new HTML_QuickForm('formLogin', 'post', './?module=Domains&class=add&event=addNow');

        $form->addElement('header', 'MyHeader', _('Add Domain'));
        $form->addElement('text', 'domain', _('Domain Name'));
        $form->addElement('submit', 'submit', _('Add'));

        $form->registerRule('secondLevel', 'regex', '/.*\..*/');
        $form->registerRule('validChars', 'regex', '/^[\.a-z0-9-]+$/i');
        $form->addRule('domain', _('Please enter a domain name'), 'required', null, 'client');
        $form->addRule('domain', _('Domain must be at least a second level domain'), 'secondLevel', null, 'client');
        $form->addRule('domain', _('Invalid characters in domain name'), 'validChars', null, 'client');

        $form->applyFilter('domain', 'strtolower');

        return $form;
    }

}
?>
