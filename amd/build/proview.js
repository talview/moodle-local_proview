define(['jquery'], function($) {
  return {
   init: function(password) {
    $(".singlebutton.quizstartbuttondiv").find("button").click(function() {
      $(".moodle-dialogue-base").hide();
      $("#id_quizpassword").val(password);
      $("#mod_quiz_preflight_form").submit();
    })
   }
  };
 });