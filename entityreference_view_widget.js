(function($) {
  /**
   * Make the views pager work inside the widget form.
   */
  Drupal.behaviors.entityreferenceViewWidgetPager = {
    attach: function (context, settings) {
      // When a pager link is clicked, store its 'page' value in a hidden
      // form element, and submit the form via ajax to trigger the rebuild.
      $('ul.pager a', context).click(function(event) {
        var match = this.href.match(/page=(.*)/);
        var page = 0;
        if (match) {
          var page = match[1];
        }
        var widget = $(this).closest('.entityreference-view-widget', context);
        $('.entityreference-view-widget-page', widget).val(page);
        $('input[name="pager_submit"]', widget).trigger('mousedown');

        event.preventDefault();
      });
    }
  }

})(jQuery);
