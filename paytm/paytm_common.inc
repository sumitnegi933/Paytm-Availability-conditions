<?php
/**
 * payfast_common.inc
 *
 * Copyright (c) 2009-2012 PayFast (Pty) Ltd
 * 
 * LICENSE:
 * 
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 * 
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 * 
 * @author     Sumit Negi
 * @copyright  2020 Sumit Negi - based on code by others
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 */

function paytm_pfGetData()
{
    // Posted variables from ITN
    $pfData = $_POST;

    // Strip any slashes in data
    foreach( $pfData as $key => $val )
        $pfData[$key] = stripslashes( $val );

    // Return "false" if no data was received
    if( sizeof( $pfData ) == 0 )
        return( false );
    else
        return( $pfData );
}



// }}}
?>
