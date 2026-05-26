# Plan: Save Device Scan Data Only in `dealer_ledger_old_1778788199`

## Current Behavior
When a device is scanned and assigned to a dealer via [`dealer_manager.php`](../dealer_manager.php), the backend API [`api_dealers.php`](../api_dealers.php) (`action=update`) currently saves to **3 tables**:

1. **`device_master`** - UPDATE: sets `holder`, `status='SOLD'`, `software`, `sim_no`, `issue_date`, `rate`
2. **`dealer_ledger`** - INSERT: new ledger record
3. **`dealer_ledger_old_1778788199`** - INSERT: old ledger backup

## Desired Behavior
Save ONLY to **`dealer_ledger_old_1778788199`** - remove saves to `device_master` and `dealer_ledger`.

## Changes Required

### 1. [`api_dealers.php`](../api_dealers.php) - `action=update` (lines 57-71)
- **Remove** Step 1: `device_master` UPDATE (lines 57-59)
- **Remove** Step 2: `dealer_ledger` INSERT (lines 61-63)
- **Keep** Step 3: `dealer_ledger_old_1778788199` INSERT (lines 65-71)
- Also remove the `device_master` validation/check (lines 34-53) since we no longer need to verify IMEI exists in device_master before saving
- Simplify: just accept dealer_name + imei and insert directly into old_ledger table

### 2. [`api_dealers.php`](../api_dealers.php) - `action=pending` (lines 81-112)
- **Remove** Query 1 that reads from `dealer_ledger` (lines 83-91)
- **Keep only** Query 2 that reads from `dealer_ledger_old_1778788199` (lines 93-106)

### 3. [`api_dealers.php`](../api_dealers.php) - `action=payment` (lines 114-169)
- **Remove** `dealer_ledger` UPDATE (lines 123-125)
- **Remove** `dealer_ledger` INSERT for PAYMENT records (lines 152-155)
- **Keep only** `dealer_ledger_old_1778788199` UPDATE/INSERT

### 4. [`dealer_manager.php`](../dealer_manager.php) - Frontend JavaScript
- The `checkImeiStatus()` function (lines 380-419) checks the IMEI against `device_master` via `api_master_data.php`. This is for informational purposes only and can stay as-is or be removed.
- No other frontend changes needed since the UI flow remains the same.

## Flow After Changes

```
Camera Scan → Browser Input Field → User clicks "Assign to Dealer"
  → api_dealers.php?action=update
    → ONLY INSERT into dealer_ledger_old_1778788199
```

## Potential Impact / Questions for User

1. **`action=payment`** - Should the payment (txn_id, selling_price update) also ONLY update `dealer_ledger_old_1778788199`? Currently it updates both `dealer_ledger` and `dealer_ledger_old_1778788199`.

2. **IMEI validation** - Currently the code validates that the IMEI exists in `device_master` before allowing assignment. If we remove this check, any IMEI can be entered. Is that acceptable?

3. **Stock status check** - The current code prevents assigning an already-sold device. Without checking `device_master`, this validation is lost. Okay?
