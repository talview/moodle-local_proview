# Introduciton | `moodle-local_proview` -

The Proview plugin, developed by Talview Inc., integrates the "Proview" proctoring solution into Moodle LMS.
This plugin continuously evolves with frequent feature enhancements.
The plugin captures and securely stores the candidate's video during an exam.

**Note:**
1. This plugin is free to download but a subscription is needed to make full use of it. For any query on subscription or to get a user token please raise a ticket [here](https://proviewsupport.freshdesk.com/support/tickets/new).
2. Before installing this plugin, ensure the quizaccess_proctor plugin is installed. Refer to the Installation Guide [here](https://github.com/talview/moodle-quizaccess_procto).

## About Proview

---

Proview is an automated cognitive remote proctoring solution developed by Talview to promote equal career opportunities and create a fair assessment environment. It eliminates scheduling and location constraints by providing fully secure and authenticated tests that can be taken anytime, anywhere.

Using advanced video and audio analytics, Proview monitors the candidate's activities during the test, ensuring their focus remains on the test screen. It also detects suspicious objects in the video and analyzes background voice activity to identify potential test irregularities.

Administrators have the ability to monitor students taking the test from their preferred location. A comprehensive log of browser activity and audio-visual responses is provided. Proview disables the copy/paste function during the exam to prevent cheating.

In contrast to other tools available in the market, Proview captures candidates' complete audio and video feeds throughout the test to detect any suspicious activity and raise alerts. The "Proview index" indicates the candidate's sincerity and engagement during the exam.

For more information about Proview and frequently asked questions about the plugin and Proview, please visit "**https://proviewsupport.freshdesk.com/support/solutions**".


## Installation

---

1. Access the admin view and navigate to Site Administration -> Plugins -> Install Plugin.
2. Download the latest release from https://github.com/talview/moodle-local_proview/releases .
3. Click on "Install the plugin" and follow the subsequent pages to complete the installation process.
4. On the plugin settings page, perform the following steps:
5. Enable Proview by checking the checkbox (Default Disabled).
6. Enter the Proctor Token provided by Talview (subscription based).
7. Enter the CDN URL provided by Talview (subscription based).
8. Enter the Proview Admin URL provided by Talview e.g., https://appv7.proview.io/embedded.
9. Enter the Account Name provided by Talview.
10. Update the root_dir to match the root directory of your Moodle installation.  
**Note**: The root directory refers to the directory where Moodle is installed. If you access Moodle using a URL like 'https://example.domain.com/,’ the root directory is '/,’ and no additional configuration is required. However, if you access Moodle using a URL like 'https://example.domain.com/moodle/,’ the root directory is '/moodle/' and must be configured accordingly in the root_dir field during the Proview installation process.

The installation process is now complete.

**Note**: While the plugin is installed, please share the domain name in which Moodle is hosted for the test takers with Talview to do domain whitelisting.

**Steps to upgrade the plugin:**  
In the admin view, go to Site Administration -> Plugins -> Install Plugin.  
Download the latest release from https://github.com/talview/moodle-local_proview/releases.  
Click on “Install the plugin.”. You will be directed through some pages; follow the steps.
Plugin Upgraded.

## Post Installation Steps

---

### Custom Configuration for Plugin Features

Several plugin features require custom configuration. Here is a list of these features:

### Disable Proview For A Group

Disable Proview for specific candidates in a course.

1. Go to the specific course and navigate to Participants.
2. Click the Settings Icon on the Right Hand Side and choose "Groups."
3. Select "Create Group."
4. Set the group name as "proview_disabled."
5. Return to the course, click the Settings Icon on the Right Hand Side, then select "Edit Settings."
    - Note: Any candidate added to this group will not have Proview enabled for them in this course.

### Configure Proctoring Type For A Quiz

Launching specific types of Proview (AI Proctor / Live Proctoring / Record and Review).
- Select one of the following values from “Proview Proctoring Session” -> “Select Proctoring Type” within the quiz settings window to set up a specific proctoring type. The supported proctoring types are outlined below; their availability is subscription-based:
    - No Proctoring: Neither Proctoring nor Talview Secure Browser will be enabled for the quiz.
    - AI Proctoring: Sessions are assessed by an AI engine, generating an automated Proview Score. Choose AI Proctoring from the dropdown values.
    - Record and Review: Completed sessions are reviewed by a proctor, who assigns a Proview Rating. Opt for Record and Review Proctoring in the dropdown values.
    - Live Proctoring: Proctors assess ongoing sessions and can interact with candidates if needed. The proctor provides the Proview rating. Select Live Proctoring from the dropdown values.
    - Note: For Live Proctoring, ensure slots are booked, or the schedule is shared with Talview.

### Configure Talview Secure Browser (TSB) For A Quiz

Talview Secure Browser (TSB): TSB is a secure browser compatible with Windows and Mac devices. Enabling TSB compels candidates to use TSB for exams.

1. TSB Activation: Once enabled, candidates are directed to an external page to download the latest TSB version and launch the exam.
2. TSB Configuration: Enforce TSB by selecting the checkbox “Proview Proctoring Session” -> “Enable Talview Secure Browser” within the quiz settings window.

### Viewing the Proctor Session

The proctor session and the generated Proview rating for each candidate can be viewed on the Proview Admin page. Additionally, this information is accessible within Moodle.

To locate the Proview URL associated with each candidate's attempt for a specific quiz, follow these steps:

1. Access the specific quiz.
2. Go to Settings -> Grades (Nested under Results).
3. The Proview URL will be listed at the end of the table under the column "Proview URL.”
4. Clicking on the proctor link will open the Proview admin interface for that candidate's attempt within Moodle.

### Uninstall Plugin

Follow these steps to uninstall the plugin:

1. Access the admin view and navigate to Site Administration -> Plugins -> Plugin Overview.
2. Scroll down to the bottom of the page and locate Proview (nested under Local Plugins).
3. Click on "Uninstall".
    - Note: Uninstalling the plugin will not automatically remove manually configured settings. Any manual configurations must be removed separately. Failing to remove these configurations will not impact the Moodle workflow or the reinstallation of the plugin.

**Important:** Uninstalling Proview will delete the data (mapping between candidate attempts and Proview admin) from the Moodle database but not from Talview's server. If you reinstall the plugin, the data will not be restored in Moodle's database.


## Terms and Conditions

---

Talview Inc and all of its subsidiaries (“Talview”) provides Proview and its related services (“Service”) subject to your compliance with the terms and conditions (“Terms of Service”) set forth.

Talview reserves the right to update and modify the Terms of Service at any time without notice. New features that may be added to the Service shall be subject to the Terms of Service. Should you continue to use the Service after any such modifications have been made, this shall constitute your agreement to such modifications. You may always view the most recent copy of the Terms of Service at "<https://www.talview.com/proview/terms-conditions>"

Violation of any part of the Terms of Service will result in termination of your account.

## Roadmap

---

-   [x] Proview Version support
-   [x] Moodle 3.5 LTS support
-   [x] Switch off Proview for some quizzes
-   [x] Integrated playback component into moodle admin view
-   [ ] Candidate ID Correlation
-   [x] Proview V7+ support (AI, RR, LP)
-   [x] Talview Secure Browser support
-   [x] Integrate [Quizaccess_Proctor Plugin](https://github.com/talview/moodle-quizaccess_proctor)
-   [ ] Secure Proctor Link
-   [ ] Moodle 4.x support
-   [ ] Merge Quizaccess_Proctor Plugin
