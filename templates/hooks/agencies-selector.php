<?php
/**
 * Swastarkencl is private software: you CANNOT redistribute it, sell it and/or 
 * modify it under or in any form without written authorization of its owner / developer.
 *
 * Swastarkencl is distributed / sell in the hope that it will be useful to you.
 *
 * You should have received a copy of its License along with Swastarkencl. 
 * If not, see https://www.softwareagil.com.
 */
?>

<div>
    <h4><?= __('Starken Agencies', 'swastarkencl') ?></h4>
    <div class="row col2-set">
        <div class="form-group">
            <select
                data-swastarkencl-selected-agency="<?= $selected_agency ?>"
                id="swastarkencl_list_of_agencies"
                style="width: 100%" class="form-control"></select>
        </div>
        <div class="col-sm-6 col-1">
            <?= __('Address:', 'swastarkencl') ?> <span id="swastarkencl_agency_address_value">--</span>
            <br />
            <?= __('Phone:', 'swastarkencl') ?> <span id="swastarkencl_agency_phone_value">--</span>
            <br />
            <?= __('Delivery:', 'swastarkencl') ?> 
            <span
                id="swastarkencl_agency_delivery_value"
                data-yes="<?= __('Yes', 'swastarkencl') ?>"
                data-no="<?= __('No', 'swastarkencl') ?>">--</span>
        </div>

        <div class="col-sm-6 col-2">
            <?= __('Weight Restrinction:', 'swastarkencl') ?> 
            <span id="swastarkencl_agency_weight_restrictions_value">--</span>
            <br />
            <a href="#" id="swastarkencl_agency_location" target="_blank">
                <?= __('Location on Google Map', 'swastarkencl') ?>
            </a>
        </div>
    </div>
</div>
