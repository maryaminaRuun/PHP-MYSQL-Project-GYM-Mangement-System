<?php 
include 'includes/init.php';
include 'modals/scheduleModal.php'; 

$message = [];

// Handle adding a new schedule

    if (isset($_POST['btnSave'])) {
        if (isset($_POST['action']) && $_POST['action'] == 'insert') {			
            $data = [

                'class_id' => $_POST['class_id'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'location' => $_POST['location']
            ];
            if (insert('schedule', $data)) {
                $message = ["schedule added successfully!", "success"];
            } else {
                $message = ["Failed to add schedule.", "danger"];
            }
        }

    // Handle updating a schedule
    else if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $data = [
            'id' => $_POST['scheduleid'],
            'class_id' => $_POST['class_id'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'location' => $_POST['location']
        ];

        if (update('schedule', $data)) {
            $message = ["Schedule updated successfully!", "success"];
        } else {
            $message = ["Failed to update schedule.", "danger"];
        }
    }

   

}

// Handle deleting a schedule
if (isset($_POST['btnDelete']) && isset($_POST['schedule_Id'])) {
    $schedule_Id = $_POST['schedule_Id'];
    
    if (delete('schedule', $schedule_Id)) {
        $message = ["Schedule deleted successfully!", "success"];
    } else {
        $message = ["Failed to delete schedule.", "danger"];
    }
}


// if (isset($_POST['btnDelete']) && isset($_POST['member_id'])) {
//     if (delete('members', $_POST['member_id'])) {
//         $message = ["Successfully Deleted!", "success"];
//     } else {
//         $message = ["Sorry! Something went wrong", "danger"];
//     }
// }

// Fetch all schedules from the database
$schedules = read('schedule');

// Fetch a specific schedule if an id is passed (for editing)
if (isset($_GET['id'])) {
    $scheduleid = $_GET['id'];
    $sql = "SELECT * FROM schedule WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $scheduleid, PDO::PARAM_INT);
    $stmt->execute();
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($schedule); // Return the schedule data as JSON
    exit(); // Prevent further code execution
}
?>

    
<!-- Begin Page Content -->
<div class="container-fluid">

<!-- Page Heading -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Schedules</h1>
</div>
<div class="card shadow mb-4">
        <div class="row alerts">
            <?php $message ? showMessage($message) : ""; ?>
        </div>
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Schedules List</h6>
            <!-- Button to open the add schedule modal -->
    <button class="btn btn-primary float-end " data-bs-toggle="modal" data-bs-target="#ScheduleModal" onclick="reset()" >Add Schedule</button>
        </div>
              
    <!-- Schedule List Table -->
    <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Class</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedules as $schedule) : ?>
                <tr>
                    <td><?= read_column('class', 'class_name', $schedule['class_id']) ?></td>
                    <td><?= $schedule['start_time'] ?></td>
                    <td><?= $schedule['end_time'] ?></td>
                    <td><?= $schedule['location'] ?></td>
                    <td>
                        <!-- Edit button with data-passing logic -->
                        <a href="#ScheduleModal" data-bs-toggle="modal" class="btn btn-primary"
                        onclick="fillForm(<?= $schedule['id']; ?>)"> <i class="bx bx-edit-alt"></i></a>

                        <!-- Delete button -->
                        <a href="#delete-schedule-modal" data-bs-toggle="modal" class="btn btn-danger"
                           onclick="setId(<?= $schedule['id']; ?>)"> <i class="bx bx-trash"></i></a>
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


<script>

function reset() {
    document.querySelector('.modal-title').textContent = 'Add Schedule Details';
    document.getElementById('action').value = 'insert';
    document.getElementById('scheduleid').value = "";
    document.getElementById('class_id').value = "";
    document.getElementById('start_time').value = "";
    document.getElementById('end_time').value = "";
    document.getElementById('location').value = "";
}

function setId(id) {
    document.getElementById('schedule_Id').value = id;
}

function fillForm(id) {
    document.querySelector('.modal-title').textContent = 'Update Schedule Details';
    document.getElementById('action').value = 'update';
    document.getElementById('scheduleid').value = id;

    $.ajax({
        url: "includes/ajax.php",
        method: "post",
        data: {
            table: "schedule",
            id: id,
            action: "forUpdate"
        },
        success: function(result) {
            const data = JSON.parse(result);
            
            document.getElementById('scheduleid').value = data.id;
            document.getElementById('class_id').value = data.class_id;
            document.getElementById('start_time').value = data.start_time;
            document.getElementById('end_time').value = data.end_time;
            document.getElementById('location').value = data.location;
        },
        error: function(error) {
            console.log(error);
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
