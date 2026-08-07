<?php 
include 'includes/init.php';
require_role(['Admin']);
include 'modals/users-modal.php';
$message = [];

if (isset($_POST['btnSignup'])) {
    if($_POST['TypeOfData'] == 'insert'){
        $data = [
            "FullName" => trim(escape($_POST['FullName'])),
            "Username" => trim(escape($_POST['Username'])),
            "Email" => trim(escape($_POST['Email'])),
            "Password" => password_hash((string) $_POST['Password'], PASSWORD_DEFAULT),
            "Role" => trim(escape($_POST['Role'])),
            // "CreatedAt" => trim(escape($_POST['CreatedAt'])),
            "Status" => trim(escape($_POST['Status'])),
        ];

        $result = insert('users', $data);
        $message = $result ? ["Successfully inserted!", "success"] : ["Sorry! Something went wrong", "danger"];

    } else if($_POST['TypeOfData'] == 'update'){
        $data = [
            "id" => trim(escape($_POST['userId'])),
            "FullName" => trim(escape($_POST['FullName'])),
            "Username" => trim(escape($_POST['Username'])),
            "Email" => trim(escape($_POST['Email'])),
            "Role" => trim(escape($_POST['Role'])),
            // "CreatedAt" => trim(escape($_POST['CreatedAt'])),
            "Status" => trim(escape($_POST['Status'])),
        ];

        if (!empty($_POST['Password'])) $data['Password'] = password_hash((string) $_POST['Password'], PASSWORD_DEFAULT);
        $result = update('users', $data);
        $message = $result ? ["Successfully updated!", "success"] : ["Sorry! Something went wrong", "danger"];
    }
}

if (isset($_POST['btnDelete']) && isset($_POST['user_id'])) {
    if (delete('users', $_POST['user_id'])) {
        $message = ["Successfully Deleted!", "success"];
    } else {
        $message = ["Sorry! Something went wrong", "danger"];
    }
}
?>

<!-- Begin Page Content -->
<div class="container">

    <!-- Page Heading -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">All Users </h1>
    </div>
    <div class="card shadow mb-4">
        <div class="row alerts">
            <?php $message ? showMessage($message) : ""; ?>
        </div>
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">User List </h6>
             <!-- Trigger Button for Registration Modal -->
    <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#register-modal" onclick="reset()">
        Open Registration Form
    </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <!-- <th>Password</th> -->
                            <th>Role</th>
                            <th>CreatedAt</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach (read('users') as $user) {
                        ?>
                        <tr>

                            <td><?= $user['FullName']; ?></td>
                            <td><?= $user['Username']; ?></td>
                            <td><?= $user['Email']; ?></td>
                            <!-- <td><?= $user['Password']; ?></td> -->
                            <td><?= $user['Role']; ?></td>
                            <td><?= $user['CreatedAt']; ?></td>
                            <td><?= $user['Status']; ?></td>

                            <td>
                                    <a href="#register-modal" data-bs-toggle="modal" class="btn btn-primary"
                                    onclick="fillForm(<?= $user['id']; ?>)"> <i class="bx bx-edit-alt "></i></a>
                                <a href="#delete-user-modal" data-bs-toggle="modal" class="btn btn-danger"
                                    onclick="setId(<?= $user['id']; ?>)"> <i class="bx bx-trash"></i></a>
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
   
function reset() {
    document.querySelector('.modal-title').textContent = 'Add User Details';
    document.getElementById('action').value = 'insert';
    document.getElementById('userId').value = 0;
    document.getElementById('FullName').value = '';
    document.getElementById('Username').value = '';
    document.getElementById('Email').value = '';
    document.getElementById('Password').value = '';
    document.getElementById('Role').value = '';
    // document.getElementById('CreatedAt').value = '';
    document.getElementById('Status').value = '';
}

function setId(id) {
    document.getElementById('user_id').value = id;
}

function fillForm(id) {
    document.querySelector('.modal-title').textContent = 'Update User Details';
    document.getElementById('action').value = 'update';
    document.getElementById('userId').value = id;

    $.ajax({
        url: "includes/ajax.php",
        method: "POST",
        data: { table: "users", id: id, action: "forUpdate" },
        success: function(result) {
            try {
                const data = JSON.parse(result);
                document.getElementById('FullName').value = data.FullName;
                document.getElementById('Username').value = data.Username;
                document.getElementById('Email').value = data.Email;
                document.getElementById('Password').value = data.Password;
                document.getElementById('Role').value = data.Role;
                // document.getElementById('CreatedAt').value = data.CreatedAt;
                document.getElementById('Status').value = data.Status;
            } catch (error) {
                console.error("JSON parse error: ", error);
            }
        },
        error: function(error) {
            console.error("AJAX error: ", error);
        }
    });
}


</script>
