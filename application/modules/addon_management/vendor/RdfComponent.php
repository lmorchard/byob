<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is addons.mozilla.org site.
 *
 * The Initial Developer of the Original Code is
 * Justin Scott <fligtar@gmail.com>.
 * Portions created by the Initial Developer are Copyright (C) 2006
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 or later (the "GPL"), or
 * the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the MPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the MPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK ***** */

//require_once('includes/rdf_parser.php');
define('EM_NS', 'http://www.mozilla.org/2004/em-rdf#');
define('MF_RES', 'urn:mozilla:install-manifest');

class RdfComponent /*extends Object*/ {
    /**
     * Parses install.rdf using Rdf_parser class
     * @param string $manifestData
     * @return array $data["manifest"]
     */
    function parseInstallManifest($manifestData) {
        $data = array();

        $rdf = new Rdf_parser();
        $rdf->rdf_parser_create(null);
        $rdf->rdf_set_user_data($data);
        // $rdf->rdf_set_statement_handler(array('RdfComponent', 'mfStatementHandler'));
        $rdf->rdf_set_statement_handler(array($this, 'mfStatementHandler'));
        $rdf->rdf_set_base('');

        if (!$rdf->rdf_parse($manifestData, strlen($manifestData), true)) {
            return xml_error_string(xml_get_error_code($rdf->rdf_parser['xml_parser']));
        }

        // Set the targetApplication data
        $targetArray = array();
        if (!empty($data['manifest']['targetApplication']) && is_array($data['manifest']['targetApplication'])) {
            foreach ($data['manifest']['targetApplication'] as $targetApp) {
                $id = $data[$targetApp][EM_NS."id"];
                $targetArray[$id]['minVersion'] = $data[$targetApp][EM_NS.'minVersion'];
                $targetArray[$id]['maxVersion'] = $data[$targetApp][EM_NS.'maxVersion'];
            }
        }

        $data['manifest']['targetApplication'] = $targetArray;

        $rdf->rdf_parser_free();

        return $data['manifest'];
    }

    /**
     * Parses install.rdf for our desired properties
     * @param array &$data
     * @param string $subjectType
     * @param string $subject
     * @param string $predicate
     * @param int $ordinal
     * @param string $objectType
     * @param string $object
     * @param string $xmlLang
     */
    function mfStatementHandler(&$data, $subjectType, $subject, $predicate,
                                $ordinal, $objectType, $object, $xmlLang) {
        //single properties - ignoring: iconURL, optionsURL, aboutURL, and anything not listed
        $singleProps = array('id' => 1, 'type' => 1, 'version' => 1, 'creator' => 1, 'homepageURL' => 1, 'updateURL' => 1, 'updateKey' => 1);
        //multiple properties - ignoring: File
        $multiProps = array('contributor' => 1, 'targetApplication' => 1, 'requires' => 1);
        //localizable properties
        $l10nProps = array('name' => 1, 'description' => 1);

        //Look for properties on the install manifest itself
        if ($subject == MF_RES) {
            //we're only really interested in EM properties
            $length = strlen(EM_NS);
            if (strncmp($predicate, EM_NS, $length) == 0) {
                $prop = substr($predicate, $length, strlen($predicate)-$length);

                if (array_key_exists($prop, $singleProps) ) {
                    if (isset($data['manifest'][$prop])) {
                        $data['manifest']['errors'][] = sprintf(___('RDF Parser error: the file contained a duplicate element: %s'), $prop);
                    } else {
                        $data['manifest'][$prop] = $object;
                    }
                }
                elseif (array_key_exists($prop, $multiProps)) {
                    $data['manifest'][$prop][] = $object;
                }
                elseif (array_key_exists($prop, $l10nProps)) {
                    $lang = ($xmlLang) ? $xmlLang : 'en-US';
                    $data['manifest'][$prop][$lang] = $object;
                }
            }
        }
        else {
            //save it anyway
            $data[$subject][$predicate] = $object;
        }
        return $data;
    }
}
?>
