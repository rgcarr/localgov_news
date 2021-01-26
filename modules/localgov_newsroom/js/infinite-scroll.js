(function ($, Drupal) {

  /**
   * Handle infinite scroll on news search page.
   * @type {{attach: Drupal.behaviors.localgovNewsinfiniteScroll.attach}}
   */
  Drupal.behaviors.localgovNewsinfiniteScroll = {
    attach: function (context, settings) {
      // Get button and wrapper objects ready.
      var button = $("#infinite-scroll--trigger", context);
      var wrapper = $("#infinite-scroll--wrapper", context);

      // Click handler on trigger button.
      button.once('localgovNewsinfiniteScroll').on("click", function(event) {
        // Do not redirect the page.
        event.preventDefault();

        // Disable the button to stop old people spamming.
        button.attr('disabled', true);

        // Create http request.
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function() {
          // If the response is finished and we received a 200 success, We can
          // convert the response to JSON and append the new items to the
          // bottom if the list.
          if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            var responseJson = JSON.parse(xmlHttp.responseText);
            wrapper.append(responseJson.html);

            // Re-enable the button.
            button.attr('disabled', false);

            // If there are no more item to load, hide the button.
            if (responseJson.finished) {
              button.addClass('hidden');
            }
          }
        };

        // Pass the current page parameters to the new url.
        var params = window.location.search.substr(1).split('&');

        // Get the page number to load.
        var page = button.attr('data-page');
        button.attr('data-page', parseInt(page) +1);
        params.push('page=' + page);

        // Convert the params array to a string.
        params = params.join('&');

        // Perform request.
        xmlHttp.open("GET", settings.infiniteScroll.callbackUrl + "?" + params, true);
        xmlHttp.setRequestHeader("Content-type", "application/json; charset=utf-8");
        xmlHttp.send(null);
      });
    }
  };

})(jQuery, Drupal);
