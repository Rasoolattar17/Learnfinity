

define(['jquery', 'local_swap_list/swap_list'],
function($, swapList) {
    var index = {
        dom: {
            main: null
        },
        init: function() {
            index.dom.main = $(document).find('#local_swap_list_index');

            var left = {
                'heading': 'left-heading',
                'list': [
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    },
                    {
                        'list_item_data_id': 'left_list_item_data_id',
                        'list_item_data_value': 'left_list_item_data_value',
                        'list_item_text': 'left_list_item_text'
                    }
                ]
            };

            var right = {
                'heading': 'right-heading',
                'list': [
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    },
                    {
                        'list_item_data_id': 'right_list_item_data_id',
                        'list_item_data_value': 'right_list_item_data_value',
                        'list_item_text': 'right_list_item_text'
                    }
                ]
            };

            swapList.renderBlocks(index.dom.main, left, right);

        },

    };
    return {
        init: index.init
    };
});
