<?php 
include 'includes/init.php'; 
include 'modals/bankModal.php';
$message = [];

// Handle form submission (add, update, delete Bank account )
if (isset($_POST['btnSave'])) {
    if (isset($_POST['action']) && $_POST['action'] == 'insert') {			
        $data = [
            "name" => trim(escape($_POST['name'])),
            "account_num" => trim(escape($_POST['accountNum'])),
            "balance" => trim(escape($_POST['balance']))
        ];

        if (insert('banks', $data)) {
            $message = ["Successfully inserted!", "success"];
        } else {
            $message = ["Sorry! Something went wrong", "danger"];
        }
    }

    

    // Update an existing bank account
    
    else if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $data = [
            "id" => trim(escape($_POST['bank_account_id'])),
            "name" => trim(escape($_POST['name'])),
            "account_num" => trim(escape($_POST['accountNum'])),
            "balance" => trim(escape($_POST['balance']))
        ];

        if (update('banks', $data)) {
            $message = ["Successfully Updated!", "success"];
        } else {
            $message = ["Sorry! Something went wrong", "danger"];
        }
    }
}



 // Delete a bank account
 if (isset($_POST['btnDelete']) && isset($_POST['account_id'])) {
    if (delete('banks', $_POST['account_id'])) {
        $message = ["Successfully Deleted!", "success"];
    } else {
        $message = ["Sorry! Something went wrong", "danger"];
    }
}



?>




<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Bank Accounts</h1>
    </div>
    <div class="card shadow mb-4">
        <div class="row alerts">
            <?php $message ? showMessage($message) : ""; ?>
        </div>
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Bank Accounts List</h6>
            <!-- Button to open the add bank modal -->
            <button class="btn btn-primary float-end " data-bs-toggle="modal" data-bs-target="#bankModal"
                onclick="reset()">Add New Bank Account</button>
        </div>


        <!-- bank List Table -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Account Number</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach (read('banks') as $account) : ?>
                        <tr>
                            <td><?= $account['id'] ?></td>
                            <td><?= $account['name'] ?></td>
                            <td><?= $account['account_num'] ?></td>
                            <td><?= $account['balance'] ?></td>

                            <td>
                                <!-- Edit button with data-passing logic -->
                                <a href="#bankModal" data-bs-toggle="modal" class="btn btn-primary"
                                    onclick="fillForm(<?= $account['id'] ?>)"> <i class="bx bx-edit-alt"></i></a>

                                <!-- Delete button -->
                                <a href="#delete-account-modal" data-bs-toggle="modal" class="btn btn-danger"
                                    onclick="setId(<?= $account['id']; ?>)"> <i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
<!-- /.container-fluid -->





<!-- JS for filling modal with data -->
<script>
function reset() {
    document.querySelector('.modal-title').textContent = 'Add Bank Account Details';
    document.getElementById('action').value = 'insert';
    document.getElementById('bank_account_id').value = "";
    document.getElementById('name').value = "";
    document.getElementById('account_num').value = "";
    document.getElementById('balance').value = "";
}

function setId(id) {
    document.getElementById('account_id').value = id;
}

function fillForm(id) {
    document.querySelector('.modal-title').textContent = 'Update Bank Account Details';
    document.getElementById('action').value = 'update';
    document.getElementById('bank_account_id').value = id;

    $.ajax({
        url: "includes/ajax.php",
        method: "post",
        data: {
            table: "banks",
            id: id,
            action: "forUpdate"
        },
        success: function(result) {
            const data = JSON.parse(result);

            document.getElementById('bank_account_id').value = data.id;
            document.getElementById("name").value = data.name;
            document.getElementById("accountNum").value = data.account_num;
            document.getElementById("balance").value = data.balance;
        },
        error: function(error) {
            console.log(error);
        }
    });
}
</script>


<?php include 'includes/footer.php'; ?>
