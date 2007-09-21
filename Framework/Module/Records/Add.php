<?php


/**
 * Framework_Module_Records_Add 
 * 
 * @uses        Framework_Module_Records
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Module_Records_Add 
 * 
 * Add Records
 * 
 * @uses        Framework_Module_Records
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Records_Add extends Framework_Module_Records
{

    /**
     * form 
     * 
     * HTML_QuickForm object
     * 
     * @var mixed
     * @access private
     */
    private $form = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->user->getBit($this->user->getPerms(), 'record_create')) {
            $this->setData('message', "Error: you do not have enough privileges to create records.");
            return;
        }
        $this->form = $this->addForm();
    }

    /**
     * __default 
     * 
     * run add()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->add();
        $tplFile = 'add.tpl';
    }

    /**
     * add 
     * 
     * Display add form
     * 
     * @access public
     * @return void
     */
    public function add()
    {
        if (!is_null($this->message)) {
            return $this->listRecords();
        }
        $this->setData('form', $this->form->toHtml());
        $this->tplFile = 'add.tpl';
    }

    /**
     * addNow 
     * 
     * Actually add the record
     * 
     * @access public
     * @return void
     */
    public function addNow()
    {
        if (!is_null($this->message)) {
            return $this->listRecords();
        }

        if (!$this->form->validate()) {
            return $this->add();
        }
        var_dump($this->vdns->addRecord($this->domInfo['domain_id'], $this->form->exportValues()));
        exit;

        $this->setData('message', "Record added successfully!");
        return $this->listRecords();
    }

    /**
     * addForm 
     * 
     * build add record form
     * 
     * @access protected
     * @return void
     */
    protected function addForm()
    {
        // Pull SOA out of types array
        $types = array();
        foreach ($this->vdns->types as $key => $val) {
            if ($val == 'SOA') {
                continue;
            }
            $types[$key] = $val;
        }

        $form = new HTML_QuickForm('formLogin', 'post', './?module=Records&class=add&event=addNow&domain_id=' . $this->domInfo['domain_id']);

        $form->setConstants(array('domain_id' => $this->domInfo['domain_id']));

        $form->addElement('header', 'MyHeader', _('Add Resource Record'), array('align' => "middle"));

        $form->addElement('text', 'hostname', _('Record Name'));
        $form->addElement('select', 'type', _('Record Type'), $types);
        $form->addElement('text', 'address', _('Record Value'));
        $form->addElement('text', 'distance', _('Distance (MX and SRV only)'));
        $form->addElement('text', 'weight', _('Weight (SRV only)'));
        $form->addElement('text', 'port', _('Port (SRV only)'));
        $form->addElement('text', 'ttl', _('TTL'));
        $form->addElement('submit', 'submit', _('Add'));


        $form->registerRule('testRule', 'callback', 'testRule', $this);
        $form->addRule(array('type', 'hostname', 'address'), _('Problems'), 'testRule');

        $form->registerRule('secondLevel', 'regex', '/.*\..*/');
        $form->registerRule('validChars', 'regex', '/^[\.a-z0-9-]+$/i');
        $form->addRule('hostname', _('Please enter a record name'), 'required', null, 'client');
        $form->addRule('address', _('Please enter a record value'), 'required', null, 'client');
        $form->applyFilter('hostname', 'strtolower');

        return $form;
    }

    static public function testRule()
    {
        // print_r(func_get_args());
        // exit;
        return true;
    }

}
?>
