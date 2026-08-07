<?php 
include 'includes/init.php'; 
include 'modals/charge-income-modal.php'; 

$message = [];

if (isset($_POST['btnSave'])) {
    $memberId = (int) ($_POST['member'] ?? 0);
    $chargeId = (int) ($_POST['charge'] ?? 0);
    $bankId = (int) ($_POST['bank'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    try {
        $conn->beginTransaction();
        $chargeStmt = $conn->prepare("SELECT Price, status FROM charges WHERE id=? AND member_id=? FOR UPDATE");
        $chargeStmt->execute([$chargeId, $memberId]);
        $charge = $chargeStmt->fetch();
        if (!$charge || $charge['status'] === 'Void') throw new RuntimeException('Invalid charge selected.');
        $paidStmt = $conn->prepare('SELECT COALESCE(SUM(amount),0) FROM payments WHERE charge_id=?');
        $paidStmt->execute([$chargeId]);
        $alreadyPaid = (float) $paidStmt->fetchColumn();
        $remaining = (float) $charge['Price'] - $alreadyPaid;
        if ($amount <= 0 || $amount > $remaining) throw new RuntimeException('Payment must be greater than zero and not exceed the outstanding balance.');
        $receipt = 'RCP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $conn->prepare('INSERT INTO payments(receipt_no,member_id,charge_id,user_id,amount,bank_id) VALUES(?,?,?,?,?,?)');
        $stmt->execute([$receipt,$memberId,$chargeId,$_SESSION['userId'],$amount,$bankId]);
        $conn->prepare('UPDATE banks SET balance=balance+? WHERE id=?')->execute([$amount,$bankId]);
        $newPaid = $alreadyPaid + $amount;
        $status = $newPaid >= (float)$charge['Price'] ? 'Paid' : 'Partially Paid';
        $conn->prepare('UPDATE charges SET status=? WHERE id=?')->execute([$status,$chargeId]);
        audit('create','payment',(int)$conn->lastInsertId(),['receipt_no'=>$receipt,'amount'=>$amount]);
        $conn->commit();
        $message = ["Payment recorded. Receipt: {$receipt}", "success"];
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $message = [$e->getMessage(), "danger"];
    }
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">All Charge Income</h1>
    </div>
    <div class="card shadow mb-4">
        <div class="row alerts">
            <?php if (!empty($message)) { showMessage($message); } ?>
        </div>
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Charges List</h6>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#charge-income-modal">
                New Charge Income
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Charge ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Banks</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (read('payments') as $payment) { ?>
                            <tr>
                                <td><?= read_column('members', "FullName", $payment['member_id']); ?></td>
                                <td><?= $payment['charge_id']; ?></td>
                                <td><?= read_column('users', "FullName", $payment['user_id']); ?></td>
                                <td><?= $payment['amount']; ?></td>
                                <td><?= $payment['PaymentDate']; ?></td>
                                <td><?= read_column('banks', "name", $payment['bank_id']); ?></td>
                                <td><?= escape($payment['receipt_no'] ?? '—'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<?php include 'includes/footer.php'; ?>

<script>

    $("#member").change(function () {
        const member = $('#member').val(); 
        console.log(member);
        
        $("#charge").empty();

        $.ajax({
            url: "includes/ajax.php",
            method: "post",
            data: {
                table: "charges",
                memberid: member,
                action: "forPayment"
            },
            success: function (result) {
                console.log(result);
                const data = JSON.parse(result);
                let options = '';

                $.each(data, (i, row) => {
                    options += `<option value=${row.id} title=${row.Price}> ${row.Type + " - $" + row.Price} </option>`;
                });

                $('#charge').html('<option value="" selected disabled> Select Charge to Pay </option>');
                $("#charge").append(options);

                $("#charge").change(() => {
                    let selectedOption = $('#charge option:selected'); 
                    let chargeTitle = selectedOption.attr('title'); 
                    $("#amount").val(Number(chargeTitle)); 
                    $("#amount").prop('readonly', true); 
                });
            },
            error: function (error) {
                console.error("An error occurred: ", error);
            }
        });
    });

    

</script>
