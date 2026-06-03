(function ($) {
    function initTaxonomyOrder() {
        if (typeof tmplTaxonomyOrder === 'undefined') {
            return;
        }

        var $tableBody = $('#the-list');

        if (!$tableBody.length) {
            return;
        }

        $tableBody.sortable({
            axis: 'y',
            cursor: 'move',
            items: '> tr:not(.inline-edit-row):not(.no-items)',
            handle: '.row-title, .name',
            helper: function (event, ui) {
                ui.children().each(function () {
                    $(this).width($(this).width());
                });

                return ui;
            },
            update: function () {
                var order = $tableBody
                    .children('tr')
                    .map(function () {
                        var id = this.id || '';

                        if (!id.match(/^tag-/)) {
                            return null;
                        }

                        return id.replace('tag-', '');
                    })
                    .get()
                    .filter(Boolean);

                $.post(tmplTaxonomyOrder.ajaxUrl, {
                    action: tmplTaxonomyOrder.action,
                    nonce: tmplTaxonomyOrder.nonce,
                    taxonomy: tmplTaxonomyOrder.taxonomy,
                    order: order
                });
            }
        });

        $tableBody.addClass('tmpl-taxonomy-order-ready');
    }

    $(initTaxonomyOrder);
})(jQuery);
