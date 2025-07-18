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
 * Badge verification and display page for the Verify Badge local plugin.
 *
 * @package   local_verify_badge
 * @author    Rasool
 * @copyright 2024, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/composer/jwtsecure.php');
require_once($CFG->libdir . '/succeed_date_lib.php');
require_once($CFG->libdir . '/succeedlib.php');

$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);
$parts = explode('/', $path);
$value = end($parts);
$certificateid = $_SESSION['certificateid'] ? $_SESSION['certificateid'] : $value;

// Start session explicitly if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if already verified
$verified = isset($_SESSION['recaptcha_verified']) && $_SESSION['recaptcha_verified'] === true;

logUserVisit($url);

global $DB, $CFG, $OUTPUT;
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/verify_badge/style/verify_badge.css'));

$courseid = $DB->get_field('course_completed_certificate', 'courseid', ['certificateid' => $certificateid]);
$badgedetails = $DB->get_record('local_verify_badge_details', ['course_id' => $courseid]);

$image = $DB->get_field('blocks_social_share_addposts', 'post_image', ['course_id' => $courseid]);
$badgevalidaty = $DB->get_record('blocks_social_share_addposts', ['course_id' => $courseid]);

$date = $DB->get_field('course_completed_certificate', 'createdon', ['certificateid' => $certificateid]);

$dataformat = 'F j, Y';

$issuedon = succeed_date_format($date, $dataformat, $badgevalidaty->created_by);

if ($badgevalidaty->no_expiry == 1) {
    $validtill = null;
} else if ($badgevalidaty->enable_fixeddate == 1) {
    $validtill  = succeed_date_format($badgevalidaty->fixed_date, $dataformat, $badgevalidaty->created_by);
} else if ($badgevalidaty->enable_relativedate == 1) {
    if ($badgevalidaty->relative_d_mode == 1) {
        $validtill = succeed_date_format(
            strtotime('+' . $badgevalidaty->relative_d_count . ' days', $date),
            $dataformat,
            $badgevalidaty->created_by
        );
    } else if ($badgevalidaty->relative_d_mode == 2) {
        $validtill = succeed_date_format(
            strtotime('+' . $badgevalidaty->relative_d_count . ' weeks', $date),
            $dataformat,
            $badgevalidaty->created_by
        );
    } else if ($badgevalidaty->relative_d_mode == 3) {
        $validtill = succeed_date_format(
            strtotime('+' . $badgevalidaty->relative_d_count . ' months', $date),
            $dataformat,
            $badgevalidaty->created_by
        );
    }
}

if ($image) {
    $postimage = $CFG->wwwroot . '/secure.php?file=stsocialshare/' . $image;
} else {
    $postimage = $CFG->wwwroot . '/local/verify_badge/pix/Default-Badge-Succeed.png';
}


if ($badgedetails->badge_image) {
    $badgeimage = $CFG->wwwroot . '/secure.php?file=verifybadge/' . $badgedetails->badge_image;
} else {
    $badgeimage = $CFG->wwwroot . '/local/verify_badge/pix/Default-Badge-Succeed.png';
}

$userid = $DB->get_field('course_completed_certificate', 'userid', ['certificateid' => $certificateid]);

$sql = "SELECT CONCAT(u.firstname, ' ', u.lastname) AS username FROM {user} u WHERE u.id = :userid";
$username = $DB->get_field_sql($sql, ['userid' => $userid]);

$companyid = $DB->get_field('company_users', 'companyid', ['userid' => $userid]);
$orgidrec = elearnposh::getorgdetails($companyid);
$logo = $CFG->wwwroot . '/secure.php?file=logo/' . $orgidrec->logo;


$tags = explode(',', $badgedetails->tags);
$skills = explode(',', $badgedetails->skills);

$component = 'local_verify_badge';
$badgeurl = $CFG->wwwroot . '/local/verify_badge/verify_badge.php?component=' . $component . '&certificateid=' . $_SESSION['certificateid'];

$title = $badgedetails->title;
$badgetext = $badgedetails->badge_text;
$badgelink = $badgedetails->badge_link;
$description = $badgedetails->description;
$issueorg = $badgedetails->issuing_organization;
$orglink = $badgedetails->org_link;
$extracontent = $badgedetails->extra_content;
$recaptcha_site_key = get_config('moodle', 'recaptchapublickey');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Twitter Card meta tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?php echo $badgeurl; ?>">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="<?php echo $description; ?>">
    <meta name="twitter:image" content="<?php echo $postimage; ?>">
    <meta name="twitter:image:alt" content="Alternative text for the image">
    <!-- Open Graph meta tags (optional) -->
    <meta property="og:title" content="<?php echo  $title; ?>">
    <meta property="og:description" content="<?php echo $description; ?>">
    <meta property="og:image" content="<?php echo $postimage; ?>">
    <meta name="og:site" content="<?php echo $badgeurl; ?>">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N"
        crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct"
        crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="<?php echo $postimage; ?>" type="image/png">
    <title>Verify Badge</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <!-- Other meta tags -->
    <!-- Include any other meta tags specific to your content -->
