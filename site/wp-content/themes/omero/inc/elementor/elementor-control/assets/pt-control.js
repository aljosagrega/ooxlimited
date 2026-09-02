(function ($, elementor,elementorCommon) {

    ControlSelect2CPT = elementor.modules.controls.Select2.extend({
        cache: null,
        isTitlesReceived: false,

        getSelect2DefaultOptions: function getSelect2DefaultOptions() {
            const $pt = this.$el.find('.elementor-select2').data('pt') ?? 'post';
            return jQuery.extend(elementor.modules.controls.Select2.prototype.getSelect2DefaultOptions.apply(this, arguments), {
                ajax: {
                    transport: function transport(params, success, failure) {

                        var data = {},
                            action = 'omero_panel_posts_control_filter';

                        data.q = params.data.q;
                        data.type = $pt;
                        return elementorCommon.ajax.addRequest(action, {
                            data: data,
                            success: success,
                            error: failure
                        });
                    },
                    data: function data(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    cache: true
                },
                escapeMarkup: function escapeMarkup(markup) {
                    return markup;
                },
                minimumInputLength: 1
            });
        },
        getValueTitles: function getValueTitles() {
            var self = this,
                ids = this.getControlValue(),
                action = 'omero_query_control_value',
                data = {
                    unique_id: this.cid
                };

            if (!_.isArray(ids)) {
                ids = [ids];
            }

            elementorCommon.ajax.loadObjects({
                action: action,
                ids: ids,
                data: data,
                before: function before() {
                    self.addControlSpinner();
                },
                success: function success(ajaxData) {
                    self.isTitlesReceived = true;
                    self.model.set('options', ajaxData);
                    self.render();
                }
            });
        },
        addControlSpinner: function addControlSpinner() {
            this.ui.select.prop('disabled', true);
            this.$el.find('.elementor-control-title').after('<span class="elementor-control-spinner">&nbsp;<i class="eicon-spinner eicon-animation-spin"></i>&nbsp;</span>');
        },
        onReady: function onReady() {
            if (!this.isTitlesReceived) {
                this.getValueTitles();
            }
        }
    });

    elementor.addControlView('product', ControlSelect2CPT);
    elementor.addControlView('service', ControlSelect2CPT);
    elementor.addControlView('team', ControlSelect2CPT);
    elementor.addControlView('game', ControlSelect2CPT);

})(jQuery, window.elementor,window.elementorCommon);