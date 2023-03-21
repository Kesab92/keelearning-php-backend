/**
 * Functionality for the mail template list and editing
 */
$(function() {
    $(".mail").click(function() {
        $(".mails-wrapper .visible").removeClass("visible");
        $(this).addClass("visible");
        showMail($(this).data("mail-type"));
    });

    // Check if we want to edit a specific category
    var editType = getQueryParameter("edit");
    if (editType) {
        showMail(editType);
    }

    // IE fix, because submit outside of the form itself is not triggered properly
    $('.button.approve.new-mail-form').click(function(e) {
        e.preventDefault();
        $('input[type=submit].ie-submit-fix.new-mail-form').trigger('click');
    });

    function showMail(mailType) {
        updateUrlWithId(mailType);
        $(".mail-content").addClass("loading");
        showMailContainer();

        $(".mail-content").load("/mails/" + mailType, function() {

            // Disable the loading state
            $(".mail-content").removeClass("loading");

            $(this).find('.ui.checkbox').checkbox();
            $(this).find('.has-popup').popup();
            $(this).find('.js-popup').popup();

            // Setup the save event
            $(".mail-content").find(".save-button").click(function () {

                var title = $(".mail-title").val();
                var content = $(".mail-content-edit").val();
                var lang = $("input[name='lang']").val();

                $(".mail-content").addClass("loading");
                $.post("/mails/" + $(this).data("mail-type") + "?lang=" + lang, {
                    title: title,
                    body: content
                }, function () {
                    $(".mail-content").removeClass("loading");
                    window.location.reload();
                });
            });
        });
    }

    function showMailContainer() {
        $(".mails-wrapper").addClass("editing");
    }
});
