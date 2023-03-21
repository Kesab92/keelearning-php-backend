/**
 * Functionality for the access log list
 */
$(function() {
    $('.dropdown').dropdown();

    $(".user-search").change(function () {
        $(this).closest("form").submit();
    });
});
