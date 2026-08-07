<?php 
include 'includes/init.php'; 
include 'modals/charge-modal.php';
$message = [];

// Check if the charge button was pressed
if (isset($_POST['btnCharge'])) {
    // Retrieve the member ID from the form
    $member_id = $_POST['member_id'];
    
    // Charge the selected member
    if (chargeMember($member_id)) {
        // $message = ["Successfully Charged!", "success"];
    } else {
        // $message = ["Something Went Wrong!", "danger"];
    }
}

?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">All Charges</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="row alerts">
            <?php $message ? showMessage($message) : ""; ?>
        </div>
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Charges List </h6>
            <button class="btn btn-primary float-end" href="#charge-modal" data-bs-toggle="modal" onclick="reset()">
                Charge Member
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Phone</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Remark</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach (read('charges') as $charge) {
                        ?>
                            <tr>
                                <td><?= read_column('members', "FullName", $charge['member_id']); ?></td>
                                <td><?= read_column('members', "Phone", $charge['member_id']); ?></td>
                                <td><?= read_column('users', "FullName", $charge['user_id']); ?></td>
                                <td><?= money($charge['Price']); ?></td>
                                <td><?= $charge['date']; ?></td>
                                <td><?= $charge['remarks']; ?></td>
                                <td><?= getStatus($charge['status']); ?></td>
                                <td>
                                    <!-- Example action to charge a member -->
                                    <form method="POST" action="">
                                        <input type="hidden" name="member_id" value="<?= $charge['member_id']; ?>">
                                        <button type="submit" name="btnCharge" class="btn btn-primary btn-sm">Charge</button>
                                    </form>
                                </td>
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
// Optional JavaScript to reset any form data, if necessary
function reset() {
    // Reset the form fields or perform other actions
}
</script>

<!-- Modal for Charge Member -->
<div class="modal fade" id="charge-modal" tabindex="-1" aria-labelledby="chargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chargeModalLabel">Charge Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Charge member form -->
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="member_id" class="form-label">Member ID</label>
                        <input type="text" class="form-control" id="member_id" name="member_id" required>
                    </div>
                    <button type="submit" name="btnCharge" class="btn btn-primary">Charge Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
     function removeAlert(){
        setTimeout(() => {
            document.querySelector('.alerts').remove()
        }, 3000);
    }
</script>
