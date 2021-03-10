/**
 * Swastarkencl is private software: you CANNOT redistribute it, sell it and/or 
 * modify it under or in any form without written authorization of its owner / developer.
 *
 * Swastarkencl is distributed / sell in the hope that it will be useful to you.
 *
 * You should have received a copy of its License along with Swastarkencl. 
 * If not, see https://www.softwareagil.com.
 */

jQuery(document).ready(function($) {
    // Agencies setup
    function swastarkencl_fetch_agencies(element) {
        $.ajax({
            type : 'POST',
            dataType : 'json',
            url : swastarkencl.url,
            data : {action: swastarkencl.change_origin_commune_action, commune_id: $(element).val()},
            success: function(result) {
                $('#woocommerce_swastarkencl_origin_agency').empty();
                if (result != null && result.agencies != null && result.agencies.length > 0) {
                    $('#woocommerce_swastarkencl_origin_agency_message').remove();
                    for(i in result.agencies) {
                        if (result.agencies[i].status == "ACTIVE" && result.agencies[i].code_dls != null) {
                            var newOption = $('<option>', {
                                value: result.agencies[i].code_dls,
                                text: result.agencies[i].name 
                                    + ", " 
                                    + swastarkencl.located_at_label 
                                    + " " 
                                    + result.agencies[i].address 
                            });
                            if (parseInt($('#woocommerce_swastarkencl_origin_agency').data('swastarkencl-origin-agency')) == parseInt(result.agencies[i].code_dls)) {
                                newOption.attr('selected', 'selected');
                            }
                            $('#woocommerce_swastarkencl_origin_agency').append(newOption);
                        }
                    }
                } else {
                    if ($('#woocommerce_swastarkencl_origin_agency_message').length == 0) {
                        $('#woocommerce_swastarkencl_origin_agency').parent().find('.description').append(`
                            <div id="woocommerce_swastarkencl_origin_agency_message">
                                <span class="text-danger" style="color:red;">`
                                + swastarkencl.no_agencies_message +
                                `</span>
                            </div>
                        `);
                    }
                }
            }
        });
    }

    $('#woocommerce_swastarkencl_origin_commune').on('change', function() {
        swastarkencl_fetch_agencies(this);
    });
    swastarkencl_fetch_agencies($('#woocommerce_swastarkencl_origin_commune option:selected'));

    // Cost centers and rut setup
    function swastarkencl_fetch_cost_centers(element) {
        $.ajax({
            type : 'POST',
            dataType : 'json',
            url : swastarkencl.url,
            data : {action: swastarkencl.change_checking_account_action, ctacte_code: $(element).val()},
            success: function(result) {
                $('#woocommerce_swastarkencl_cost_center').empty();
                if (result.length > 0) {
                    for(i in result) {
                        var newOption = $('<option>', {
                            value: result[i].id,
                            text: result[i].descripcion.trim()
                        });
                        $('#woocommerce_swastarkencl_cost_center').append(newOption);
                    }
                }
            }
        });
    }

    $('#woocommerce_swastarkencl_checking_account').on('change', function() {
        $('#woocommerce_swastarkencl_rut').val($("option:selected", this).data('rut'));
        swastarkencl_fetch_cost_centers(this);
    });
    $('#woocommerce_swastarkencl_rut').val($('#woocommerce_swastarkencl_checking_account option:selected').data('rut'));
    swastarkencl_fetch_cost_centers($('#woocommerce_swastarkencl_checking_account option:selected'));

    // Show/hide checking account
    function swastarkencl_show_or_hide(action) {
        if (action) {
            $("#woocommerce_swastarkencl_checking_account").closest('tr').addClass('hidden d-none');
            $("#woocommerce_swastarkencl_cost_center").closest('tr').addClass('hidden d-none');
        } else {
            $("#woocommerce_swastarkencl_checking_account").closest('tr').removeClass('hidden d-none');
            $("#woocommerce_swastarkencl_cost_center").closest('tr').removeClass('hidden d-none');
        }
    }
    $("#woocommerce_swastarkencl_disable_checking_accounts_usage").change(function(){
        swastarkencl_show_or_hide($(this).is(':checked'));
    });
    swastarkencl_show_or_hide($("#woocommerce_swastarkencl_disable_checking_accounts_usage").is(':checked'));
});