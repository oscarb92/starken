<?php if(strtolower($issuance->status) != 'error'): ?>
    <?php if(!empty($issuance->tag)): ?>
    <h1 style="text-transform: uppercase;">
        <?= __('Print your tag', 'swastarkencl') ?>
        <a href="<?= $issuance->tag ?>" target="_blank">
            <?= __('here', 'swastarkencl') ?>
        </a>
    </h1>
    <?php endif; ?>

    <h2 style="font-size: 20pt; padding: 0px; font-weight: bolder; text-transform: uppercase;">
    <?= __('Shipping info', 'swastarkencl') ?>
    </h2>
    <h4 style="text-transform: uppercase;" title="ID de la emisión: 12983">
        <?= __('Freight order', 'swastarkencl') ?>
        <strong><?= $issuance->freight_order ?></strong>
    </h4>

    <table width="100%">
        <tbody>
            <tr>
                <td>
                    <ul style="list-style: none; padding: 0; margin: 0">
                        <?php if ($swastarkencl_tracking != null): ?>
                            <li>
                                <strong><?= __('Issuance ID', 'swastarkencl') ?></strong>
                                <br>
                                <?= $issuance->issuance_id ?>
                            </li>

                            <li>
                                <strong><?= __('Origin', 'swastarkencl') ?></strong>
                                <br>
                                <?= strtoupper($swastarkencl_tracking->origin) ?>
                            </li>

                            <li>
                                <strong><?= __('Destination', 'swastarkencl') ?></strong>
                                <br>
                                <?= strtoupper($swastarkencl_tracking->destination) ?>
                            </li>

                            <li>
                                <strong><?= __('Issuer\'s RUT', 'swastarkencl') ?></strong>
                                <br>
                                <?php
                                    $issuer_rut = '--';
                                    if (isset($swastarkencl_tracking->issuer_rut)) {
                                        $issuer_rut = str_split(str_replace('-', '', $swastarkencl_tracking->issuer_rut));
                                        $issuer_rut_last_item = array_slice($issuer_rut, -1, 1);
                                        $issuer_rut[key(array_slice($issuer_rut, -1, 1, true))] = '-';
                                        $issuer_rut[] = $issuer_rut_last_item[0];
                                        $issuer_rut = implode('', $issuer_rut);
                                    }
                                ?>
                                <?= $issuer_rut ?>
                            </li>

                            <li>
                                <strong><?= __('Issuer\'s name', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->issuer_name) ? $swastarkencl_tracking->issuer_name : '--' ?>
                            </li>

                            <li>
                                <strong><?= __('Issuer\'s email', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->issuer_email) ? $swastarkencl_tracking->issuer_email : '--' ?> 
                            </li>

                            <li>
                                <strong><?= __('Issuer\'s phone', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->issuer_phone) && !empty($swastarkencl_tracking->issuer_phone) ? $swastarkencl_tracking->issuer_phone : '--' ?> 
                            </li>

                            <li>
                                <strong><?= __('Issuer\'s mobile', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->issuer_mobile) && !empty($swastarkencl_tracking->issuer_mobile) ? $swastarkencl_tracking->issuer_mobile : '--' ?> 
                            </li>

                            <li>
                                <strong><?= __('Receiver\'s RUT', 'swastarkencl') ?></strong>
                                <br>
                                <?php
                                    $receiver_rut = '--';
                                    if (isset($swastarkencl_tracking->receiver_rut)) {
                                        $receiver_rut = str_split(str_replace('-', '', $swastarkencl_tracking->receiver_rut));
                                        $receiver_rut_last_item = array_slice($receiver_rut, -1, 1);
                                        $receiver_rut[key(array_slice($receiver_rut, -1, 1, true))] = '-';
                                        $receiver_rut[] = $receiver_rut_last_item[0];
                                        $receiver_rut = implode('', $receiver_rut);
                                    }
                                ?>
                                <?= $receiver_rut ?>
                            </li>

                            <li>
                                <strong><?= __('Receiver\'s name', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->receiver_name) ? $swastarkencl_tracking->receiver_name : '--' ?> 
                            </li>

                            <li>
                                <strong><?= __('Receiver\'s email', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->receiver_email) ? $swastarkencl_tracking->receiver_email : '--' ?> 
                            </li>

                            <li>
                                <strong><?= __('Receiver\'s phone', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->receiver_phone) ? $swastarkencl_tracking->receiver_phone : '--' ?> 
                            </li>

                            <li>
                                <strong><?= __('Receiver\'s mobile', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->receiver_mobile) ? $swastarkencl_tracking->receiver_mobile : '--' ?> 
                            </li>

                            <li>
                                <strong><?= __('Receiver\'s address', 'swastarkencl') ?></strong>
                                <br>
                                <?= isset($swastarkencl_tracking->receiver_address) ? $swastarkencl_tracking->receiver_address : '--' ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </td>
                <td>
                    <ul style="list-style: none; padding: 0; margin: 0">
                        <li title="<?= $issuance->payment_type['descripcion'] ?>">
                            <strong><?= __('Service type', 'swastarkencl') ?></strong>
                            <br>
                            <?= $issuance->service_type['nombre'] ?>
                        </li>

                        <li title="<?= $issuance->payment_type['descripcion'] ?>">
                            <strong><?= __('Payment type', 'swastarkencl') ?></strong>
                            <br>
                            <span 
                                <?php if(strtolower($issuance->payment_type['nombre']) == 'por pagar'): ?>
                                    style="color:red"
                                <?php endif; ?> >
                                <?= !empty($issuance->payment_type['nombre']) ? $issuance->payment_type['nombre'] : '--' ?>
                            </span>
                        </li>

                        <li>
                            <strong><?= __('Checking account', 'swastarkencl') ?></strong>
                            <br>
                            <?= $issuance->checking_account ?>
                        </li>

                        <li>
                            <strong><?= __('Cost center', 'swastarkencl') ?></strong>
                            <br>
                            <?= $issuance->cost_center ?>
                        </li>

                        <li>
                            <strong><?= __('Origin agency', 'swastarkencl') ?></strong>
                            <br>
                            <?= $issuance->origin_agency_code ?>
                        </li>

                        <li>
                            <strong><?= __('Origin agency address', 'swastarkencl') ?></strong>
                            <br>
                            <?= $issuance->origin_agency_address ?>
                        </li>

                        <li>
                            <strong><?= __('Shipping price', 'swastarkencl') ?></strong>
                            <br>
                            <?= $order->get_shipping_total() ?>
                        </li>

                        <li>
                            <strong><?= __('Declared value', 'swastarkencl') ?></strong>
                            <br>
                            <?= $issuance->declared_value ?>
                        </li>

                        <?php if ($swastarkencl_tracking != null): ?>
                            <li>
                                <strong><?= __('Status', 'swastarkencl') ?></strong>
                                <br>
                                <?= $swastarkencl_tracking->status ?>
                            </li>

                            <li>
                                <strong><?= __('Commitment date', 'swastarkencl') ?></strong>
                                <br>
                                <?= date("d/m/Y H:i:s", strtotime($swastarkencl_tracking->commitmen_date)) ?>
                            </li>

                            <li>
                                <strong><?= __('Created at', 'swastarkencl') ?></strong>
                                <br>
                                <?= date("d/m/Y H:i:s", strtotime($swastarkencl_tracking->created_at)) ?>
                            </li>

                            <li>
                                <strong><?= __('Updated at', 'swastarkencl') ?></strong>
                                <br>
                                <?= date("d/m/Y H:i:s", strtotime($swastarkencl_tracking->updated_at)) ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>

    <?php if ($swastarkencl_tracking != null): ?>
        <hr />

        <h3><?= __('History', 'swastarkencl') ?></h3>
        <table width="100%" class="wp-list-table">
            <thead>
                <tr>
                    <th style="text-align: left;"><?= __('Status', 'swastarkencl') ?></th>
                    <th style="text-align: left;"><?= __('Note', 'swastarkencl') ?></th>
                    <th style="text-align: left;"><?= __('Created at', 'swastarkencl') ?></th>
                    <th style="text-align: left;"><?= __('Updated at', 'swastarkencl') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($swastarkencl_tracking->history as $tracking_history): ?>
                    <tr>
                        <td><?= $tracking_history->status ?></td>
                        <td><?= $tracking_history->note ?></td>
                        <td><?= date("d/m/Y H:i:s", strtotime($tracking_history->created_at)) ?></td>
                        <td><?= date("d/m/Y H:i:s", strtotime($tracking_history->updated_at)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="notice notice-warning inline">
            <p>
                <?= __('No trace data available yet!', 'swastarkencl') ?> 
                <a onclick="return window.location.reload();" style="cursor: pointer"><?= __('Update', 'swastarkencl') ?></a>
            </p>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="notice notice-error inline">
        <strong><?= __('Error', 'swastarkencl') ?></strong><br />
        <p>
            <?= $issuance->orders ?>
            <ul>
                <li>
                    <strong><?= __('Issuance ID:', 'swastarkencl') ?></strong>
                    <?= $issuance->issuance_id ?>
                </li>
                <li>
                    <strong><?= __('Delivery type:', 'swastarkencl') ?></strong>
                    <?= $issuance->delivery_type['descripcion'] ?>
                </li>
                <li>
                    <strong><?= __('Payment type:', 'swastarkencl') ?></strong>
                    <?= $issuance->payment_type['descripcion'] ?>
                </li>
                <li>
                    <strong><?= __('Service type:', 'swastarkencl') ?></strong>
                    <?= $issuance->service_type['descripcion'] ?>
                </li>
                <li>
                    <strong><?= __('Origin agency:', 'swastarkencl') ?></strong>
                    <?= $issuance->origin_agency_code ?>
                </li>
                <li>
                    <strong><?= __('Destination agency:', 'swastarkencl') ?></strong>
                    <?= $issuance->destination_agency_code ?>
                </li>
            </ul>
        </p>
    </div>
<?php endif; ?>