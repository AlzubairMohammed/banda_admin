"use strict";

document.addEventListener("DOMContentLoaded", function () {
    let checkboxes = document.querySelectorAll(".dynamic-checkbox");
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("click", function (event) {
            event.preventDefault();
            const checkboxId = checkbox.getAttribute("data-id");
            const imageOn = checkbox.getAttribute("data-image-on");
            const imageOff = checkbox.getAttribute("data-image-off");
            const titleOn = checkbox.getAttribute("data-title-on");
            const titleOff = checkbox.getAttribute("data-title-off");
            const textOn = checkbox.getAttribute("data-text-on");
            const textOff = checkbox.getAttribute("data-text-off");

            const isChecked = checkbox.checked;

            if (isChecked) {
                $("#toggle-status-title").empty().append(titleOn);
                $("#toggle-status-message").empty().append(textOn);
                $("#toggle-status-image").attr("src", imageOn);
                $("#toggle-status-ok-button").attr(
                    "toggle-ok-button",
                    checkboxId
                );
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);

                console.log("Checkbox " + checkboxId + " is checked");
            } else {
                $("#toggle-status-title").empty().append(titleOff);
                $("#toggle-status-message").empty().append(textOff);
                $("#toggle-status-image").attr("src", imageOff);
                $("#toggle-status-ok-button").attr(
                    "toggle-ok-button",
                    checkboxId
                );
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
                console.log("Checkbox " + checkboxId + " is unchecked");
            }

            $("#toggle-status-modal").modal("show");
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let checkboxes = document.querySelectorAll(".dynamic-checkbox-toggle");
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("click", function (event) {
            event.preventDefault();
            const checkboxId = checkbox.getAttribute("data-id");
            const imageOn = checkbox.getAttribute("data-image-on");
            const imageOff = checkbox.getAttribute("data-image-off");
            const titleOn = checkbox.getAttribute("data-title-on");
            const titleOff = checkbox.getAttribute("data-title-off");
            const textOn = checkbox.getAttribute("data-text-on");
            const textOff = checkbox.getAttribute("data-text-off");

            const isChecked = checkbox.checked;

            if (isChecked) {
                $("#toggle-title").empty().append(titleOn);
                $("#toggle-message").empty().append(textOn);
                $("#toggle-image").attr("src", imageOn);
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
            } else {
                $("#toggle-title").empty().append(titleOff);
                $("#toggle-message").empty().append(textOff);
                $("#toggle-image").attr("src", imageOff);
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
            }

            $("#toggle-modal").modal("show");
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let imageData = document.querySelectorAll(".remove-image");
    imageData.forEach(function (image) {
        image.addEventListener("click", function (event) {
            event.preventDefault();
            const imageId = image.getAttribute("data-id");
            const title = image.getAttribute("data-title");
            const text = image.getAttribute("data-text");

            $("#toggle-status-title").empty().append(title);
            $("#toggle-status-message").empty().append(text);
            $("#toggle-status-ok-button").attr("toggle-ok-button", imageId);
            $("#toggle-ok-button").attr("toggle-ok-button", imageId);

            $("#toggle-status-modal").modal("show");
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const langLinks = document.querySelectorAll(".lang_link");

    langLinks.forEach(function (langLink) {
        langLink.addEventListener("click", function (e) {
            e.preventDefault();
            langLinks.forEach(function (link) {
                link.classList.remove("active");
            });
            this.classList.add("active");
            document.querySelectorAll(".lang_form").forEach(function (form) {
                form.classList.add("d-none");
            });
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);

            $("#" + lang + "-form").removeClass("d-none");
            $("#" + lang + "-form1").removeClass("d-none");
            $("#" + lang + "-form2").removeClass("d-none");
            $("#" + lang + "-form3").removeClass("d-none");
            $("#" + lang + "-form4").removeClass("d-none");
            if (lang === "default") {
                $(".default-form").removeClass("d-none");
            }
        });
    });
});

$("[data-slide]").on("click", function () {
    let serial = $(this).data("slide");
    $(`.tab--content .item`).removeClass("show");
    $(`.tab--content .item:nth-child(${serial})`).addClass("show");
});
$(document).ready(function () {
    $(".add-required-attribute").on("click", function () {
        let status = $(this).attr("id");
        let name = $(this).data("textarea-name");
        if ($("#" + status).is(":checked")) {
            $("#en-form ." + name).attr("required", true);
        } else {
            $("#en-form ." + name).removeAttr("required");
        }
    });
});

$(document).on("click", ".location-reload", function () {
    location.reload();
});
$(document).on("click", ".redirect-url", function () {
    location.href = $(this).data("url");
});

function readURL(input, viewer = "viewer") {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $("#" + viewer).attr("src", e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready(function () {
    "use strict";
    $(
        ".upload-img-3, .upload-img-4, .upload-img-2, .upload-img-5, .upload-img-1, .upload-img"
    ).each(function () {
        let targetedImage = $(this).find(".img");
        let targetedImageSrc = $(this).find(".img img");
        function proPicURL(input) {
            if (input.files && input.files[0]) {
                let uploadedFile = new FileReader();
                uploadedFile.onload = function (e) {
                    targetedImageSrc.attr("src", e.target.result);
                    targetedImage.addClass("image-loaded");
                    targetedImage.hide();
                    targetedImage.fadeIn(650);
                };
                uploadedFile.readAsDataURL(input.files[0]);
            }
        }
        $(this)
            .find("input")
            .on("change", function () {
                proPicURL(this);
            });
    });

    $(".read-url").on("change", function () {
        readUrl(this);
    });
});
$(document).on("ready", function () {
    // INITIALIZATION OF SHOW PASSWORD
    // =======================================================
    $(".js-toggle-password").each(function () {
        new HSTogglePassword(this).init();
    });

    // INITIALIZATION OF FORM VALIDATION
    // =======================================================
    $(".js-validate").each(function () {
        $.HSCore.components.HSValidation.init($(this), {
            rules: {
                confirmPassword: {
                    equalTo: "#signupSrPassword",
                },
            },
        });
    });
});

$(".route-alert").on("click", function () {
    let route = $(this).data("url");
    let message = $(this).data("message");
    let title = $(this).data("title");
    route_alert(route, message, title);
});
$(".set-filter").on("change", function () {
    const id = $(this).val();
    const url = $(this).data("url");
    const filter_by = $(this).data("filter");
    let nurl = new URL(url);
    nurl.searchParams.delete("page");
    nurl.searchParams.set(filter_by, id);
    location.href = nurl;
});
$(document).ready(function () {
    $(".onerror-image").on("error", function () {
        let img = $(this).data("onerror-image");
        $(this).attr("src", img);
    });

    $(".onerror-image").each(function () {
        let defaultImage = $(this).data("onerror-image");
        if ($(this).attr("src").endsWith("/")) {
            $(this).attr("src", defaultImage);
        }
    });
});

$(document).on("click", ".confirm-Status-Toggle", function () {
    let Status_toggle = $("#toggle-status-ok-button").attr("toggle-ok-button");
    if ($("#" + Status_toggle).is(":checked")) {
        $("#" + Status_toggle)
            .prop("checked", false)
            .val(0);
    } else {
        $("#" + Status_toggle)
            .prop("checked", true)
            .val(1);
    }
    $("#" + Status_toggle + "_form").submit();
});
$(document).on("click", ".confirm-Toggle", function () {
    let toggle_id = $("#toggle-ok-button").attr("toggle-ok-button");
    if ($("#" + toggle_id).is(":checked")) {
        $("#" + toggle_id).prop("checked", false);
    } else {
        $("#" + toggle_id).prop("checked", true);
    }
    $("#toggle-modal").modal("hide");

    // if (toggle_id === "admin_free_delivery_status") {
    //     if ($("#admin_free_delivery_status").is(":checked")) {
    //         $("#free_delivery_over").removeAttr("readonly");
    //     } else {
    //         $("#free_delivery_over").attr("readonly", true).val(null);
    //     }
    // }
    if (toggle_id === "product_gallery") {
        if ($("#product_gallery").is(":checked")) {
            $(".access_all_products").removeClass("d-none");
        } else {
            $(".access_all_products").addClass("d-none");
        }
    }
    if (toggle_id === "product_approval") {
        if ($("#product_approval").is(":checked")) {
            $("#inlineCheckbox1").removeAttr("disabled");
            $("#inlineCheckbox2").removeAttr("disabled");
            $("#inlineCheckbox3").removeAttr("disabled");
            $("#inlineCheckbox4").removeAttr("disabled");
        } else {
            $("#inlineCheckbox1").attr("disabled", true);
            $("#inlineCheckbox2").attr("disabled", true);
            $("#inlineCheckbox3").attr("disabled", true);
            $("#inlineCheckbox4").attr("disabled", true);
        }
    }
    if (toggle_id === "additional_charge_status") {
        if ($("#additional_charge_status").is(":checked")) {
            $("#additional_charge_name")
                .removeAttr("readonly")
                .attr("required", true);
            $("#additional_charge")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#additional_charge_name")
                .attr("readonly", true)
                .removeAttr("required");
            $("#additional_charge")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "cash_in_hand_overflow") {
        if ($("#cash_in_hand_overflow").is(":checked")) {
            $("#cash_in_hand_overflow_store_amount")
                .removeAttr("readonly")
                .attr("required", true);
            $("#min_amount_to_pay_store")
                .removeAttr("readonly")
                .attr("required", true);
            $("#min_amount_to_pay_dm")
                .removeAttr("readonly")
                .attr("required", true);
            $("#dm_max_cash_in_hand")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#cash_in_hand_overflow_store_amount")
                .attr("readonly", true)
                .removeAttr("required");
            $("#min_amount_to_pay_store")
                .attr("readonly", true)
                .removeAttr("required");
            $("#min_amount_to_pay_dm")
                .attr("readonly", true)
                .removeAttr("required");
            $("#dm_max_cash_in_hand")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "play-store-dm-status") {
        if ($("#play-store-dm-status").is(":checked")) {
            $("#playstore_url").removeAttr("readonly").attr("required", true);
        } else {
            $("#playstore_url").attr("readonly", true).removeAttr("required");
        }
    }
    if (toggle_id === "apple-dm-status") {
        if ($("#apple-dm-status").is(":checked")) {
            $("#apple_store_url").removeAttr("readonly").attr("required", true);
        } else {
            $("#apple_store_url").attr("readonly", true).removeAttr("required");
        }
    }
    if (toggle_id === "new_customer_discount_status") {
        if ($("#new_customer_discount_status").is(":checked")) {
            $("#new_customer_discount_amount")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_validity")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_type")
                .removeAttr("disabled")
                .attr("required", true);
            $("#new_customer_discount_validity_type")
                .removeAttr("disabled")
                .attr("required", true);
        } else {
            $("#new_customer_discount_amount")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_validity")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_type")
                .attr("disabled", true)
                .removeAttr("required");
            $("#new_customer_discount_validity_type")
                .attr("disabled", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "customer_loyalty_point") {
        if ($("#customer_loyalty_point").is(":checked")) {
            $("#loyalty_point_exchange_rate")
                .removeAttr("readonly")
                .attr("required", true);
            $("#item_purchase_point")
                .removeAttr("readonly")
                .attr("required", true);
            $("#minimum_transfer_point")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#loyalty_point_exchange_rate")
                .attr("readonly", true)
                .removeAttr("required");
            $("#item_purchase_point")
                .attr("readonly", true)
                .removeAttr("required");
            $("#minimum_transfer_point")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "wallet_status") {
        if ($("#wallet_status").is(":checked")) {
            $(".text-muted").removeClass("text-muted");
            $("#new_customer_discount_status").removeAttr("disabled");
            $("#add_fund_status").removeAttr("disabled");
            $("#ref_earning_status").removeAttr("disabled");
            $("#refund_to_wallet").removeAttr("disabled");

            $("#ref_earning_exchange_rate")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_validity")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_type")
                .removeAttr("disabled")
                .attr("required", true);
            $("#new_customer_discount_validity_type")
                .removeAttr("disabled")
                .attr("required", true);
        } else {
            $("#new_customer_discount_status")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");
            $("#add_fund_status")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");
            $("#ref_earning_status")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");
            $("#refund_to_wallet")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");

            $("#ref_earning_exchange_rate")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_validity")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_type")
                .attr("disabled", true)
                .removeAttr("required");
            $("#new_customer_discount_validity_type")
                .attr("disabled", true)
                .removeAttr("required");
        }
    }

    if (toggle_id === "extra_packaging_status") {
        if ($("#extra_packaging_status").is(":checked")) {
            $("#extra_packaging_amount")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#extra_packaging_amount")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "order_cancelation_rate_limit_status") {
        if ($("#order_cancelation_rate_limit_status").is(":checked")) {
            $("#order_cancelation_rate_block_limit").removeAttr("readonly").attr("required", true);
            $("#order_cancelation_rate_warning_limit").removeAttr("readonly").attr("required", true);
        } else {
            $("#order_cancelation_rate_block_limit").attr("readonly", true).removeAttr("required");
            $("#order_cancelation_rate_warning_limit").attr("readonly", true).removeAttr("required");
        }
    }

    if (toggle_id === "admin_free_delivery_status") {


        if ($("#admin_free_delivery_status").is(":checked")) {
            $('.add_text_mute').removeClass('text-muted').addClass('text-dark');

            document.querySelectorAll('input[name="admin_free_delivery_option"]').forEach(input => {
                input.removeAttribute('disabled');
                input.classList.remove('radio-disable-bg');
            });
            $("#free_delivery_over").removeAttr("readonly")
        } else {
            document.querySelectorAll('input[name="admin_free_delivery_option"]').forEach(input => {
                if (input.checked) {
                    input.classList.add('radio-disable-bg');
                }
                input.setAttribute('disabled', true);
            });
                $('.add_text_mute').addClass('text-muted').removeClass('text-dark');

                $("#free_delivery_over").attr("readonly" , true).removeAttr("required");
        }
    }


});

$(document).on("click", ".location-reload-to-base", function () {
    const url = $(this).data("url");
    let nurl = new URL(url);
    nurl.searchParams.delete("search");
    location.href = nurl;
});
document.querySelectorAll('[name="search"]').forEach(function (element) {
    element.addEventListener("input", function (event) {
        if (this.value === "" && window.location.search !== "") {
            let baseUrl = window.location.origin + window.location.pathname;
            window.location.href = baseUrl;
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const activeLink = document.querySelector(".nav-link.active");

    if (activeLink) {
        activeLink.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
            inline: "center",
        });
    }
});


document.addEventListener('DOMContentLoaded', function () {
    let modalData = document.querySelectorAll('.new-dynamic-submit-model');
    modalData.forEach(function (data) {
        data.addEventListener('click', function (event) {
            event.preventDefault();
            const dataId = data.getAttribute('data-id');
            const title = data.getAttribute('data-title');
            const text = data.getAttribute('data-text');
            const image = data.getAttribute('data-image');
            const type = data.getAttribute('data-type');
            const btn_class = data.getAttribute('data-btn_class');
            const cancel_btn_text = data.getAttribute('data-2nd_btn_text');
            const success_btn_text = data.getAttribute('data-success_btn_text');



            $('#get-text-note').val('');
            $('#modal-title').empty().append(title);
            $('#modal-text').empty().append(text);
            $('#image-src').attr('src',image);
            $('#new-dynamic-submit-model').modal('show');
            $('#new-dynamic-ok-button').addClass('btn-primary');
            $('#new-dynamic-ok-button-show').addClass('d-none');
            $('#hide-buttons').addClass('d-none');


            if(type === 'delete'){
                $('#new-dynamic-ok-button').attr('toggle-ok-button', dataId);
                $('#note-data').addClass('d-none');
                $('#hide-buttons').removeClass('d-none');
                }
            else if(type === 'pause'){
                $('#new-dynamic-ok-button').attr('toggle-ok-button', dataId);
                $('#hide-buttons').removeClass('d-none');
                $('#note-data').removeClass('d-none');
                $('#get-text-note').attr('get-text-note-id', dataId);
                }
            else if(type === 'deny'){
                $('#new-dynamic-ok-button').attr('toggle-ok-button', dataId);
                $('#hide-buttons').removeClass('d-none');
                $('#note-data').removeClass('d-none');
                $('#get-text-note').attr('get-text-note-id', dataId);
                $('#new-dynamic-ok-button').removeClass('btn-primary').addClass(btn_class);
                $('#cancel_btn_text').text(cancel_btn_text);

                }
            else if(type === 'resume'){
                $('#new-dynamic-ok-button').attr('toggle-ok-button', dataId);
                $('#hide-buttons').removeClass('d-none');
                $('#note-data').addClass('d-none');
                $('#new-dynamic-ok-button').removeClass('btn-primary').addClass(btn_class);
                    if(success_btn_text){
                        $('#new-dynamic-ok-button').text(success_btn_text);
                    }
                }
            else{
                $('#note-data').addClass('d-none');
                $('#hide-buttons').addClass('d-none');
                $('#new-dynamic-ok-button-show').removeClass('d-none');
            }
        });
    });
});

$(document).on('click', '.confirm-model', function () {
    let Status_toggle = $('#new-dynamic-ok-button').attr('toggle-ok-button');
    $('#'+Status_toggle+'_form').submit();
});
$(document).on('keyup', '#get-text-note', function () {
    let text_data = $('#get-text-note').attr('get-text-note-id');
    $('#'+text_data+'_note').val($(this).val());
});

$(document).ready(function(){
    $(function () {

        $('.suggestion_dropdown .dropdown-item').on('click', function(){
            let input =$(this).closest('.suggestion_dropdown').children('input')
            input.val($(this).text())
            $(this).closest('.dropdown-menu').removeClass('show')
            input.focus()
        })

        $(".suggestion_dropdown")
            .find(".form-control")
            .on("input", function () {
                let search = $(this).val().toLowerCase();
                let dropdown = $(this).closest('.suggestion_dropdown').find(".dropdown-menu");
                dropdown.find(".dropdown-item").each(function () {
                    let text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(search));
                });
                dropdown.addClass('show');

                if(!dropdown.find(".dropdown-item:visible").length ){
                    dropdown.removeClass('show')
                }
            });

        $(".suggestion_dropdown").find(".form-control").on("click", function (e) {
            e.preventDefault()
            let dropdown = $(this).closest('.suggestion_dropdown').find(".dropdown-menu");
            dropdown.addClass('show');
            if(dropdown.find(".dropdown-item:visible").length == 0){
                dropdown.removeClass('show')
            }
        });

        $(".suggestion_dropdown").find(".form-control").on("blur", function (e) {
            const timer = setTimeout(() => {
                $(this).closest('.suggestion_dropdown').find(".dropdown-menu").removeClass('show')
            } , 200)
            return () => clearTimeout(timer)
        });

    });
})
$(document).ready(function () {
    $('form').on('submit', function (event) {
        const $submitButton = $(this).find('button[type="submit"]');
        $submitButton.prop('disabled', true);
        setTimeout(() => {
        $submitButton.prop('disabled', false);
        }, 2000);
    });
});
