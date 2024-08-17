(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $("#save").on("click", function () {
    var apiKey = $("#api_key").val();
    var senderKey = $("#sender_key").val();
    var secretKey = $("#secret_key").val();

    $.ajax({
      url: kakaoSettings.ajax_url,
      method: "POST",
      data: {
        action: "save_kakao_settings",
        api_key: apiKey,
        sender_key: senderKey,
        secret_key: secretKey,
        nonce: kakaoSettings.nonce,
      },
      success: function (response) {
        if (response.success) {
          alert("Settings saved successfully.");
        } else {
          alert("Failed to save settings: " + response.data);
        }
      },
      error: function () {
        alert("An error occurred while saving the settings.");
      },
    });
  });
})(jQuery);
