<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace DataTransfert;

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/datatransfert.inc.php';

class webdav extends DataTransfert {
  function __construct($_baseUri, $_username, $_password, $_disableSslVerification) {
    $this->baseUri = $_baseUri;
    $this->username = $_username;
    $this->password = $_password;
    $this->disableSslVerification = $_disableSslVerification;
  }

  static function withEqLogic($_eqLogic) {
    return new self($_eqLogic->getConfiguration('baseUri'),
	                $_eqLogic->getConfiguration('userName'),
					$_eqLogic->getConfiguration('password'),
					($_eqLogic->getConfiguration('disableSslVerification') == 1) ? true : false);
  }
  
  function put($_source, $_cible) {
    \log::add('datatransfert', 'debug', "uploading " . $_source . " to " . $_cible . " with " . $this->baseUri);
    $settings = array(
		'baseUri' => $this->baseUri,
		'userName' => $this->username,
		'password' => $this->password
	);
	$client = new \Sabre\DAV\Client($settings);
	if ($this->disableSslVerification) {
		$client->addCurlSetting(CURLOPT_SSL_VERIFYPEER, false);
		$client->addCurlSetting(CURLOPT_SSL_VERIFYHOST, false);
	}
	$adapter = new \League\Flysystem\WebDAV\WebDAVAdapter($client, dirname($_cible));
	$flysystem = new \League\Flysystem\Filesystem($adapter);
	$flysystem->put(basename($_cible), fopen($_source, 'r'));
  }
}