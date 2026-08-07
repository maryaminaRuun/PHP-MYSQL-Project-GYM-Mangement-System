<?php
include 'session.php'; include 'functions.php';
header('Content-Type: application/json; charset=utf-8');
if(empty($_SESSION['userId'])){http_response_code(401);echo json_encode(['error'=>'Unauthenticated']);exit;}

if(($_POST['action']??'')==='forUpdate'){
    $allowed=['members','memberships','trainers','class','schedule','equipments','banks','users'];
    $table=(string)($_POST['table']??'');$id=(int)($_POST['id']??0);
    if(!in_array($table,$allowed,true)||$id<1){http_response_code(400);echo json_encode(['error'=>'Invalid request']);exit;}
    $stmt=$conn->prepare("SELECT * FROM `$table` WHERE id=? LIMIT 1");$stmt->execute([$id]);
    echo json_encode($stmt->fetch()?:[]);exit;
}

if(($_POST['action']??'')==='forPayment'){
    $memberId=(int)($_POST['memberid']??0);
    $stmt=$conn->prepare("SELECT c.id,c.Price-COALESCE(SUM(p.amount),0) Price,ms.MembershipType Type FROM charges c JOIN members m ON m.id=c.member_id LEFT JOIN memberships ms ON ms.id=m.MembershipID LEFT JOIN payments p ON p.charge_id=c.id WHERE c.member_id=? AND c.status IN ('Unpaid','Partially Paid') GROUP BY c.id,ms.MembershipType HAVING Price>0 ORDER BY c.date DESC");
    $stmt->execute([$memberId]);echo json_encode($stmt->fetchAll());exit;
}

http_response_code(400);echo json_encode(['error'=>'Unsupported action']);
