<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF QR Cliente <?= $customer->display_name ?></title>
</head>

<body>
    <table style="width: 100%;">
        <tr>
            <td align="center" style="padding: 8px;">
                <img
                    src="data:image/svg+xml;base64,<?= base64_encode(jet_engine_get_qr_code($customer->user_email)) ?>">
            </td>
        </tr>
        <tr>
            <td align="center" style="padding: 8px;">
                <?= $customer->user_email ?>
            </td>
        </tr>
    </table>
</body>

</html>