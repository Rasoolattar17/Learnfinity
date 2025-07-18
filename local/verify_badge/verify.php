<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Handles AJAX verification of Google reCAPTCHA token for badge verification.
 *
 * @package   local_verify_badge
 * @author    Rasool
 * @copyright 2024, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

defined('MOODLE_INTERNAL') || die();

$recaptchasecretkey = get_config('moodle', 'recaptchaprivatekey');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secret = $recaptchasecretkey;
    $token = $_POST['token'];
    $verifyurl = "https://www.google.com/recaptcha/api/siteverify";
    $response = file_get_contents($verifyurl . "?secret=" . $secret . "&response=" . $token);
    $responsekeys = json_decode($response, true);
    echo json_encode(['success' => $responsekeys['success']]);
}
