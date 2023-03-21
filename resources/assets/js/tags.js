$(document).ready(function() {
    var updateMessageContainer = $('.update-message');

    $(".add-tag").click(function() {
        $('.add-tag-modal').modal({
            onApprove: function() {
                if(!$('input[name=label]').is(':valid')) {
                    return false;
                } else {
                    return true;
                }
            }
        }).modal('show');
    });

    // IE fix, because submit outside of the form itself is not triggered properly
    $('.button.approve.new-tag-form').click(function(e) {
        e.preventDefault();
        $('input[type=submit].ie-submit-fix.new-tag-form').trigger('click');
    });

    // Initialize the editor
    new MediumEditor('.editable', {
        toolbar: false
    });

    // Save on
    $('.tags-wrapper').find('.header.editable').keyup(function(event) {
        var id = $(this).data("tag-id");
        var label = $(this).text();

        // Delay the ajax request
        setTimeout(function() {
            hideMessage();
            label = $.trim(label);
            if(label.length < 2) {
                showMessage('warning', 'Der Labelname ist zu kurz!')
            } else {
                $.post("/tags/" + id, {
                    label: label
                }).fail(function(xhr, textStatus, error) {
                    showMessage('error', 'Es gab einen unerwarteten Fehler. Bitte laden Sie die Seite neu und versuchen Sie es erneut')
                }).done(function() {
                    showMessage('success', 'Alle Änderungen wurden gespeichert')
                });
            }

        }, 500);
    });


    $(".tag-delete").click(function (e) {
        return confirm('Sind Sie sicher, dass Sie diesen TAG löschen möchten?');
    });

    function showMessage(mode, text) {

        var heading = '';
        if(mode == 'error') {
            heading = 'Fehler';
            updateMessageContainer.addClass('negative');
            updateMessageContainer.removeClass('positive');
            updateMessageContainer.removeClass('warning');
        } else if(mode == 'warning') {
            heading = 'Achtung';
            updateMessageContainer.addClass('warning');
            updateMessageContainer.removeClass('positive');
            updateMessageContainer.removeClass('negative');
        } else {
            heading = 'Änderungen gespeichert';
            updateMessageContainer.addClass('positive');
            updateMessageContainer.removeClass('negative');
            updateMessageContainer.removeClass('warning');
        }
        updateMessageContainer.find('.header').text(heading);
        updateMessageContainer.find('.content').text(text);
        updateMessageContainer.show();

    }

    function hideMessage() {
        updateMessageContainer.hide();
    }

    $('.tags-wrapper').find('input[type="checkbox"].exclusive').change(function() {
        hideMessage();
        $.post("/tags/setexclusive/" + $(this).data("tag-id"), {
            exclusive: $(this).is(":checked")
        }).fail(function (xhr, textStatus, error) {
            showMessage('error', 'Es gab einen unerwarteten Fehler. Bitte laden Sie die Seite neu und versuchen Sie es erneut');
        }).done(function () {
            showMessage('success', 'Alle Änderungen wurden gespeichert');
        });
    })

    $('.tags-wrapper').find('input[type="checkbox"].hideHighscore').change(function() {
        hideMessage();
        $.post("/tags/setHideHighscore/" + $(this).data("tag-id"), {
          hideHighscore: $(this).is(":checked")
        }).fail(function (xhr, textStatus, error) {
          showMessage('error', 'Es gab einen unerwarteten Fehler. Bitte laden Sie die Seite neu und versuchen Sie es erneut');
        }).done(function () {
          showMessage('success', 'Alle Änderungen wurden gespeichert');
        });
      })

    $('.dropdown').dropdown({
      onChange: function () {
        hideMessage();
        var element = $(this);
        var currentGroupId = element.data('current-group-id') || 'null';
        var taggroup = element.find('input[type="hidden"][name="taggroup"]').val();
        if (currentGroupId == taggroup) {
          return;
        }
        if (element.data('tag-id')) {
          $.post('/tags/setTaggroup/' + element.data('tag-id'), {
            tag_group_id: taggroup
          }).fail(function (xhr) {
            element.dropdown('set selected', currentGroupId)
            if (xhr.status == 400) {
              alert('Dieser TAG kann keiner TAG-Gruppe zugeordnet werden, da ' + xhr.responseText + ' Benutzer schon einen TAG aus dieser Gruppe ' + (xhr.responseText > 1 ? 'besitzen' : 'besitzt') + '. Die TAG-Gruppe lässt maximal einen TAG der Gruppe zu.')
            } else {
              alert('Es gab einen unerwarteten Fehler. Bitte laden Sie die Seite neu und versuchen Sie es erneut')
            }
          }).done(function () {
            element.data('current-group-id', taggroup ? taggroup : 'null');
          });
        }
      },
    });
});
