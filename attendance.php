<?php
include 'includes/init.php';
$message = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $memberId = (int) ($_POST['member_id'] ?? 0);
    if (isset($_POST['check_in']) && $memberId) {
        $open = $conn->prepare('SELECT id FROM attendance WHERE member_id=? AND check_out IS NULL LIMIT 1');
        $open->execute([$memberId]);
        if ($open->fetchColumn()) $message = ['This member is already checked in.', 'warning'];
        else {
            $stmt=$conn->prepare('INSERT INTO attendance(member_id,recorded_by) VALUES(?,?)'); $stmt->execute([$memberId,$_SESSION['userId']]);
            audit('check_in','attendance',(int)$conn->lastInsertId(),['member_id'=>$memberId]);
            $message=['Member checked in successfully.','success'];
        }
    }
    if (isset($_POST['check_out']) && $memberId) {
        $stmt=$conn->prepare('UPDATE attendance SET check_out=NOW() WHERE member_id=? AND check_out IS NULL ORDER BY id DESC LIMIT 1'); $stmt->execute([$memberId]);
        $message=$stmt->rowCount()?['Member checked out successfully.','success']:['No open check-in found.','warning'];
    }
}
$members=$conn->query("SELECT id,FullName FROM members WHERE Status='Active' ORDER BY FullName")->fetchAll();
$rows=$conn->query("SELECT a.*,m.FullName,TIMESTAMPDIFF(MINUTE,a.check_in,COALESCE(a.check_out,NOW())) minutes FROM attendance a JOIN members m ON m.id=a.member_id WHERE DATE(a.check_in)=CURRENT_DATE ORDER BY a.check_in DESC")->fetchAll();
?>
<div class="container-fluid"><div class="d-flex justify-content-between align-items-center mb-4"><h3>Member Attendance</h3><span class="badge bg-label-primary"><?= count($rows) ?> visits today</span></div><div class="alerts"><?php if($message) showMessage($message); ?></div>
<div class="card mb-4"><div class="card-body"><form method="post" class="row g-3 align-items-end"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="col-md-7"><label class="form-label">Member</label><select name="member_id" class="form-select" required><option value="">Select member</option><?php foreach($members as $m): ?><option value="<?= $m['id'] ?>"><?= escape($m['FullName']) ?></option><?php endforeach; ?></select></div><div class="col-md-5 d-flex gap-2"><button name="check_in" class="btn btn-success flex-fill"><i class="bx bx-log-in"></i> Check In</button><button name="check_out" class="btn btn-outline-danger flex-fill"><i class="bx bx-log-out"></i> Check Out</button></div></form></div></div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Member</th><th>Check In</th><th>Check Out</th><th>Duration</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= escape($r['FullName']) ?></td><td><?= date('h:i A',strtotime($r['check_in'])) ?></td><td><?= $r['check_out']?date('h:i A',strtotime($r['check_out'])):'—' ?></td><td><?= floor($r['minutes']/60) ?>h <?= $r['minutes']%60 ?>m</td><td><span class="badge <?= $r['check_out']?'bg-label-secondary':'bg-label-success' ?>"><?= $r['check_out']?'Completed':'In Gym' ?></span></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="text-center py-4 text-muted">No attendance today.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include 'includes/footer.php'; ?>
