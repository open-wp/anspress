(function($){
    AnsPress = AnsPress||{};
    AnsPress.theme = {
        events: {
            'click [data-toggleclassof]'     : 'toggleClassOf',
            'change [ap="submitOnChange"]'   : 'autoSubmitForm',
            'submit [apDisableEmptyFields]'  : 'disableEmptyFields',
            'click [ap="removeQFilter"]'     : 'removeFilter',
            'click [ap="toggleAnswer"]'      : 'toggleAnswer',
            'click [ap="loadMoreActivities"]': 'loadedMoreActivities'
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
        autoSubmitForm: function(){
            $(this).submit();
        },
        disableEmptyFields: function(e){
            $(this).find(':input').filter(function(){
                return !this.value || '0' == this.value;
            }).prop('disabled', true);
        },
        removeFilter: function(e){
            e.preventDefault();
            var removefilter = $(this).attr('data-name');
            $('[name='+removefilter+']').val('');
            $(this).closest('form').submit();
            $(this).remove();
        },
        toggleAnswer: function(e){
			e.preventDefault();
			var self = this;
			var q = $.parseJSON($(e.target).attr('apquery'));
			q.action = 'ap_toggle_best_answer';

			AnsPress.showLoading(e.target);
			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					if(data.success){
						location.reload();
					}
				}
			});
        },
        loadedMoreActivities: function(e){
            e.preventDefault();
            var query = JSON.parse($(this).attr('apquery'));

            AnsPress.showLoading(this);
            AnsPress.ajax({
                data: query,
                success: function(data){
                    AnsPress.hideLoading(e.target);
                    $(e.target).remove();
                    $('.ap-overview-activities').append(data.html);
                }
            })
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


