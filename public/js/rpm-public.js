(function ($) {
  "use strict";

  // The DOM needs to be fully loaded (including graphics, iframes, etc)
  $(window).on("load", function () {
    const $readingProgressMeter = $(".readingProgressMeter");

    if (!$readingProgressMeter.length) return; // Exit if the progress meter is not found

    // Maximum value for the progress bar
    const winHeight = $(window).height();
    const docHeight = $(document).height();
    const max = docHeight - winHeight;

    $readingProgressMeter.attr("max", max);

    // Retrieve data attributes from the element
    const progressForeground = $readingProgressMeter.data("foreground");
    const progressBackground = $readingProgressMeter.data("background");
    const progressHeight = $readingProgressMeter.data("height");
    let progressPosition = $readingProgressMeter.data("position");
    const progressCustomPosition =
      $readingProgressMeter.data("custom-position");
    let progressFixedOrAbsolute = "fixed";

    // Handle custom position
    if (progressPosition === "custom" && progressCustomPosition) {
      $readingProgressMeter.appendTo(progressCustomPosition);
      progressPosition = "bottom";
      progressFixedOrAbsolute = "absolute";
    }

    // Determine top or bottom position styles
    const progressTop = progressPosition === "top" ? "0" : "auto";
    const progressBottom = progressPosition === "bottom" ? "0" : "auto";

    // Apply styles to the progress meter
    $readingProgressMeter.css({
      "background-color": progressBackground,
      color: progressForeground,
      height: `${progressHeight}px`,
      top: progressTop,
      bottom: progressBottom,
      position: progressFixedOrAbsolute,
      display: "block",
    });

    // Inject CSS styles for different browser progress bar elements
    const progressStyles = `
      .readingProgressMeter::-webkit-progress-bar { background-color: transparent; }
      .readingProgressMeter::-webkit-progress-value { background-color: ${progressForeground}; }
      .readingProgressMeter::-moz-progress-bar { background-color: ${progressForeground}; }
    `;
    $("<style>").text(progressStyles).appendTo("head");

    // Initialize the progress bar value (if the page is loaded within an anchor)
    const updateProgress = () => {
      const value = $(window).scrollTop();
      $readingProgressMeter.attr("value", value);
    };

    updateProgress(); // Set initial value

    // Update progress bar on scroll
    $(document).on("scroll", updateProgress);
  });
})(jQuery);
