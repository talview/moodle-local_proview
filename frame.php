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
global $PAGE, $COURSE;

$PAGE->set_context(context_system::instance());

$PAGE->set_pagelayout('embedded');
$PAGE->set_heading("Proview Quiz");
$PAGE->set_url('/local/proview/frame.php');

echo $OUTPUT->header();

?>

<iframe id="contentIFrame" title="Iframe application" style="width: 98vw; height:95vh; border: 0px;">
  <p>Your browser does not support iframes.</p>
</iframe>
<script src="https://browser.sentry-cdn.com/5.18.1/bundle.min.js" integrity="sha384-4zdOhGLDdcXl+MRlpApt/Nvfe6A3AqGGBil9+lwFSkXNTv0rVx0eCyM1EaJCXS7r" crossorigin="anonymous"></script>
<script>
    var childOrigin = '*';
    Sentry.init({
      dsn: 'https://61facdc5414c4c73ab2b17fe902bf9ba@o286634.ingest.sentry.io/5304587'
    });
    // Defining function for event handling on postMessage from any window
    function receiveMessage(event) {
    //  if (event.origin == childOrigin) {
      try {
        if(event.data.type == 'startProview') {
            startProview(...event.data.args);
        }
        if(event.data.type == 'stopProview') {
            stopProview(event.data.url);
        }       
      } catch (error) {
        if( error && error.error ) {
          Sentry.captureException(error.error);
        } else {
          Sentry.captureException(error);
        }
        document.getElementById('contentIFrame').src = 'https://pages.talview.com/proview/error/index.html'; 
      }
    }

    window.addEventListener("message", receiveMessage, false);
    window.addEventListener('error', function(e) {
      console.error(e);
    }, false);


    //Javascript function to start proview invoked upon postMessage from iframe
    function startProview(authToken, profileId, session, proview_url, skipHardwareTest, previewStyle, clear) {
      let url = proview_url || '//cdn.proview.io/init.js';
      document.getElementById('contentIFrame').src = window.iframeUrl;
      //script to load the proview STARTS
      (function(i,s,o,g,r,a,m){i['TalviewProctor']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script',url,'tv');
        tv('init', authToken,{
            profileId: profileId,
            session: session,
            clear: clear || false,
            skipHardwareTest: skipHardwareTest || false,
            previewStyle: previewStyle || 'position: fixed; bottom: 0px;',
            initCallback: onProviewStart
      });
    }

    function onProviewStart(err, id) {
      try {
        const urlParams = new URLSearchParams(window.location.search);
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
        
        let url = urlParams.get('proview_url') || '//cdn.proview.io/init.js';
        url = ((url.search('v5')!=-1)?'https://appv5.proview.io/embedded/':'https://app.proview.io/embedded/') + id;
        const arr = {
          "user_id"       : urlParams.get('profile'),
          "quiz_id"       : urlParams.get('quizId'),
          "course_id"     : '<?php print $COURSE->id ?>',
          "proview_url"   : url,
        }
        const xmlhttp = new XMLHttpRequest();
        
        let retries=5;
        function run(){
          xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4) {
              // console.log(xmlhttp.response);
            }
            if (xmlhttp.status == 404) {
              if (retries > 0) {
                retries-=1;
                // console.log(retries,": ",xmlhttp);
                run();
              } else if(xmlhttp.readyState === 4) {
                // console.log(retries,": ERROR!!");
                Sentry.captureException(new Error(xmlhttp.response));
              }
            }
          }
          xmlhttp.open("POST","datastore.php",true);
          xmlhttp.send(JSON.stringify(arr));
        }
        run();
      } catch (error) {
        console.log("Enter catch")
        if( error && error.error ) {
          Sentry.captureException(error.error);
        } else {
          Sentry.captureException(error);
        }
        document.getElementById('contentIFrame').src = 'https://pages.talview.com/proview/error/index.html'; 
      }
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
      try {
        const urlParams = new URLSearchParams(window.location.search);
        window.iframeUrl = urlParams.get('url');
        window.quizPassword = urlParams.get('quizPassword'); //setting quiz password
        startProview(urlParams.get('token'), urlParams.get('profile'), urlParams.get('session'), urlParams.get('proview_url'));
      } catch (error) {
        if( error && error.error ) {
          Sentry.captureException(error.error);
        } else {
          Sentry.captureException(error);
        }
        document.getElementById('contentIFrame').src = 'https://pages.talview.com/proview/error/index.html'; 
      }
    })();
</script>


<?php echo $OUTPUT->footer();