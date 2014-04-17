(function($) {
  Drupal.behaviors.entityreferenceViewWidget = {
    attach: function(context, settings) {
      // The following code is a workaround to make the eventual exposed filters
      // work on the embedded view displayed in the modal, probably remove once
      // http://drupal.org/node/1809958 has been merged.
      $('.ervw-add-items').bind('click',
        function() {
          if (typeof Drupal.settings['views'] != 'undefined') {
            Drupal.settings['views']['ajaxViews'] = null;
          }
        }
      );
      $('.entityreference-view-widget-modal-submit').each(function() {
        var selected_ids = '';
        var selector = '#' + $(this).data('table-id') + ' input[type=checkbox]:checked';
        $(selector).each(function(i) {
          selected_ids += $(this).val() + ';';
        });
        if (selected_ids.length > 0) {
          $(this).attr('data-selected-ids', selected_ids.substring(0, selected_ids.length - 1));
        }
      });
      var checkboxes = '#modal-content input[name="entity_ids[]"]';
      $('#entityreference-view-widget-select-all').unbind('click').text('Select all').data('unselect', 0).click(function() {
        if ($(this).data('unselect')) {
          $(checkboxes).removeAttr('checked');
          $(checkboxes).trigger('change');
          $(this).data('unselect', 0).text('Select all');
        }
        else {
          $(checkboxes).attr('checked', 'checked');
          $(checkboxes).trigger('change');
          $(this).data('unselect', 1).text('Unselect all');
        }
        return false;
      });
      // Maintain an entity_ids hidden field on the form that is displayed at the
      // bottom of the modal, this is used by the ajax callback to append the
      // selected values.
      var entity_ids_selector = '#ervw-modal-form-entity-ids';
      $(checkboxes).bind('change', function() {
          if ($(entity_ids_selector).parent().attr('data-selected-ids')) {
            $(entity_ids_selector).val($(entity_ids_selector).parent().data('selected-ids'));
          }
          $(checkboxes + ':checked').each(function(i) {
              var el = $(this);
              $('#ervw-modal-form-entity-ids').val(function(index, value) {
                  return value ? value + ';' + el.val() : el.val();
                }
              )
            }
          );
        }
      );
    }
  }

  // Create a new ajax command, ervw_draggable that is called to make the rows
  // of the widget draggable.
  Drupal.ajax.prototype.commands.ervw_draggable = function(ajax, response, status) {
    $('#' + response.selector + ' tr').each(function(){
      var el = $(this);
      Drupal.tableDrag[response.selector].makeDraggable(el.get(0));
      el.find('td:last').addClass('tabledrag-hide');
      if ($.cookie('Drupal.tableDrag.showWeight') == 1) {
        el.find('.tabledrag-handle').hide();
      }
      else {
        el.find('td:last').hide();
      }
    });
  };
})(jQuery);
