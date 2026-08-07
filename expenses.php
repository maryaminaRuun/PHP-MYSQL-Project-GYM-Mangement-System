<?php
include 'includes/init.php';
require_role(['Admin','Manager','Cashier']);
$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['save_expense'])) {
        $amount = (float) ($_POST['amount'] ?? 0);
        $bankId = (int) ($_POST['bank_id'] ?? 0);
        if ($amount <= 0 || $bankId <= 0 || empty($_POST['payee']) || empty($_POST['expense_date'])) {
            $message = ['Enter a valid amount, bank, payee and date.', 'danger'];
        } else {
            try {
                $conn->beginTransaction();
                $bank = $conn->prepare('SELECT balance FROM banks WHERE id = ? FOR UPDATE');
                $bank->execute([$bankId]);
                $balance = $bank->fetchColumn();
                if ($balance === false || (float) $balance < $amount) {
                    throw new RuntimeException('Insufficient bank balance.');
                }
                $expenseNo = 'EXP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
                $stmt = $conn->prepare('INSERT INTO expenses (expense_no, category_id, bank_id, amount, expense_date, payee, description, reference_no, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$expenseNo, (int) $_POST['category_id'], $bankId, $amount, $_POST['expense_date'], trim($_POST['payee']), trim($_POST['description'] ?? ''), trim($_POST['reference_no'] ?? ''), $_SESSION['userId']]);
                $expenseId = (int) $conn->lastInsertId();
                $conn->prepare('UPDATE banks SET balance = balance - ? WHERE id = ?')->execute([$amount, $bankId]);
                audit('create', 'expense', $expenseId, ['expense_no' => $expenseNo, 'amount' => $amount]);
                $conn->commit();
                $message = ['Expense recorded successfully.', 'success'];
            } catch (Throwable $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                $message = [$e->getMessage(), 'danger'];
            }
        }
    }
}

$categories = $conn->query("SELECT * FROM expense_categories WHERE status='Active' ORDER BY name")->fetchAll();
$banks = $conn->query('SELECT * FROM banks ORDER BY name')->fetchAll();
$expenses = $conn->query('SELECT e.*, c.name category_name, b.name bank_name, u.FullName user_name FROM expenses e JOIN expense_categories c ON c.id=e.category_id JOIN banks b ON b.id=e.bank_id LEFT JOIN users u ON u.id=e.user_id ORDER BY e.expense_date DESC, e.id DESC')->fetchAll();
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4"><h3>Expenses</h3><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expenseModal">Record Expense</button></div>
  <div class="alerts"><?php if ($message) showMessage($message); ?></div>
  <div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>No.</th><th>Date</th><th>Category</th><th>Payee</th><th>Bank</th><th>Reference</th><th class="text-end">Amount</th></tr></thead><tbody>
  <?php foreach ($expenses as $row): ?><tr><td><?= escape($row['expense_no']) ?></td><td><?= escape($row['expense_date']) ?></td><td><?= escape($row['category_name']) ?></td><td><?= escape($row['payee']) ?></td><td><?= escape($row['bank_name']) ?></td><td><?= escape($row['reference_no']) ?></td><td class="text-end fw-semibold text-danger"><?= money($row['amount']) ?></td></tr><?php endforeach; ?>
  <?php if (!$expenses): ?><tr><td colspan="7" class="text-center py-4 text-muted">No expenses recorded.</td></tr><?php endif; ?>
  </tbody></table></div></div>
</div>
<div class="modal fade" id="expenseModal" tabindex="-1"><div class="modal-dialog"><form method="post" class="modal-content"><div class="modal-header"><h5 class="modal-title">Record Expense</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
<div class="mb-3"><label class="form-label">Category</label><select name="category_id" class="form-select" required><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= escape($c['name']) ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Bank / Cash Account</label><select name="bank_id" class="form-select" required><?php foreach($banks as $b): ?><option value="<?= $b['id'] ?>"><?= escape($b['name']) ?> (<?= money($b['balance']) ?>)</option><?php endforeach; ?></select></div>
<div class="row"><div class="col-md-6 mb-3"><label class="form-label">Amount</label><input name="amount" type="number" min="0.01" step="0.01" class="form-control" required></div><div class="col-md-6 mb-3"><label class="form-label">Date</label><input name="expense_date" type="date" value="<?= date('Y-m-d') ?>" class="form-control" required></div></div>
<div class="mb-3"><label class="form-label">Payee</label><input name="payee" class="form-control" required></div><div class="mb-3"><label class="form-label">Reference</label><input name="reference_no" class="form-control"></div><div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
</div><div class="modal-footer"><button class="btn btn-label-secondary" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary" name="save_expense">Save Expense</button></div></form></div></div>
<?php include 'includes/footer.php'; ?>
