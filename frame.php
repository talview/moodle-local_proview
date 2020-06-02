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
   var childOrigin = '*'; // can be location origin, replace with you domain
    // Javascript self invoking function
    // Take the query parameters from url and pass it to iframe as source
    /* Explanation:
      if parent URL is loaded as ?url=https://www.example.com/test/1
      below defined function extact testurl(params) value and set it as source for iframe.
     */

    // Defining function for event handling on postmessage from any window
    function receiveMessage(event) {
    //  if (event.origin == childOrigin) {
        if(event.data.type == 'startProview') {
          startProview(...event.data.args);
        }

        if(event.data.type =='stopProview') {
          stopProview(event.data.url)
        }
    //  }
    }

    // Javascript event listener for window post message API (Ref: https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage)
    window.addEventListener("message", receiveMessage, false);


    //Javascript function to start proview invoked upon postmessage from iframe
    function startProview(authToken, session, proview_url, clear, skipHardwareTest, previewStyle) {
      let url = proview_url || '//cdn.proview.io/init.js';
      //Setting src attribute as error page URL so if the proview is not loaded error page will be shown
      document.getElementById('contentIFrame').src = 'https://pages.talview.com/proview/error/index.html';
      let iframeWindow = document.getElementById('contentIFrame').contentWindow;
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
    };

    function onProviewStart(err, id) {
       window.ProviewStatus = 'start';
      //Post message to application loaded into application on recording start
      document.getElementById('contentIFrame').src = window.iframeUrl ;
      let iframeWindow = document.getElementById('contentIFrame').contentWindow;
      iframeWindow.postMessage({
        type: 'startedProview',
        args: [
          err,   //Error on proview stating, if any
          id     // Playback ID
        ]
      }, childOrigin);
    };

    function stopProview(url) {
      //Post message to application loaed into application on recording stop
      if (window.ProviewStatus && window.ProviewStatus == 'start') {
        ProctorClient3.stop(function() {
            window.ProviewStatus = 'stop';
            document.getElementById('contentIFrame').contentWindow.postMessage({
                type: 'stoppedProview'
            }, childOrigin);
            window.location.href = url;
        })
      }
    }
    (function() {
      const urlParams = new URLSearchParams(window.location.search);
      window.iframeUrl = urlParams.get('url');
      startProview(urlParams.get('token'),urlParams.get('profile'),urlParams.get('proview_url'))
    })();
</script>


<?php echo $OUTPUT->footer();