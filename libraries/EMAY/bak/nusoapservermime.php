<?php
namespace MLIB\EMAY;
/*
$Id: nusoapmime.php,v 1.13 2010/04/26 20:15:08 snichol Exp $

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

The NuSOAP project home is:
http://sourceforge.net/projects/nusoap/

The primary support for NuSOAP is the mailing list:
nusoap-general@lists.sourceforge.net

If you have any questions or comments, please email:

Dietrich Ayala
dietrich@ganx4.com
http://dietrich.ganx4.com/nusoap

NuSphere Corporation
http://www.nusphere.com

*/

/*require_once('nusoap.php');*/
/* PEAR Mail_MIME library */
require_once('Mail/mimeDecode.php');
require_once('Mail/mimePart.php');
/*
 *	For backwards compatiblity, define soapclientmime unless the PHP SOAP extension is loaded.
 */
if (!extension_loaded('soap')) {
	class soapclientmime extends \MLIB\EMAY\nusoap_client_mime {
	}
}
/*
 *	For backwards compatiblity
 */
class nusoapservermime extends \MLIB\EMAY\nusoap_server_mime {
}

?>
