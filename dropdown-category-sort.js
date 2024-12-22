
jQuery(document).ready(function ($) {
    $('.category-tree').sortable({
        items: '> .category-item',
        placeholder: 'sortable-placeholder',
    });

    $('#dropdown-category-sort-form').on('submit', function () {
        let sortedCategories = [];
        $('.category-tree .category-item').each(function () {
            sortedCategories.push($(this).data('id'));
        });
        $('#sorted-categories').val(JSON.stringify(sortedCategories));
    });
});
