# ReStep: Steps 1-4 Implementation and Verification

Date: 29 October 2026. Original application commit: `7e73a56`.
Branch: `fix/base-steps-1-4`. Changes are uncommitted.

## Scope and implementation

Only the four steps assigned by the user were implemented. Simulated payment
outcomes, a duplicate-purchase attempt guard, page imagery/table improvements,
FAQ/About changes, Admin and the modern version remain later work.

| Step | Changes |
|---|---|
| 1: setup/package | Confirmed Apache port 8000 and MySQL port 3306. Set the application/email URL to `http://localhost:8000/IE4727/shoe-store/`, including the private local override. Verified 60 products and 660 size rows. Removed the unused JSON manifest after preserving all 60 source records in `docs/IMAGE_ATTRIBUTION.txt`. Added the internal handover to Git's ignore list and documented submission exclusions. |
| 2: mail | Configured the user-confirmed exact recipients `f31ee@localhost` and `f32ee@localhost`. Other recipients are rejected before `mail()`. Checkout ignores posted email and uses the account record. Received/updated/confirmed/cancelled actions distinguish saved state from mail-server acceptance/failure. Unchanged saves send no update message. |
| 3: validation | Added shared PHP rules and corresponding named JavaScript checks. Included account phone/postal validation, CS04 names/email, field lengths, required keys, permitted contact topics/payment choices, strict integer quantities and non-expired MM/YY dates. Submit property handlers return false for invalid data. Removed checkout's input-stripping and constructed warning script. Kept inherited hashing and output escaping. |
| 4: bag/orders | Enforced combined quantity 1-10 per product/size and stock limits. Invalid bag changes preserve the existing bag. Order changes validate all lines/delivery before writes, aggregate size demand for swaps and reserve only extra stock. Conditional updates and affected-row checks guard reservations and status transitions. Repeated cancellation cannot restore stock twice. Later query failures attempt compensation using the approved PHP 8 compatibility setting below. |

Code uses four-space indentation, named functions, ordinary PHP arrays/loops and
multiline SQL/HTML where needed. No production test framework, dependency, AJAX,
JSON transport, prepared-statement API, SQL transaction, CSRF or session
regeneration machinery was added.

## Environment and data protection

- The working site's home/login pages returned HTTP 200 without PHP error text.
- State-changing integration tests ran only in a temporary application copy and
  a uniquely named disposable database. Real PHP mail was disabled and replaced
  by an external capture stub.
- Working database counts remained: products 60, product_sizes 660, users 0,
  orders 0, order_items 0 and enquiries 0. No working-data writes were required.
- Actual Thunderbird/Mercury inbox delivery was not tested. The permitted account
  owners should verify that separately with the correct local mail service.
- Private credentials and temporary test tools remain outside the submission.

## Executed checks

These are automated/boundary checks, not a claim that every case in the manual
workbook has been manually performed. Manual cases remain Not run until the team
records their own execution and evidence.

| Check | Method | Observed result |
|---|---|---|
| Original defects | Run the original HEAD in the disposable environment before implementation. | Eight expected failures: missing account client check, expired card acceptance, forged checkout recipient, cumulative quantity 11, silent bag loss from invalid size, partial order edit, repeated cancellation and misleading mail-success feedback. |
| PHP validation | Direct calls to the shared validators using valid/invalid names, email, phone, postal, raw quantities and relative expiry dates. | 33 cases passed. |
| PHP invalid pages | Exercise registration/login/account/contact rejection handlers with database calls blocked by a test boundary. | Six rejection cases passed without writes. |
| JavaScript rules | Execute named validation/submit functions against controlled form elements. | 28 cases passed. |
| Bag/order boundary behavior | Execute controller/action code with controlled database/mail boundaries, including injected false query results. | 30 cases passed, including late invalid line, stockless size swap, combined quantity cap, decimal rejection, repeated cancel, ownership, compensation and padded/unchanged delivery fields. |
| HTTP integration | Run signup/login/account/contact/bag/checkout/order/recipient and mail-failure scenarios against disposable MySQL and captured mail. | 29 groups passed on the final snapshot. |
| Additional boundaries | Exercise padded phone/postal input, unchanged padded address and overlong raw card input. | Four groups passed on the final snapshot. |
| Actual browser validation | Chrome checks form handlers, invalid submission cancellation, account rules, expired card and valid COD submission. | Six groups passed on the final snapshot. |
| Real database-error recovery | Use disposable MySQL triggers to reject an order-item INSERT and UPDATE; also verify overlong raw card input is rejected. | Three checks passed after the approved compatibility line; both previously failing SQL-error cases restore the original records/stock without extra mail. |
| Syntax | PHP lint across all 22 PHP files; Node syntax check for `assets/script.js`. | Passed; no whitespace errors and no lines above 120 columns in the touched form/action/helper files. |
| Package | Check image attribution and scan for remaining JSON files. | All 60 source records preserved; no JSON files remain in the Base folder. |

