"use strict";
$(document).on('ready', function() {
    // INITIALIZATION OF NAV SCROLLER
    // =======================================================
    $('.js-nav-scroller').each(function() {
        new HsNavScroller($(this)).init()
    });

    // INITIALIZATION OF SELECT2
    // =======================================================
    $('.js-select2-custom').each(function() {
        var select2 = $.HSCore.components.HSSelect2.init($(this));
    });


    // INITIALIZATION OF DATATABLES
    // =======================================================
    var datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
        dom: 'Bfrtip',
        buttons: [{
            extend: 'copy',
            className: 'd-none'
        },
            {
                extend: 'print',
                className: 'd-none'
            },
        ],
        select: {
            style: 'multi',
            selector: 'td:first-child input[type="checkbox"]',
            classMap: {
                checkAll: '#datatableCheckAll',
                counter: '#datatableCounter',
                counterInfo: '#datatableCounterInfo'
            }
        },
    });

    $('#export-copy').click(function() {
        datatable.button('.buttons-copy').trigger()
    });

    $('#export-excel').click(function() {
        datatable.button('.buttons-excel').trigger()
    });

    $('#export-csv').click(function() {
        datatable.button('.buttons-csv').trigger()
    });

    $('#export-pdf').click(function() {
        datatable.button('.buttons-pdf').trigger()
    });

    $('#export-print').click(function() {
        datatable.button('.buttons-print').trigger()
    });

    $('#datatableSearch').on('mouseup', function(e) {
        var $input = $(this),
            oldValue = $input.val();

        if (oldValue == "") return;

        setTimeout(function() {
            var newValue = $input.val();

            if (newValue == "") {
                // Gotcha
                datatable.search('').draw();
            }
        }, 1);
    });

    $('#toggleColumn_name').change(function(e) {
        datatable.columns(1).visible(e.target.checked)
    })

    $('#toggleColumn_email').change(function(e) {
        datatable.columns(2).visible(e.target.checked)
    })

    $('#toggleColumn_total_order').change(function(e) {
        datatable.columns(3).visible(e.target.checked)
    })


    $('#toggleColumn_status').change(function(e) {
        datatable.columns(4).visible(e.target.checked)
    })

    $('#toggleColumn_actions').change(function(e) {
        datatable.columns(5).visible(e.target.checked)
    })

    // INITIALIZATION OF TAGIFY
    // =======================================================
    $('.js-tagify').each(function() {
        var tagify = $.HSCore.components.HSTagify.init($(this));
    });
});
var forms = document.querySelectorAll('.priority-form');

forms.forEach(function(form) {
    var select = form.querySelector('.priority-select');

    select.addEventListener('change', function() {
        form.submit();
    });
});