</head>
<?php if ($badgedetails) { ?>

    <body>
        <nav class="navbar navbar-expand-md navbar-light bg-light">
            <div class="mt-2"> <!-- Added container for better alignment -->
                <a class="navbar-brand" href="#">
                    <img src="<?php echo $logo; ?>" alt="Site Logo" class="d-inline-block align-top" style="height: 52px;object-fit: contain;max-width: 100%;">
                </a>
            </div>
        </nav>

        <div class="local_verify_badge_verify_badge" id="local_verify_badge_verify_badge">
            <div class="row justify-content-center mt-2 mb-3">
                <div class="col-12 text-center bg-light pt-2 rounded">
                    <!-- Flex container to ensure elements align in a row for desktop and column for mobile -->
                    <div class="d-flex align-items-center justify-content-start flex-wrap ms-5">
                        <!-- Icon (left aligned in desktop, centered in mobile) -->
                        <div class="me-3 mb-3 mb-sm-0">
                            <i class="fa fa-user-circle user-icon" style="font-size: 30px" aria-hidden="true"></i>
                        </div>

                        <!-- Text content (aligns properly on larger screens, stacks vertically on mobile) -->
                        <div class="d-flex flex-column flex-sm-row align-items-center">
                            <p class="lead mb-0">This badge was issued to</p>
                            <p class="font-weight-bold ms-2 text-info lead my-0">
                                <?php echo $username; ?>
                            </p>
                            &nbsp; &nbsp;
                            <p class="lead mb-0">on <?php echo $issuedon; ?>.</p>

                            <?php
                            if ($badgevalidaty->no_expiry != 1) {
                                if (strtotime($validtill) < strtotime(succeed_date_format(time(), $dataformat))) {
                            ?>
                                    &nbsp; &nbsp;
                                    <p class="lead valid-until text-danger font-weight-bold">Expired</p>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-5 col-md-5 text-center">
                        <div class="badge">
                            <img src="<?php echo $badgeimage; ?>" alt="" class="fixed-image">
                        </div>
                        <div class="text-center badge-text">
                            <button class="btn secondary btn-md" id="verify_badge">
                                <a href="<?php echo $badgelink; ?>" class="badgelin" target="_blank">
                                    <?php echo $badgetext; ?>
                                </a>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-sm-7 col-md-7">
                        <div class="title">
                            <h1><?php echo $title; ?></h1>
                        </div>
                        <div class="issueorg mt-3">
                            <p>Issued by <a href="<?php echo $orglink; ?>" target="_blank"> <?php echo $issueorg; ?>
                                </a>
                            </p>
                        </div>
                        <div class="description">
                            <p><?php echo $description; ?></p>
                        </div><br>
                        <div class="tags">
                            <?php foreach ($tags as $tag) : ?>
                                <span class="btn btn-md me-2 mb-2 d-inline-block"><?php echo $tag; ?></span>
                            <?php endforeach; ?>
                        </div><br>

                        <div class="skills">
                            <h3 class="my-4">Skills</h3>
                            <?php foreach ($skills as $skill) : ?>
                                <span class="btn btn-md me-2 mb-2 d-inline-block"><?php echo $skill; ?></span>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php
            if (trim(strip_tags($extracontent))) { ?>
                <div class="my-4">
                    <hr class="hr">
                </div>
            <?php } ?>
            <div class="container">
                <div class="row">
                    <div class="col-1"></div>
                    <div class="col-10">
                        <div class="extra-content">
                            <p><?php echo $extracontent; ?></p>
                        </div><br>
                    </div>
                    <div class="col-1"></div>
                </div>
            </div>
            <button id="recaptcha-button" class="g-recaptcha" 
                data-sitekey="<?php echo $recaptcha_site_key; ?>" 
                data-callback="onRecaptchaSuccess">
            </button>
            <!-- <p id="recaptcha-status" class="status">Processing...</p> -->
        </div>
    </body>
    
    <script>
        function onRecaptchaSuccess(token) {
            console.log("Generated Token:", token);  // Debugging log
            // document.getElementById('recaptcha-status').innerText = 'Verification in progress...';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo $CFG->wwwroot; ?>/local/verify_badge/verify.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log("Server Response:", xhr.responseText);
                    if (xhr.status === 200) {
                        try {
                            let response = JSON.parse(xhr.responseText); // Parse JSON response
                            if (response.success) {
                                // document.getElementById('recaptcha-status').innerText = 'Verification successful!';
                            } else {
                                // document.getElementById('recaptcha-status').innerText = 'Verification failed. Please try again.';
                            }
                        } catch (error) {
                            console.error("JSON Parse Error:", error);
                            // document.getElementById('recaptcha-status').innerText = 'Verification error. Please try again.';
                        }
                    } else {
                        console.error("AJAX Error: " + xhr.status, xhr.responseText);
                    }
                }
            };
            xhr.send('token=' + encodeURIComponent(token));
        }

        window.onload = function() {
            if (!sessionStorage.getItem("recaptcha_verified")) { // Run only if not already verified
                setTimeout(function() {
                    document.getElementById("recaptcha-button").click();
                }, 1000); // Delay to ensure reCAPTCHA script loads
            } else {
                document.getElementById('recaptcha-status').innerText = 'Already verified!';
            }
        };
    </script>

</html>
<style>
    body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    ::-webkit-scrollbar {
        display: none;
    }

    .user-icon {
        font-size: 20px;
    }

    #verify_badge {
        background-color: #004438;
    }

    #verify_badge a {
        color: #fff;
    }

    .title h1 {
        font-size: 32px !important;
    }

    .issueorg a {
        color: #0056b3;
        text-decoration: underline;
    }

    .issueorg p {
        font-size: 15px;
    }

    .description p {
        font-size: 18px;
    }

    .tags button {
        background-color: #efefef;
        color: #000;
    }

    .lead {
        font-size: 1rem !important;
    }

    .text-info {
        font-size: 1em !important;
        margin-top: 1px !important;
    }

    .badgelin {
        text-decoration: none !important;
    }

    .navbar {
        box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 6px, rgba(0, 0, 0, 0.23) 0px 3px 6px;
        height: 60px;
    }

    .hr {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .fixed-image {
        width: 100%;
        height: auto;
        max-width: 300px;
        max-height: 300px;
    }

    span {
        border: 1px solid black !important;
    }

    /* Responsive Design for Mobile View */
    @media (max-width: 768px) {
        .navbar {
            height: auto;
        }

        .user-icon {
            font-size: 16px;
        }

        .lead {
            font-size: 0.9rem !important;
        }

        .text-info {
            font-size: 0.9em !important;
        }

        .badge {
            margin: 0 auto;
            text-align: center;
        }

        .title h1 {
            font-size: 24px !important;
            text-align: center;
        }

        .issueorg p,
        .description p {
            font-size: 14px;
            text-align: center;
        }

        .tags button,
        .skills button {
            width: 100%;
            margin-bottom: 5px;
        }

        .fixed-image {
            max-width: 200px;
            max-height: 200px;
        }

        .local_verify_badge_verify_badge {
            padding: 10px;
        }

        .d-flex {
            flex-direction: column;
            align-items: center;
        }

        .ml-2,
        .ml-5 {
            margin-left: 0 !important;
        }

        .badge-text {
            margin-top: 15px;
        }
    }

    .user-icon {
        font-size: 30px;
        /* Default font size for desktop */
    }

    /* Mobile view adjustments */
    @media (max-width: 768px) {
        .user-icon {
            font-size: 40px;
            /* Increase font size for mobile screens */
        }

        .row {
            flex-wrap: wrap;
            /* Allow content to wrap in mobile view */
        }

        .col-12 {
            padding-left: 0 !important;
            padding-right: 0 !important;
            /* Remove unnecessary padding */
        }

        .col-md-12 {
            text-align: center;
            /* Center-align text for mobile */
        }

        .col-auto {
            margin-bottom: 10px;
            /* Add some space between icon and text on mobile */
        }

        .text-center {
            text-align: center !important;
            /* Ensure text is centered on mobile */
        }

        .lead {
            font-size: 0.9rem;
            /* Adjust font size for mobile */
        }

        .bg-light {
            padding: 10px;
            /* Add padding to background for mobile screens */
        }

        /* Stack the text and icon vertically on mobile */
        .row.align-items-center {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .font-weight-bold {
            font-size: 1.1rem;
            /* Adjust font size for bold text */
        }
    }

    /* Mobile-specific styles */
    @media (max-width: 768px) {

        /* Adjust image size within the extra-content class */
        .extra-content img {
            max-width: 100%;
            /* Make the image responsive */
            height: auto;
            /* Maintain aspect ratio */
            max-height: 200px;
            /* Set a maximum height if needed */
        }
    }

    /* Mobile-specific adjustments */
    @media (max-width: 768px) {
        .user-icon {
            font-size: 24px;
            /* Reduce icon size on mobile */
        }

        .lead {
            font-size: 0.9rem;
            /* Adjust font size for better readability on mobile */
        }
    }
</style>



    <?php
} else {
    succeed_alert(['message' => get_string('invalid_varification', 'local_verify_badge')]);
}

// Function to log user visit
function loguservisit($page) {

    global $DB;
    // Get user's IP address
    $ip = $_SERVER['REMOTE_ADDR'];

    // Get user's browser information
    $browser = $_SERVER['HTTP_USER_AGENT'];

    // Get current timestamp
    $timestamp = time();

    $userlogs = new stdClass();
    $userlogs->page_url = $page;
    $userlogs->ip_address = $ip;
    $userlogs->browser = $browser;
    $userlogs->visited_on = $timestamp;
    $userlogs->session_id = session_id() ? session_id() : 0;

    $DB->insert_record('local_verifybadge_uservisit', $userlogs);
}