The final tested snapshot matched all 23 application/configuration files checked
by the integration harness. Summary: 97 focused validator/controller boundary
checks, 39 HTTP/additional-boundary/actual-browser groups, and three additional
fault/length checks passed. Both actual SQL-error recovery cases have passed on
the installed PHP 8.3/MySQL runtime after the approved compatibility change.

Verification evidence is stored outside the application in the temporary folder
named `restep_steps14_20261029_172251_83bf6a`. It includes REPORT.md, per-suite result
files, mail-guard capture counts, Chrome screenshot, application hashes and fault
responses. Blocked recipient checks produced zero new captured messages. The
dedicated temporary PHP/Chrome processes were stopped; the disposable database and
restart/test scripts remain available for reproducing the checks. No test triggers
remain and the final suite produced no PHP fatal errors.

Final application source manifest SHA256:
`b872d6ecf1ef1354589ba9ffc7d283c7060756ba0640fbcd7c4f4c471c31ca23`.

## Approved PHP 8 database-error compatibility

The installed PHP is 8.3.36. Before the compatibility fix, its mysqli driver used
exception reporting mode 3. The lecture's `if (!$db->query(...))` pattern expects false returns, so an
actual SQL error threw before the new checks/recovery could run.

A disposable real-MySQL fault test confirmed this boundary:

- Rejecting an order-item INSERT left a Pending order header without items and
  stock changed from 30 to 28.
- Rejecting an order-item UPDATE left the original quantity 1 with stock changed
  from 29 to 27 after the attempted quantity increase.
- Both responses contained an uncaught mysqli exception. No success mail was
  captured. The faults and test records affected only the disposable database;
  the temporary triggers were removed afterward.

The user explicitly approved this compatibility setting before opening the connection:

```php
mysqli_report(MYSQLI_REPORT_OFF);
```

This API was not found in the supplied module notes. The user allowed it as a
specific PHP 8 compatibility exception, and it has been added to `includes/db.php`.

The disposable real-MySQL retest now passes:

- Failed order-item INSERT: the error is shown without an uncaught exception,
  stock is restored to 30, no incomplete order/items remain and no mail is sent.
- Failed order-item UPDATE: the original quantity 1 and stock 29 are restored,
  the order is unchanged and no extra mail is sent.
- Both temporary triggers were removed successfully. The accompanying raw-card
  length rejection case also passes: three fault-suite checks, zero failures.

The original RED evidence is retained separately from `compat-green` results.
Source review also confirmed mysqli reporting mode zero and a failed read-only
query returning false on the installed PHP 8.3 runtime.

Even with false-return compatibility, several separate SQL writes are not an
atomic transaction. A crash, connection loss or concurrent edit can still leave
incomplete compensation. Array-valued forged POST inputs and PHP byte-length
versus JavaScript character-length differences also remain documented limits.

## Teammate handoff

1. Keep the allowlist exact and preserve the working `$site_url`/private settings.
2. Preserve the approved PHP 8 compatibility line so the lecture-style database
   result checks work; the recorded fault-recovery tests verify synchronous query errors.
3. Implement step 5's distinct simulated payment outcomes and duplicate-purchase
   attempt guard. Existing Card checks still validate demo input only.
4. Continue page/report work and record the manual test workbook's actual results.
5. Do not describe captured email as an actual inbox-delivery test or claim the
   entire Base Version is complete from these scoped changes.
