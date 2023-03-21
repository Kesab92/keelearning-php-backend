/**
 * Functionality for the faq page
 */
$(function() {
    var editors = [];
    $('.full-editable').each(function() {
        editors.push(new MediumEditor(this, {
            toolbar: true
        }));
    });

    for(var i = 0; i < editors.length; i++) {
        $('.full-editable').eq(i).mediumInsert({
            editor: editors[i],
            addons: {
                images: {
                    deleteScript: null
                }
            }
        });
    }

    $(".edit-faq-title").click(function(event) {
        event.preventDefault();
        event.stopPropagation();
        var title = $(this).parent().find('.title');
        var newTitle = prompt('Bitte neuen Titel für die Seite eingeben:', title.html().trim());
        if(newTitle) {
            $.post("/misc/faq/" + $(this).attr('data-page-id'), {
                title: newTitle
            }, function () {
                title.html(newTitle);
            });
        }
    });

    $(".save-faq-changes").click(function() {
        var parent = $(this).parents('.tab');
        var content = parent.find(".faq-content").html();
        parent.addClass("loading");
        $.post("/misc/faq/" + $(this).attr('data-page-id'), {
            content: content
        }, function () {
            parent.removeClass("loading");
        });
    });

    $('.add-faq-page').click(function() {
        if(confirm('Neue Hilfe-Seite anlegen?')) {
            window.location = '/misc/faq/add';
        }
    });

    $('.delete-faq-page').click(function(event) {
        if(!confirm('Diese Hilfe-Seite löschen?')) {
            event.preventDefault();
        }
    })

});
