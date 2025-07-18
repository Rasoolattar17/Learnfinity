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
 * reCAPTCHA verification page for the Verify Badge local plugin.
 *
 * @package   local_verify_badge
 * @author    Rasool
 * @copyright 2024, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

defined('MOODLE_INTERNAL') || die();

$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_verify_badge'));
$PAGE->set_heading(get_string('pluginname', 'local_verify_badge'));

echo $OUTPUT->header();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invisible reCAPTCHA Verification</title>
    <script src="https://www.google.com/recaptcha/api.js?render=YOUR_PUBLIC_KEY"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        .status {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Invisible reCAPTCHA Verification</h2>
    <p>Verifying reCAPTCHA on page load...</p>
    <div id="status" class="status">Processing...</div>

    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6LfkOQArAAAAAKgMDfo8fFQrEMIgbJMZGiuoOReB', { action: 'verify' }).then(function(token) {
                fetch('verify.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'token=' + token
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('status').innerText = data.success ? "Verified Successfully!" : "Verification Failed!";
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>

<?php
echo $OUTPUT->footer();
