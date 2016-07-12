var Draggabilly = require('draggabilly');
var DragToOrderView = Backbone.View.extend({
    endPoint: null,
    parentId: null,
    initialize: function(options) {
        var that = this;
        var $el = $(this.el);
        that.endPoint = options.endPoint;
        that.parentId = options.parentId;

        $.notify.defaults({
            autoHideDelay: 2500,
            className: 'info',
            position: 'top',
            showAnimation: 'fadeIn',
            hideAnimation: 'fadeOut'
        });

        $el.packery({
            itemSelector: '.card',
            gutter: 5,
            percentPosition: true
        });

        $el.find('.card').each(function(i,e){
            var draggie = new Draggabilly(e, {
                containment: $(e).parent()[0],
                handle: '.handle'
            });
            $el.packery( 'bindDraggabillyEvents', draggie );
        });
        //
        $el.imagesLoaded().progress( function() {
            $el.packery();
        });

        $el.on( 'dragItemPositioned', function(e){
            that.orderItems(
                $el.packery('getItemElements'));
        });

    },
    orderItems: function(items) {
        var that = this;
        var $el = $(this.el);

        data = {};
        data[that.parentId] = $el.data('id');
        data['items'] = [];

        $(items).each(function(i,e){
            var $e = $(e);
            if($e.data('id')!="") {
                data['items'][$e.data('id')] = i;
            }
        });

        // make the request
        $.ajax({
            method: 'POST',
            url: that.endPoint,
            data: data
        })
        .success(function(data){
            $el.notify('Saved order.', {
                className: 'success'});
        })
        .error(function(data){
            $el.notify('Error saving order.', {
                className: 'error'});
        });
    }

});

module.exports = DragToOrderView;
