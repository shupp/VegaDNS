<?php

abstract class VegaDNS_Common extends Framework_Auth_User
{

    public function getRequestSortWay()
    {
        if (!isset($_REQUEST['sortway'])) {
            $sortway = "asc";
        } else if ( $_REQUEST['sortway'] == 'desc') {
            $sortway = 'desc';
        } else {
            $sortway = 'asc';
        }
        return $sortway;
    }
        
    function getSortField($mode)
    {
        if($mode == 'records') {
            $default_field = 'type';
        } else if($mode == 'domains') {
            $default_field = 'status';
        }

        if (!isset($_REQUEST['sortfield'])) {
            $sortfield = $default_field;
        } else {
            $sortfield = $_REQUEST['sortfield'];
        }

        return $sortfield;
    }

    public function getSortWay($sortfield, $val, $sortway)
    {
        if($sortfield == $val) {
            if($sortway == 'asc') {
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
