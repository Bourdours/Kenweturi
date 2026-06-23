<?php
/** @var string $prefLabel */
/** @var string $unsubscribeUrl */
/** @var string $preferencesUrl */
?>
                    <!-- Unsubscribe -->
                    <tr>
                        <td style="background-color:#0d1520;padding:12px 40px;border-top:1px solid #1e2e40;">
                            <p style="margin:0;font-size:11px;color:#3d5570;line-height:1.8;text-align:center;">
                                Vous recevez cet email car vous avez activé les notifications &laquo;&nbsp;<?= esc($prefLabel) ?>&nbsp;&raquo;.<br>
                                <a href="<?= esc($unsubscribeUrl) ?>" style="color:#93b8d8;text-decoration:underline;">Se désabonner de ce type</a>
                                &nbsp;·&nbsp;
                                <a href="<?= esc($preferencesUrl) ?>" style="color:#93b8d8;text-decoration:underline;">Gérer toutes mes préférences</a>
                            </p>
                        </td>
                    </tr>
