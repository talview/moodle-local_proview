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
 * Proview
 *
 * This module provides support for remote proctoring quizzes and assessments using Proview
 *
 * @package    local_proview
 * @copyright  Talview, 2020
 * @author     Mani Ka <mani@talview.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require_login();

// Include config.php.
// @codingStandardsIgnoreStart
// Let codechecker ignore the next line because otherwise it would complain about a missing login check
// after requiring config.php which is really not needed.
require_once('../../config.php');
// @codingStandardsIgnoreEnd

// Include lib.php.
//require_once(__DIR__ . '/lib.php');

// Globals.
global $PAGE;

$PAGE->set_context(context_system::instance());

$PAGE->set_pagelayout('embedded');
$PAGE->set_heading("Proview Quiz");
$PAGE->set_url('/local/proview/frame.php');

echo $OUTPUT->header();

?>

<iframe id="contentIFrame" title="Iframe application" style="width: 98vw; height:95vh; border: 0px;">
  <p>Your browser does not support iframes.</p>
</iframe>
<script>
   var childOrigin = '*';
    // Defining function for event handling on postMessage from any window
    function receiveMessage(event) {
    //  if (event.origin == childOrigin) {
        if(event.data.type == 'startProview') {
            startProview(...event.data.args);
        }
        if(event.data.type == 'stopProview') {
            stopProview(event.data.url);
        }
    }

    window.addEventListener("message", receiveMessage, false);
    window.addEventListener('error', function(e) {
      console.error(e);
    }, false);


    //Javascript function to start proview invoked upon postMessage from iframe
    function startProview(authToken, session, proview_url, clear, skipHardwareTest, previewStyle) {
      let url = proview_url || '//cdn.proview.io/init.js';
      document.getElementById('contentIFrame').src = window.iframeUrl;
      //script to load the proview STARTS
      (function(i,s,o,g,r,a,m){i['TalviewProctor']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script',url,'tv');
        tv('init', authToken,{
            session: session,
            clear: clear || true,
            skipHardwareTest: skipHardwareTest || false,
            previewStyle: previewStyle || 'position: fixed; bottom: 0px;',
        initCallback: onProviewStart
      });
    }

    function onProviewStart(err, id) {
      window.ProviewStatus = 'start';
      let iframeWindow = document.getElementById('contentIFrame').contentWindow;

      //Lock quiz logic STARTS
      const button = iframeWindow.document.getElementById('id_quizpassword');
      if( button ) { //checking if the password is enabled for the quiz or not
        button.value = window.quizPassword; //fetching the password value from the window object
        iframeWindow.document.getElementById('mod_quiz_preflight_form').submit(); //submitting the password form
      }
      const id_submitbutton = iframeWindow.document.getElementById('id_submitbutton');
      if( id_submitbutton ) { //handle if the test is timed quiz
        id_submitbutton.click();//submitting the form
      }
      //Lock quiz logic ENDS
      iframeWindow.postMessage({
        type: 'startedProview',
        args: [
          err,   //Error on proview stating, if any
          id     // Playback ID
        ]
      }, childOrigin);
    }

    function stopProview(url) {
      //Post message to application loaded into application on recording stop
      if ( window.ProviewStatus && window.ProviewStatus == 'start') {
        ProctorClient3.stop(function() {
            window.ProviewStatus = 'stop';
            document.getElementById('contentIFrame').contentWindow.postMessage({
                type: 'stoppedProview',
                url: url
            }, childOrigin);
            window.location.href = url;
        });
      } else {
          document.getElementById('contentIFrame').contentWindow.postMessage({
              type: 'stoppedProview',
              url: url
          }, childOrigin);
          window.location.href = url;
      }
    }
    (function() {
      const urlParams = new URLSearchParams(window.location.search);
      window.iframeUrl = urlParams.get('url');
      window.quizPassword = urlParams.get('quizPassword'); //setting quiz password
      startProview(urlParams.get('token'),urlParams.get('profile'),urlParams.get('proview_url'))
    })();
</script>


<?php echo $OUTPUT->footer();