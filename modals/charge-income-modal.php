<!-- Charge Income Modal -->
<div class="modal fade" tabindex="-1" id="charge-income-modal">
    <div class="modal-dialog modal-lg">
        <form method="post" id="chargeIncomeForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="form-group mb-3">
                        <label for="member">Select Member</label>
                        <select name="member" id="member" class="form-select" >
                            <option value="" selected disabled>Please select member</option>
                            <?php foreach(read('members') as $member) { ?>
                                <option value="<?= htmlspecialchars($member['id']) ?>"><?= htmlspecialchars($member['FullName']) . " " . htmlspecialchars($member['Phone']); ?></option>
                            <?php } ?>
                        </select>
                        <span class="text-danger" id="memberError"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="charge">Select Charge</label>
                        <select name="charge" id="charge" class="form-select" >
                            <option value="" selected disabled>Please select charge to pay</option>
                        </select>
                        <span class="text-danger" id="chargeError"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="amount">Amount Paid</label>
                        <input type="text" class="form-control" placeholder="Amount Paid" name="amount" id="amount" >
                        <span class="text-danger" id="amountError"></span>
                    </div>
                  
                    <div class="form-group mb-3">
                        <label for="bank">Bank</label>
                        <select name="bank" id="bank" class="form-control">
                            <option value="" selected disabled> please select bank</option>
                            <?php foreach(read('banks') as $bank){ ?>
                                <option value="<?= $bank['id'] ?>"><?= $bank['name']." ". $bank['account_num']; ?></option>
                            <?php } ?>
                        </select>
                        <span class="text-danger" id="paymentMethodError"></span>
                    </div>

                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="btnSave">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function removeAlert(){
        setTimeout(() => {
            document.querySelector('.alerts').remove()
        }, 3000);
    }
</script>
