"use strict";
$(document).ready(function() {
    let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));
});

$('#reset-btn').on('click', function(){

    $('.check--item-wrapper .check-item .form-check-input').attr('checked', false)
})
$('#select-all').on('change', function(){
    if(this.checked === true) {
        $('.check--item-wrapper .check-item .form-check-input').attr('checked', true)
    } else {
        $('.check--item-wrapper .check-item .form-check-input').attr('checked', false)
    }
})
$('.check--item-wrapper .check-item .form-check-input').on('change', function(){
    if(this.checked === true) {
        $(this).attr('checked', true)
    } else {
        $(this).attr('checked', false)
    }
})
