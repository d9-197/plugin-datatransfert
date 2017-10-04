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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/datatransfert.inc.php';

class datatransfert extends eqLogic {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */

	
	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'datatransfert_update';
		$return['progress_file'] = '/tmp/datatransfer_in_progress';
		$state = '';
		if (file_exists(dirname(__FILE__) . "/../../external/rclone/rclone")) {
			$state = 'ok';
		} else {
			$state = 'nok';
		}
		$return['state'] = $state;
		return $return;
	}

	public static function dependancy_install() {
		if (file_exists('/tmp/datatransfer_in_progress')) {
			return;
		}

		log::remove('datatransfert_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../external/rclone/download.sh';
		$cmd .= ' >> ' . log::getPathToLog('datatransfert_update') . ' 2>&1 &';
		exec($cmd);    
	}

    public static function supportedProtocol() {
        $return = array();
        foreach (ls(dirname(__FILE__) . '/../php', '*.protocol.php') as $file) {
            $protocol = explode('.', $file);
            $return[] = $protocol[0];
        }
        return $return;
    }

    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
}

class datatransfertCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */

    public static function orderFile($a, $b) {
        if ($a['datetime'] == $b['datetime']) {
            return 0;
        }
        return ($a['datetime'] < $b['datetime']) ? +1 : -1;
    }

    /*     * *********************Methode d'instance************************* */

    public function execute($_options = null) {
        $eqLogic = $this->getEqLogic();
        $protocol = $eqLogic->getConfiguration('protocol');
        include_file('core', $protocol . '.protocol', 'php', 'datatransfert');
		$class = call_user_func('DataTransfert\\' . $protocol . '::withEqLogic', $eqLogic);
        $cible = $this->getConfiguration('cible');
        $source = calculPath($this->getConfiguration('source'));
        $filelist = array();
        $filter_recentfile = $this->getConfiguration('filter_recentfile');
        if ($this->getConfiguration('filter_recentfile') != '') {
            foreach (ls($source, $this->getConfiguration('filter_file', '*')) as $file) {
                $filelist[] = array(
                    'file' => $file,
                    'datetime' => filemtime($source . '/' . $file)
                );
            }
            usort($filelist, 'datatransfertCmd::orderFile');
            $filelist = array_slice($filelist, 0, $this->getConfiguration('filter_recentfile'));
			foreach ($filelist as $file) {
			    $class->put($source . "/" . $file['file'], $cible . "/" . $file['file']);
            }
        } else {
            foreach (ls($source, $this->getConfiguration('filter_file', '*')) as $file) {
                $class->put($source . '/' . $file, $cible . '/' . $file);
            }
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>