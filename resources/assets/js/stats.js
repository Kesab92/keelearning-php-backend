$(function() {
    $('.user-tag-select').dropdown();
    $('.table.sortable:not(.nofrontendsort)').tablesort();
    $('.nofrontendsort th').click(function () {
        var newSort = $(this).attr('data-sort')
        var currentSort = $(".stats-player-filter").find("[name='sort']").val()
        var sortDirection = parseInt($(".stats-player-filter").find("[name='sortDesc']").val())
        if(newSort === currentSort) {
            // Switch between 0 and 1
            sortDirection = (sortDirection + 1) % 2
        }
        $(".stats-player-filter").find("[name='sort']").val(newSort)
        $(".stats-player-filter").find("[name='sortDesc']").val(sortDirection)
        $(".stats-player-filter").submit()
    });

    $(".user-tag-select select").change(function() {
        $(".stats-player-filter").find("[name='tag']").val($(this).val());
        $(".stats-player-filter").submit();
    });
});
