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

<div class="row">
    <div class="col-sm-12">
        <h4><?= __('Starken Agencies', 'swastarkencl') ?></h4>
        <select id="swastarkencl_simple_list_of_agencies" style="width: 100%" class="form-control">
            <?php if(is_array($agencies)): ?>
                <?php foreach($agencies as $agency): ?>
                    <option
                        value="<?= $agency->code_dls ?>"
                        <?php if($agency->code_dls == $selected_agency): ?>selected="selected"<?php endif; ?>>
                        <?= $agency->name ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
</div>
