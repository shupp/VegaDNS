<?php
/**
 * VegaDNS Pager
 * 
 * PHP Version 5
 * 
 * @category  DNS
 * @package   VegaDNS
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://www.vegadns.org
 */

/**
 * VegaDNS Pager
 * 
 * Simple Paging
 * 
 * PHP Version 5
 * 
 * @category  DNS
 * @package   VegaDNS
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://www.vegadns.org
 */
class VegaDNS_Pager
{
    /**
     * paginate 
     * 
     * Simple paginate method
     * 
     * @param mixed $module Framework_Module instance
     * @param mixed $total  total number of items
     * 
     * @static
     * @access public
     * @return void
     */
    static public function paginate(Framework_Module $module, $total)
    {
        $module->setData('total', $total);
        $module->setData('limit', (integer)Framework::$site->config->maxPerPage);
        if (isset($_REQUEST['start']) 
            && !preg_match('/[^0-9]/', $_REQUEST['start'])) {
            $start = $_REQUEST['start'];
        }
        if (!isset($start)) {
            $start = 0;
        }
        $module->setData('start', $start);
        $module->setData('currentPage', 
            ceil($module->start / $module->limit));
        $module->setData('totalPages', 
            ceil($module->total / $module->limit));
    }
}
?>
