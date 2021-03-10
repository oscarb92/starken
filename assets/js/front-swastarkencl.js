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
    function swastarkencl_save_agency(agency, rut, state_id) {
        var swastarkencl_customer_prev_rut = null;
        if ($('#billing_rut').attr('data-swastarkencl-prev-rut') != '') {
            swastarkencl_customer_prev_rut = $('#billing_rut').attr('data-swastarkencl-prev-rut');
        } else {
            swastarkencl_customer_prev_rut = $('#shipping_rut').attr('data-swastarkencl-prev-rut');
        }

        $.ajax({
            type : "POST",
            dataType : "json",
            url : swastarkencl.url,
            data : {
                action: swastarkencl.change_agency_action,
                agency_id: agency,
                nonce: swastarkencl.nonce,
                customer_rut: rut,
                customer_prev_rut: swastarkencl_customer_prev_rut,
                state_id: state_id
            },
            success: function(response) {
                console.log(response);
            }
        });
    };

    function swastarkencl_disable_place_order_button() {
        $('#place_order').prop('disabled', true);
        $('#place_order').css('cursor', 'not-allowed');
    }

    function swastarkencl_enable_place_order_button() {
        $('#place_order').css('cursor', 'pointer');
        $('#place_order').prop('disabled', false);
    }

    function swastarkencl_show_agency_details(agency_dls_code, agencies) {
        if (agencies != null && agencies.length > 0) {
            
            for(i in agencies) {
                if (agencies[i].code_dls == agency_dls_code) {
                    $('#swastarkencl_agency_location').attr(
                        'href',
                        'https://www.google.com/maps/@'+agencies[i].latitude+','+agencies[i].longitude+',18z'
                    )
                    $('#swastarkencl_agency_address_value').html(agencies[i].address);

                    if (agencies[i].phone != '') {
                        $('#swastarkencl_agency_phone_value').html(agencies[i].phone);
                    }

                    $('#swastarkencl_agency_delivery_value').html(
                        agencies[i].delivery
                            ? $('#swastarkencl_agency_delivery_value').data('yes')
                            : $('#swastarkencl_agency_delivery_value').data('no')
                    );
                    
                    $('#swastarkencl_agency_weight_restrictions_value').html(agencies[i].weight_restriction);
                }
            }
        }
    };

    $(document).on('change', '.woocommerce-shipping-methods input[type=radio][name="shipping_method[0]"]', function() {
        const agency_needed = (
            $(this).val().toString().toLowerCase().split('-')[1] == 'agencia'
            || $(this).val().toString().toLowerCase().split('-')[1] == 'sucursal'
        );
        if (agency_needed && $('#swastarkencl_list_of_agencies option').length == 0) {
            swastarkencl_disable_place_order_button();
        } else {
            swastarkencl_enable_place_order_button();
        }
    });

    $(document).on('DOMSubtreeModified', '#order_review', function() {
        var selected_shipping_option = $('.woocommerce-shipping-methods input[type=radio]:checked');
        if (selected_shipping_option.length > 0) {
            const agency_needed = (
                selected_shipping_option.val().toString().toLowerCase().split('-')[1] == 'agencia'
                || selected_shipping_option.val().toString().toLowerCase().split('-')[1] == 'sucursal'
            );
            if (agency_needed && $('#swastarkencl_list_of_agencies option').length == 0) {
                swastarkencl_disable_place_order_button();
            } else {
                swastarkencl_enable_place_order_button();
            }
        } else if (selected_shipping_option.length > 0) {
            swastarkencl_enable_place_order_button();
        }
    });

    $('#billing_rut, #shipping_rut').on('focus', function(){
        $(this).attr('data-swastarkencl-prev-rut', $(this).val());
    });

    function swastarkencl_get_list_of_agencies(state_id) {
        var params = {action: swastarkencl.commune_agencies_from_api_action, nonce: swastarkencl.nonce};
        if (state_id !== undefined) {
            params.state_id = state_id;
        }
        $.ajax({
            type : "POST",
            dataType : "json",
            url : swastarkencl.url,
            data : params,
            success: function(response) {
                $('#swastarkencl_list_of_agencies').empty();
                if (response != null && response.length > 0) {
                    for(i in response) {
                        if (response[i].status == "ACTIVE" && response[i].code_dls != null) {
                            var newOption = $('<option>', {
                                value: response[i].code_dls,
                                text: response[i].name
                            });
                            if ($('#swastarkencl_list_of_agencies').attr('data-swastarkencl-selected-agency') == response[i].code_dls) {
                                $(newOption).attr('selected', 'selected');
                            }
                            $('#swastarkencl_list_of_agencies').append(newOption);
                        }
                    }
                }
            }
        }).done(function(response) {
            $(
                '#swastarkencl_list_of_agencies, #billing_rut, #shipping_rut, #billing_state, #shipping_state'
            ).on('change', function(){
                swastarkencl_show_agency_details($(this).val(), response);
                if ($('#billing_rut').length > 0 || $('#shipping_rut').length >0) {
                    var swastarkencl_state_id = null;
                    if ($('#billing_state option:selected').val() != '') {
                        swastarkencl_state_id = $('#billing_state option:selected').val();
                    } else {
                        swastarkencl_state_id = $('#shipping_state option:selected').val();
                    }
                    swastarkencl_save_agency(
                        $('#swastarkencl_list_of_agencies option:selected').val(),
                        $('#billing_rut').val() != '' ? $('#billing_rut').val() : $('#shipping_rut').val(),
                        swastarkencl_state_id
                    );
                }
            });

            swastarkencl_show_agency_details($('#swastarkencl_list_of_agencies option:selected').val(), response);

            // TODO: take care of shipping/billing rut field change
            if ($('#billing_rut').length > 0 || $('#shipping_rut').length >0) {
                var swastarkencl_state_id = null;
                if ($('#billing_state option:selected').val() != '') {
                    swastarkencl_state_id = $('#billing_state option:selected').val();
                } else {
                    swastarkencl_state_id = $('#shipping_state option:selected').val();
                }
                swastarkencl_save_agency(
                    $('#swastarkencl_list_of_agencies option:selected').val(),
                    $('#billing_rut').val() != '' ? $('#billing_rut').val() : $('#shipping_rut').val(),
                    swastarkencl_state_id
                );
            }

            if ($('.cart_totals #swastarkencl_simple_list_of_agencies').length > 0) {
                $('#swastarkencl_simple_list_of_agencies').on('change', function(){
                    swastarkencl_save_agency(
                        $('#swastarkencl_simple_list_of_agencies option:selected').val(),
                        '',
                        $('#calc_shipping_state option:selected').val()
                    );
                });
                swastarkencl_save_agency(
                    $('#swastarkencl_simple_list_of_agencies option:selected').val(),
                    '',
                    $('#calc_shipping_state option:selected').val()
                );
            }
        });
    }

    $(document.body).on(
        "change",
        '#billing_state, #calc_shipping_state, #shipping_state',
        function() {
            swastarkencl_get_list_of_agencies($(this).val());
        }
    );
});