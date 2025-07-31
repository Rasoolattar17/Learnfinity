/* eslint-disable promise/always-return */
define(['jquery', 'core/notification', 'core/templates', 'core/st_loader'],
function($, notification, templates, stLoader) {
    var renderBlocks = function(div, left, right) {
        var variables = {
            main: null,
            left: {
                'main-class': 'left-swap-main',
                'select-all-id': 'left-select-all',
                'list-class': 'left-swap-list',
                'search-class': 'left-swap-search'
            },
            right: {
                'main-class': 'right-swap-main',
                'select-all-id': 'right-select-all',
                'list-class': 'right-swap-list',
                'search-class': 'right-swap-search'
            },
            swapListListItem: null,
            moveLeftBtn: null,
            moveRightBtn: null,
            leftSelectAll: null,
            rightSelectAll: null,
            leftSwapSearch: null,
            rightSwapSearch: null,
            selectedCount: 0
        };

        left = $.extend({}, variables.left, left);
        right = $.extend({}, variables.right, right);

        var hash = {
            left,
            right
        };

        templates.render('local_swap_list/blocks', hash).then(function(html) {
            div.html(html);
            variables.main = $(document).find('#local_swap_list');
            variables.swapListListItem = variables.main.find('.swap-list-list-item');
            variables.moveLeftBtn = variables.main.find('.move-left-btn');
            variables.moveRightBtn = variables.main.find('.move-right-btn');
            variables.leftSelectAll = variables.main.find('#left-select-all');
            variables.rightSelectAll = variables.main.find('#right-select-all');
            variables.leftSwapSearch = variables.main.find('.left-swap-search');
            variables.rightSwapSearch = variables.main.find('.right-swap-search');


            variables.swapListListItem.click(function(e) {
                var li = $(e.currentTarget);
                var countSpan = li.parent().parent().parent().parent().find('.selected-count');
                variables.selectedCount = parseInt(countSpan.html(), 10);

                if (li.hasClass('selected')) {
                    li.removeClass('selected');
                    if (variables.selectedCount != 0) {
                        variables.selectedCount--;
                    }
                } else {
                    li.addClass('selected');
                    variables.selectedCount++;
                }

                if (li.find('.list-item-check').hasClass('d-none')) {
                    li.find('.list-item-check').removeClass('d-none');
                } else {
                    li.find('.list-item-check').addClass('d-none');
                }

                li.parent().parent().parent().parent().find('.selected-count').html(variables.selectedCount);
            });

            variables.moveLeftBtn.click(function() {
                if (variables.selectedCount) {
                    stLoader.showLoader();
                    variables.main.find('.right-swap-list > .swap-list-list-item.selected').each(function(index, item) {
                        item = $(item);
                        variables.main.find('.left-swap-list').prepend(item);
                        if (item.hasClass('swapped')) {
                            item.removeClass('swapped');
                        } else {
                            item.addClass('swapped');
                        }

                        if (item.hasClass('selected')) {
                            item.removeClass('selected');
                        } else {
                            item.addClass('selected');
                        }

                        $('#right-select-all').next().html(0);
                        item.find('.list-item-check').addClass('d-none');

                        variables.selectedCount = 0;
                        stLoader.hideLoader();

                    });
                } else {
                    notification.addNotification({
                        type: 'error',
                        message: 'Select at least one from the list.'
                    });
                }
            });

            variables.moveRightBtn.click(function() {
                if (variables.selectedCount) {
                    stLoader.showLoader();
                    variables.main.find('.left-swap-list > .swap-list-list-item.selected').each(function(index, item) {
                        item = $(item);
                        variables.main.find('.right-swap-list').prepend(item);
                        if (item.hasClass('swapped')) {
                            item.removeClass('swapped');
                        } else {
                            item.addClass('swapped');
                        }

                        if (item.hasClass('selected')) {
                            item.removeClass('selected');
                        } else {
                            item.addClass('selected');
                        }

                        $('#left-select-all').next().html(0);

                        item.find('.list-item-check').addClass('d-none');

                        variables.selectedCount = 0;
                        stLoader.hideLoader();
                    });
                } else {
                    notification.addNotification({
                        type: 'error',
                        message: 'Select at least one from the list.'
                    });
                }

            });

            variables.leftSelectAll.click(function(e) {
                var checkbox = $(e.currentTarget);
                var selected = checkbox[0].checked;
                variables.selectedCount = 0;

                variables.main.find('.left-swap-list > .swap-list-list-item').each(function(index, item) {
                    item = $(item);

                    if (!selected) {
                        item.removeClass('selected');
                        if (variables.selectedCount != 0) {
                            variables.selectedCount--;
                        }
                        item.find('.list-item-check').addClass('d-none');
                    } else {
                        if (!item.hasClass('disabled')) {
                            item.addClass('selected');
                            item.find('.list-item-check').removeClass('d-none');
                            variables.selectedCount++;
                        }
                    }
                });

                checkbox.parent().find('.selected-count').html(variables.selectedCount);
            });

            variables.rightSelectAll.click(function(e) {
                var checkbox = $(e.currentTarget);
                var selected = checkbox[0].checked;
                variables.selectedCount = 0;

                variables.main.find('.right-swap-list > .swap-list-list-item').each(function(index, item) {
                    item = $(item);

                    if (!selected) {
                        item.removeClass('selected');
                        if (variables.selectedCount != 0) {
                            variables.selectedCount--;
                        }
                        item.find('.list-item-check').addClass('d-none');
                    } else {
                        item.addClass('selected');
                        variables.selectedCount++;
                        item.find('.list-item-check').removeClass('d-none');
                    }
                });

                checkbox.parent().find('.selected-count').html(variables.selectedCount);
            });


            variables.leftSwapSearch.keyup(function() {
                var search = variables.leftSwapSearch.val().toLowerCase();
                variables.main.find('.left-swap-list > .swap-list-list-item').each(function(index, item) {
                    item = $(item);
                    if (item.html().toLowerCase().includes(search)) {
                        item.removeClass('d-none');
                    } else {
                        item.addClass('d-none');
                    }
                });
            });

            $('.loader').addClass('d-none');

            variables.rightSwapSearch.keyup(function() {
                var search = variables.rightSwapSearch.val().toLowerCase();
                variables.main.find('.right-swap-list > .swap-list-list-item').each(function(index, item) {
                    item = $(item);
                    if (item.html().toLowerCase().includes(search)) {
                        item.removeClass('d-none');
                    } else {
                        item.addClass('d-none');
                    }
                });
            });

        }).fail(function() {
            notification.addNotification({
                type: 'error',
                message: 'Something went wrong..'
            });
        });
    };

    return {
        renderBlocks: renderBlocks
    };
});
