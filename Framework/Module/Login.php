<?php 
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Login
 *
 * Provide login/logout functionality
 *
 * PHP Version 5
 *
 * @category   Net
 * @package    VegaDNS
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org>
 * @copyright  2008 Bill Shupp <hostmaster@shupp.org>
 * @license    GPL http://www.gnu.org/copyleft/gpl.html
 * @link       http://vegadns.org/
 * @filesource
 */

/**
 * Framework_Module_Login
 *
 * @category   Net
 * @package    VegaDNS
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org>
 * @copyright  2008 Bill Shupp <hostmaster@shupp.org>
 * @license    GPL http://www.gnu.org/copyleft/gpl.html
 * @link       http://vegadns.org/
 */
class Framework_Module_Login extends Framework_Auth_No
{

    /**
     * __construct 
     * 
     * Set pageTemplateFile
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTemplateFile = 'loginPage.tpl';
    }

    /**
     * __default
     *
     * Display Login.tpl
     *
     * @access      public
     * @return      mixed
     */
    public function __default()
    {
        $form = $this->_createLoginForm();
        $this->setData('QF_Form', $form->toHtml());
        $this->tplFile = 'Login.tpl';
    }

    /**
     * loginNow 
     * 
     * Try and log the user in.
     * 
     * @access public
     * @return void
     */
    public function loginNow()
    {
        $form = $this->_createLoginForm();
        if ($form->validate()) {
            $result = $this->user->authenticate($_POST['email'], $_POST['password']);
            if ($result !== true) {
                $this->setData('loginError', _("Login failed"));
                $this->setData('QF_Form', $form->toHtml());
                $this->session->__set('userID', null);
                return;
            }
            $this->session->__set('lastActionTime', time());
            header('Location: ./index.php?module=Domains');
            return;
        } else {
            $this->setData('QF_Form', $form->toHtml());
        }
    }

    /**
     * _createLoginForm 
     * 
     * Create HTML_QuickForm object for logging in
     * 
     * @access private
     * @return HTML_Quickform object
     */
    private function _createLoginForm()
    {
        $form = new HTML_QuickForm('formLogin',
            'post', $_SERVER['REQUEST_URI'] . '&event=loginNow');

        $form->addElement('header', 'MyHeader', _('Login'));
        $form->addElement('text', 'email', _('Email'));
        $form->addElement('password', 'password', _('Password'));
        $form->addElement('submit', 'submit', _('Login'));

        $form->addRule('email',
            _('Please enter your email address'), 'required', null, 'client');
        $form->addRule('email',
            _('Please enter a valid email address'), 'email', null, 'client');
        $form->addRule('password',
            _('Please enter your password'), 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    /**
     * logoutNow 
     * 
     * Log user out and run $this->__default()
     * 
     * @access public
     * @return void
     */
    public function logoutNow()
    {
        $this->session->destroy();
        $this->setData('message', _('Logged out successfully'));
        return $this->__default();
    }

    /**
     * logoutInactive 
     * 
     * Log out user for inactivity
     * 
     * @access public
     * @return $this->__default()
     */
    public function logoutInactive()
    {
        $this->session->destroy();
        $this->setData('message',
            _('You have been logged out automatically for inactivity'));
        return $this->__default();
    }

}

?>
