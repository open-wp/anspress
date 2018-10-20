(function($){
    AnsPress = AnsPress||{};
    AnsPress.theme = {
        events: {
            'click [data-toggleclassof]': 'toggleClassOf',
            'change [name="questionFilters"]': 'questionFilters',
            'click [data-removefilter]': 'removeFilter'
        },

        bindEvents: function() {
            $.each(AnsPress.theme.events, function(event, fn){

                event = event.split(' ');
                if(event.length<2)
                    return console.log('AnsPress: Selector missing for ' + event[0]);

                $('body').on( event[0], event[1], AnsPress.theme[fn] );
            })
        },
        toggleClassOf: function(e) {
            e.preventDefault();
            var elm = $($(this).attr('data-toggleclassof'));
            var klass = $(this).attr('data-classtotoggle');
            elm.toggleClass(klass);
        },
        questionFilters: function(){
            $(this).submit();
        },
        removeFilter: function(e){
            e.preventDefault();
            var removefilter = $(this).attr('data-removefilter');
            $('[name='+removefilter+']').val('');
            $(this).closest('form').submit();
            $(this).remove();
        }
    }

    $(document).ready(function () {
        AnsPress.theme.bindEvents();

        $('textarea.autogrow, textarea#post_content').autogrow({
            onInitialize: true
        });

        $('.ap-categories-list li .ap-icon-arrow-down').click(function(e) {
            e.preventDefault();
            $(this).parent().next().slideToggle(200);
        });


        $('.ap-radio-btn').click(function() {
            $(this).toggleClass('active');
        });

        $('.bootstrap-tagsinput > input').keyup(function(event) {
            $(this).css(width, 'auto');
        });

        $('.ap-label-form-item').click(function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            var hidden = $(this).find('input[type="hidden"]');
            hidden.val(hidden.val() == '' ? $(this).data('label') : '');
        });

    });

    $('[ap-loadmore]').click(function(e){
        e.preventDefault();
        var self = this;
        var args = JSON.parse($(this).attr('ap-loadmore'));

        if(typeof args.action === 'undefined')
            args.action = 'bp_loadmore';

        AnsPress.showLoading(this);
        AnsPress.ajax({
            data: args,
            success: function(data){
                AnsPress.hideLoading(self);
                console.log(data.element);
                if(data.success){
                    $(data.element).append(data.html);
                    $(self).attr('ap-loadmore', JSON.stringify(data.args));
                    if(!data.args.current){
                        $(self).hide();
                    }
                }
            }
        });
    });

})(jQuery);


