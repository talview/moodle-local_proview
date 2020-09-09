# Introduciton | `moodle-local_proview` - 

A local Moodle Module adding Proview integration to Moodle.

This plugin is developed by the team at Talview Inc and implements “Proview” (which is a proctoring solution developed in Talview) in Moodle LMS. It is an ever growing solution with regular new feature enhancements. The plugin will capture and store the candidate's video while the candidate attempts an exam.

***Note:** This plugin is free to download but a subscription is needed to make full use of it. For any query on subscription or to get a user token please raise a ticket on: “<https://proviewsupport.freshdesk.com/support/tickets/new>”.*

## Installation

---

-   In the admin view go to Site Administration -> Plugins -> Install Plugin.

-   Download the plugin from Moodle Plugin Directory.

-   Click on “Install the plugin”. You will be directed through some pages, follow the steps.

-   “On the plugin settings page”
    -   Click on Checkbox to enable Proview (Default Disabled).
    -   Enter the User Token shared by Talview (subscription based).
    -   Enter the cdn provided by Talview (subscription based).
    -  Change the root_dir to root directory of your moodle installation.</br>
    *(Root directory is the directory where moodle is installed. If you can access moodle with a URL like this '<https://example.domain.com/>' then the directory is '/' and no configuration is required. If you access moodle with a URL like '<https://example.domain.com/moodle/>' then '/moodle/' is the root directory and the same needs to be configured in the root_dir field when installing proview.)*

-   Installation Completed.

## Post Installation Steps

---

There are some plugin features to enable which user has to make some custom configuration. List of such features:

-   Course Level Configuration: Enable/Disable Proview for specific courses.
-   Quiz Level Configuration: Enable Proview for specific quizzes. *(This is the default configuration, and will be used if no manual configuration is made by Admin.)*

-   Proview Disabled group: Disable Proview for specific candidates in a course.

### Configurations to enable above mentioned features

-   **Course Level Configuration:**
    -   Go to Site Administration -> Courses -> Course Custom Fields and select “Add a New Category”.

    -   Rename “Other Fields” to “Proview”.
   
    -   Click on “Add a new Custom Field” and select “Dropdown Menu”.
   
    -   Put the name as “Proview Configuration”.
   
    -   Short name as “proview_configuration”.
   
    -   Menu Options (Nested under Dropdown menu field Settings) as
        -   Always On
        -   Quiz Level Configuration
        -   Always Off</br> ***Note:** Each value should be in a separate line and this order should be maintained.*
      
    -   Set Default value as “Always On”.
   
    -   Set “Locked” as “No” and “Visible to” as “Everyone”.
   
    -   After the changes are saved go to any Course.
   
    -   On the Right Hand Side click on Settings Icon and click on “Edit Settings”.
   
    -   Scroll to the bottom of the page and open Proview Category.
   
    -   Here you can select the Course Level Configuration from three given options:
        -   Always On: Enable Proview for complete course.
        -   Quiz Level Configuration: Enable Proview for specific quizzes in this course.
        -   Always Off: Disable Proview for complete course.

-   **Quiz Level Configuration:** If you have not made the configuration required for course level configuration of Proview then this is the default configuration of Proview. This is also the expected behaviour from proview if you have enabled “Quiz Level Configuration” in “Course Level Configuration”.</br>
As the name suggests, in this configuration Proview is enabled for specific quizzes. To enable Proview in a specific quiz you need to rename the quiz such that the quiz name contains the keyword “proctor” in any form. It is case insensitive and can be appended to any other word. Some valid examples: Proctored Quiz, Quiz Proctoring, Quizproctor, Proctorquiz, proctor, PROCTOR1234 etc.</br></br>
***Note:** “Unproctored quiz” is also a valid name and proview will launch for that quiz (since the name contains the keyword “proctor”), even though the name is contradictory.*

-   **Proview Disabled Group:** This is a user group which has to be created for each course in which you have specific candidates for whom proview should not load.
    -   Go to the specific course and then go to Participants
    -   Click the Settings Icon on the Right Hand Side and select “Groups” .
    -   Click on “Create Group”.
    -   Set the group name as “proview_disabled”.
    -   Go to the course again and click on the Settings Icon on the Right Hand Side” and select “edit settings”.
    -   Scroll down to “Groups” category and set group mode as “Seperate Groups”.</br>
    *Now any candidate you add in this group will not have proview enabled for them in this course.*    

## Roadmap

---

-   [x] Proview Version support
-   [x] Moodle 3.5 LTS support
-   [x] Switch off Proview for some quizzes
-   [x] Integrated playback component into moodle admin view
-   [ ] Candidate ID Correlation
-   [ ] Moodle 2.x Support
