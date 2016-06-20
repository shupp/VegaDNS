<?php
/*
 * This file is part of Linfo (c) 2010 Joseph Gillotti.
 * 
 * Linfo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Linfo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Linfo.  If not, see <http://www.gnu.org/licenses/>.
 * 
*/
//$_POST['CPU']=1;
//$_POST['RAM']=1;

include_once 'functions.misc.php';
function determineOS() {


    list($os) = explode('_', PHP_OS, 2);

    // This magical constant knows all
    switch ($os) {

        // These are supported
        case 'Linux':
        case 'FreeBSD':
        case 'DragonFly':
        case 'OpenBSD':
        case 'NetBSD':
        case 'Minix':
        case 'Darwin':
        case 'SunOS':
            return PHP_OS;
        break;
        case 'WINNT':
            @define('IS_WINDOWS', true);
            return 'Windows';
        break;
        case 'CYGWIN':
            @define('IS_CYGWIN', true);
            return 'CYGWIN';
        break;

        // So anything else isn't
        default:
            return false;    
        break;
    }
}
function getInfoClass()
{
  $os = determineOS();
$class = 'OS_'.$os;
    
        $file = 'class.'.$class.'.php';
        return  new $class();  
}
 function getLoadMy()
 {
$os = determineOS();
$class = 'OS_'.$os;
    
        $file = 'class.'.$class.'.php';
        require $file;
        $info =  new $class();
    return $info->getLoad();
 }

  include('class.LinfoTimerStart.php');
  include('class.LinfoTimer.php');
  
 if(isset($_POST['CPU']))
 {
	echo getLoadMy();
 } 