/**
 * @file
 * File: entityreference_view_widget.js.
 */

(function($) {
  Backdrop.behaviors.entityreferenceViewWidget = {
    attach: function(context, settings) {
      var tableRowSelector = 'tr.entityreference-view-widget-table-row';
      $(tableRowSelector).once('processed', function () {
        var $row = $(this);

        // Prevent item removal when clicking on checkbox labels.
        $('label', $row).click(function (e) {
          e.preventDefault();
        });

        // Handle item removal.
        $('input.entityreference-view-widget-checkbox', $row).click(function () {
          if (!$(this).is(':checked')) {
            var $table = $row.parents('table.field-multiple-table:first');
            $row.remove();

            // If no rows are left, display an empty message.
            if (!$(tableRowSelector, $table).length
                && typeof settings.entityreferenceViewWidgetEmptyMessage !== 'undefined') {
              $('tbody', $table).html('<tr class="odd"><td colspan="3">' +
              settings.entityreferenceViewWidgetEmptyMessage + '</td> </tr>');
            }
          }
        });
      });

      var checkboxes = '#modal-content input.entity-reference-view-widget-select';
      var selectAllSelector = '#entityreference-view-widget-select-all';
      // Use the proper jQuery method depending on the jQuery version.
      var version = $.fn.jquery.split('.');
      var use_prop = (version[0] > 1 || version[1] > 5);
      $(selectAllSelector).once('processed', function() {
        $(this).click(function(e) {
          e.preventDefault();
          if ($(this).data('unselect')) {
            use_prop ? $(checkboxes).prop('checked', false) : $(checkboxes).removeAttr('checked');
            $(this).data('unselect', 0).text(Backdrop.t('Select all'));
          }
          else {
            use_prop ? $(checkboxes).prop('checked',true) : $(checkboxes).attr('checked', 'checked');
            $(this).data('unselect', 1).text(Backdrop.t('Unselect all'));
          }
        });

        // Select checkboxes when clicking on table rows.
        var $table_rows = $(checkboxes).parents('table').find('tbody tr');
        if ($table_rows.length > 0) {
          $table_rows.click(function (e) {
            var $current_checkbox = $('input.entity-reference-view-widget-select', $(this));
            if ($current_checkbox.attr('checked')) {
              $current_checkbox.removeAttr('checked');
            }
            else {
              $current_checkbox.attr('checked', 'checked');
            }
          });
        }
      });

      if (settings.entityReferenceViewWidget) {
        var ervwSetttings = settings.entityReferenceViewWidget.settings;
        if (ervwSetttings.cardinality != -1 || $(checkboxes).length === 0) {
          $(selectAllSelector).remove();
        }

        var selector = '#' + ervwSetttings.table_id + ' input[type=checkbox]:checked';
        var selected_ids = '';
        $(selector).each(function() {
          selected_ids += $(this).val() + ';';
        });
        if (selected_ids.length > 0) {
          $('input[name="selected_entity_ids"]').val(selected_ids.substring(0, selected_ids.length - 1)).trigger('change');
        }

        // We need to pass the settings via an hidden field because Views doesn't
        // allow us to pass data between ajax requests.
        if (settings.entityReferenceViewWidget.serialized) {
          $('input[name="ervw_settings"]').val(settings.entityReferenceViewWidget.serialized);
        }
      }
    }
  };

  // Create a new ajax command, ervw_draggable that is called to make the rows
  // of the widget draggable.
  Backdrop.ajax.prototype.commands.ervw_draggable = function(ajax, response, status) {
    $('#' + response.selector + ' tr').each(function(){
      var el = $(this);
      Backdrop.tableDrag[response.selector].makeDraggable(el.get(0));
      el.find('td:last').addClass('tabledrag-hide');
      if ($.cookie('Backdrop.tableDrag.showWeight') == 1) {
        el.find('.tabledrag-handle').hide();
      }
      else {
        el.find('td:last').hide();
      }
    });
  };
})(jQuery);
