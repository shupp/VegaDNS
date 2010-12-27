<?php
/**
 * Deneb_Exception_NotFound
 * 
 * @uses      Exception
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2010 Empower Campaigns
 * @link      http://github.com/empower/deneb
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */

/**
 * Required file 
 */
require_once 'Deneb/Exception.php';

/**
 * Used when a requested object or collection is not found in the data store
 * 
 * @uses      Deneb_Exception
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2010 Empower Campaigns
 * @link      http://github.com/empower/deneb
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */
class Deneb_Exception_NotFound extends Deneb_Exception
{
}
