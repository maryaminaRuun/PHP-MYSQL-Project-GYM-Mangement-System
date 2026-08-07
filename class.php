<?php 
include 'includes/init.php'; 
include 'Modals/classModal.php';
$message = [];

// Handle form submission (add, update, delete class)
if (isset($_POST['btnSave'])) {
    if (isset($_POST['action']) && $_POST['action'] == 'insert') {			
        $data = [
            'class_name' => $_POST['class_name'],
            'description' => $_POST['description'],
            'trainer_id' => $_POST['trainer_id'],
            'capacity' => $_POST['capacity']
        ];
        if (insert('class', $data)) {
            $message = ["Class added successfully!", "success"];
        } else {
            $message = ["Failed to add class.", "danger"];
        }
    }

    // Update an existing class
    
    else if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $data = [
            'id' => $_POST['classid'],
            'class_name' => $_POST['class_name'],
            'description' => $_POST['description'],
            'trainer_id' => $_POST['trainer_id'],
            'capacity' => $_POST['capacity']
        ];

        if (update('class', $data)) {
            $message = ["Schedule updated successfully!", "success"];
        } else {
            $message = ["Failed to update schedule.", "danger"];
        }
    }

   
}

 // Delete a class
 if (isset($_POST['btnDelete']) && isset($_POST['class_id'])) {
    if (delete('class', $_POST['class_id'])) {
        $message = ["Successfully Deleted!", "success"];
    } else {
        $message = ["Sorry! Something went wrong", "danger"];
    }
}

// Fetch all classes from the database
$classes = read('class');

?>



   
<!-- Begin Page Content -->
<div class="container-fluid">

<!-- Page Heading -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Classes</h1>
</div>
<div class="card shadow mb-4">
        <div class="row alerts">
            <?php $message ? showMessage($message) : ""; ?>
        </div>
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Classes List</h6>
            <!-- Button to open the add schedule modal -->
    <button class="btn btn-primary float-end " data-bs-toggle="modal" data-bs-target="#ClassModal" onclick="reset()" >Add Schedule</button>
        </div>


    <!-- Class List Table -->
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>description</th>
                <th>Trainer Name</th>
                <th>Capacity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classes as $class) : ?>
                <tr>
                    <td><?= $class['class_name'] ?></td>
                    <td><?= $class['description'] ?></td>
                    <td><?= read_column('trainers', 'FullName', $class['trainer_id']); ?></td>
                    <td><?= $class['capacity'] ?></td>
                    
                    <td>
                        <!-- Edit button with data-passing logic -->
                        <a href="#ClassModal" data-bs-toggle="modal" class="btn btn-primary"
                        onclick="fillForm(<?= $class['id'] ?>)"> <i class="bx bx-edit-alt"></i></a>

                        <!-- Delete button -->
                        <a href="#delete-class-modal" data-bs-toggle="modal" class="btn btn-danger"
                           onclick="setId(<?= $class['id']; ?>)"> <i class="bx bx-trash"></i></a>
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
    document.querySelector('.modal-title').textContent = 'Add class Details';
    document.getElementById('action').value = 'insert';
    document.getElementById('classid').value = "";
                document.getElementById('class_name').value = "";
                document.getElementById('description').value = "";
                document.getElementById('trainer_id').value = "";
                document.getElementById('capacity').value = "";
}

function setId(id) {
    document.getElementById('class_id').value = id;
}

function fillForm(id) {
    document.querySelector('.modal-title').textContent = 'Update Class Details';
    document.getElementById('action').value = 'update';
    document.getElementById('classid').value = id;

    $.ajax({
        url: "includes/ajax.php",
        method: "post",
        data: {
            table: "class",
            id: id,
            action: "forUpdate"
        },
        success: function(result) {
            const data = JSON.parse(result);
            
            document.getElementById('classid').value = data.id;
                document.getElementById('class_name').value = data.class_name;
                document.getElementById('description').value = data.description;
                document.getElementById('trainer_id').value = data.trainer_id;
                document.getElementById('capacity').value = data.capacity;
        },
        error: function(error) {
            console.log(error);
        }
    });
}
</script>


<?php include 'includes/footer.php'; ?>
