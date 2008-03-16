<?php
/**
 * VegaDNS_Sort 
 * 
 * PHP Version 5.1+
 * 
 * @category  DNS
 * @package   VegaDNS
 * @abstract
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://vegadns.org
 */


/**
 * VegaDNS_Sort 
 * 
 * Group of sorting methods
 * 
 * @category  DNS
 * @package   VegaDNS
 * @abstract
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://vegadns.org
 */
abstract class VegaDNS_Sort
{
    /**
     * getRequestSortWay 
     * 
     * Determine requested sort way
     * 
     * @access public
     * @static
     * @return string
     */
    static public function getRequestSortWay()
    {
        if (!isset($_REQUEST['sortway'])) {
            return 'asc';
        }
        if ( $_REQUEST['sortway'] == 'desc') {
            return 'desc';
        } else {
            return 'asc';
        }
    }
        
    /**
     * getSortField 
     * 
     * Determine sort field
     * 
     * @param mixed $mode records or domains
     * 
     * @access public
     * @static
     * @return string
     */
    static public function getSortField($mode)
    {
        if ($mode == 'records') {
            $defaultField = 'type';
        } else if ($mode == 'domains') {
            $defaultField = 'status';
        }

        if (!isset($_REQUEST['sortfield'])) {
            $sortField = $defaultField;
        } else {
            $sortField = $_REQUEST['sortfield'];
        }
        return $sortField;
    }

    /**
     * getSortWay 
     * 
     * Get sort way
     * 
     * @param string $sortfield sort field
     * @param string $val       value
     * @param string $sortway   sort way
     * 
     * @access public
     * @static
     * @return string
     */
    static public function getSortWay($sortfield, $val, $sortway)
    {
        if ($sortfield == $val) {
            if ($sortway == 'asc') {
                return 'desc';
            } else {
                return 'asc';
            }
        } else {
            return 'asc';
        }
    }
}

?>
