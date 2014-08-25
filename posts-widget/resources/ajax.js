var $j = jQuery.noConflict();


function start_sortable(widgetId) {
    var widgetContainer = $j('#' + widgetId + '');
    widgetContainer.find('.search-posts-list-selected').sortable({
        placeholder: 'ui-state-highlight'
    });
}

$j(function() {
    startWidgetFunc();
    start_sortable();

    $j(document).ajaxStop(function() {
        startWidgetFunc();
    });

});


function startWidgetFunc() {

    $j('#widgets-right .widget').each(function() {

        widgetId = $j(this).attr('id');
        start_sortable(widgetId);

        var widgetContainer = $j(this);
        widgetSave = $j('#' + widgetId + '-savewidget');

        widgetSave.live('click', function(event) {

            start_sortable(widgetId);
            widgetContainer.find('.search_posts').trigger('click');
        });

        widgetContainer.find('.search_posts').live('click', function(e) {
            

            var search_value = $j(this).prev().val();

            widgetContainer.find('.search-posts-list').html('<span class="spinner" style="display: block;"></span>');

            if (search_value != '') {
                var data = {
                    'action': 'test_response',
                    'post_var': search_value
                };
                $j.post(the_ajax_script.ajaxurl, data, function(response) {
                    var container = widgetContainer.find('.search-posts-list');
                    container.html('');
                    if (response !== 'no post has found') {
                        obj = JSON.parse(response);

                        for (i = 0; i < obj.length; i++) {
                            container.append('<span class="test-btn" id="' + obj[i].post_id + '">' + obj[i].title + '</span>');
                        }
                    } else {
                        container.append('no post has found');
                    }
                });
            } else {
                widgetContainer.find('.search-posts-list').html('');
                widgetContainer.find('.search-posts-list').html('Error search value is empty!');
            }
            e.preventDefault();
        });


        widgetContainer.find('.test-btn').click(function() {
            var parent = $j(this).parent();
            
            var wnumber  = parent.attr('data-widget-number');
            var wid     = parent.attr('data-widget-id');
            
            var ids = [];
            var post_id = $j(this).attr('id'),
                    post_title = $j(this).text(),
                    new_post = '\
                <li><span class="item-added" id="' + post_id + '">' + post_title + '\
                    <input type="hidden" name="widget-' + wid + '[' + wnumber + '][articles][]" value="' + post_id + '" />\
                    <span class="delete-post button button-default right">delete</span>\
                </span></li>';

            widgetContainer.find('.search-posts-list-selected').append(new_post);
            start_sortable(widgetId);
        });

        widgetContainer.find('.delete-post').live('click', function() {
            $j(this).parent().remove();
        });
    });
}