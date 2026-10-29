# ReStep: Team Summary and Next Steps

Prepared: 29 October 2026. Reviewed application: commit `7e73a56`.

Implementation update: steps 1-4 have since been applied on `fix/base-steps-1-4`, including the explicitly approved PHP 8 database-error compatibility setting. The tables below describe the original audit/planned scope; see `STEPS_1_TO_4_RESULTS.md` for the current executed checks and handoff. Steps 5 onward remain the teammate's work.

This is a standalone summary of the agreed audit, proposed fixes and manual tests. Website code has not been changed by the audit. Implemented source is not proof that a feature has passed testing. All tests below are **Not run**.

## 1. Current state: Base Version (85%) and Additional Version (15%)

ReStep is a shoe shop using HTML, CSS, JavaScript, PHP, MySQL and local Mercury mail. The main journey is browse -> bag -> sign in -> checkout -> email acknowledgement -> review/change order -> confirm or cancel.

| Area | Current state | Required next action |
|---|---|---|
| Pages | Home plus ten content routes. Even excluding static About/FAQ, home plus eight dynamic content routes remain. | Keep within home plus 4-10 qualifying content pages. Exclude configuration, logout and includes from the count. |
| Catalog/product | Search, filters, sorting, images, product details, sizes and live subtotal exist. | Verify combinations, stock limits and empty/not-found states. |
| Forms/tables/database | Local four-field forms, PHP processing, SELECT/INSERT/UPDATE, server-rendered pages, tables and an external stylesheet exist. | Demonstrate working examples and preserve them during fixes. |
| Account validation | Phone/postal formats have PHP checks but no equivalent client checks. | Add the missing JavaScript or HTML5 format validation. |
| Payment | Valid card formatting currently leads to order creation. | Add a distinct unsuccessful-payment outcome. An invalid input or failed email is not a declined payment. |
| Email | Acknowledgement and order-update mail exist; recipients are unrestricted. | Permit only the student's exact own local mailbox. Check the result of every email action. |
| Bag/order integrity | Quantity rules differ between steps; invalid order edits can partially save. | Use consistent combined quantities, validate the complete edit before writes, and correct stock/repeated-action behavior. |
| Card expiry | MM/YY format is checked, but past months pass. | Reject expired months in both JavaScript and PHP. |
| Images/content | All normal pages contain shared logos. Login/account/contact/FAQ lack contextual imagery; About/FAQ bodies are static. | Add relevant local imagery and useful dynamic content where appropriate. These are marking risks, not proven page-count failures. |
| Report/deployment | Sitemap and planning files exist. Complete report, wireframes, actual test results and deployment evidence were not found or verified here. | Assemble and reconcile the evidence before submission. |
| Modern version | No modern Additional Version was found in this repository. | Complete the Base first, then implement and justify selected modern enhancements. |

The current sessions, JavaScript subtotals and PHP features belong to the traditional implementation. A PHP Admin page would also extend that implementation; it would not automatically demonstrate the modern 15% component.

Execute the work in this order:

| Step | Work | Main files / evidence | Completion check |
|---|---|---|---|
| 1 | Confirm actual XAMPP URL, database, permitted mailbox and test fixtures. Remove submission-only packaging conflicts. | `README.md`, `config.php`, local override, asset attribution | Working setup and email-link path; no credentials/internal handover/unused JSON in final Base package. |
| 2 | Restrict mail to the exact permitted local recipient; ignore a forged checkout email and report mail failures accurately. | `includes/functions.php`, `checkout.php`, `order.php`, `includes/order-actions.php` | MAIL checks pass without sending to any prohibited recipient. |
| 3 | Standardize form validation, expiry checks and submit cancellation. Remove input-character deletion and constructed warning spans. | `assets/script.js`, form pages, shared script placement | Browser rejection and independent PHP rejection work; valid submissions still succeed. |
| 4 | Fix cumulative/merged quantities, complete-request validation and stock differences. Prevent repeated cancellation from restoring stock twice. | `cart.php`, `checkout.php`, `order.php`, `includes/order-actions.php` | Invalid edits leave existing data unchanged; normal stock/total changes and repeated requests are correct. |
| 5 | Add simulated payment success/failure and server-side repeat-submit handling. | `checkout.php`, session state, order/email wording, `README.md` | Success creates one reviewable order; decline preserves bag/stock and sends no success acknowledgement. |
| 6 | Complete page/table/presentation checks. | Existing pages, header/footer, `assets/style.css` | Appropriate titles/text/images, useful tables, working search/navigation and narrow/wide layouts. |
| 7 | Run applicable tests, fix failures and complete the report/deployment evidence. | Test records, report, sitemap/storyboard, wireframes, final package | Required features have actual passing results; unresolved failures remain explicitly identified. |

For repeatable payment tests, the proposed implementation uses demo card `4111 1111 1111 1111` for success and `4000 0000 0000 0002` for decline, both with otherwise valid details. These are proposed project rules, not existing behavior or numbers required by the brief. Cash on delivery remains a separate accepted order path. No real charge, payment gateway redirect or refund is required.

Use a session-held checkout attempt number and recorded owned order ID to identify a repeated completed submission. A disabled submit button alone does not prevent duplicates. Keep Pending/Confirmed/Cancelled as order-review states rather than treating them as payment states.

After the Base passes, consider only a few additions:

| Addition | Benefit / scope |
|---|---|
| Admin prices and daily sales reports | Follows Case Study 05: selected-product price UPDATE then SELECT; daily revenue/quantity by product/category; best product by revenue and its popular size by quantity. Protect Admin server-side and preserve historical order prices. |
| Better discovery | Preserve compatible filters, show sold-out cards/button states and useful bag stock warnings. |
| Better review | Live proposed order totals and an owner-only pending-order reminder. PHP remains authoritative after saving. |
| Fulfilment progress | Optional Confirmed -> Shipped -> Delivered states with restricted local notification. Theme 2 does not mandate this. |

Admin is not required by Theme 2. If adding its page, merge About into home first to retain a conservative home-plus-ten count and update all navigation/design references. Cookies, guest checkout, password changes and automatic order expiry are not needed to close the current brief gaps.

For the modern version, a separate React implementation using component state/live updates is one taught option. Tailwind/Express are further choices, not compulsory ingredients. Explain each actual improvement and keep the Base independently runnable.

## 2. Compatibility with the brief and taught techniques

Primary source: **IE4727 Project List and Guidelines_2025.pdf**. PDF page 1 contains Theme 2; page 3 covers payment outcomes; pages 4-6 define the Base/exclusions and sequence; page 7 defines the report; page 8 restricts email. Case Studies 01-05 and lecture notes provide teaching examples, not automatic extra final-project requirements.

The final submission must cover all of the following:

- [ ] Original design and correct contributors/group arrangement; no purchased/free/textbook site template and no reuse for another assessed course/FYP.
- [ ] Home plus 4-10 additional meaningful content pages, with appropriate titles, text and images; no purely image-based pages.
- [ ] At least one effective table and one local form with four or more fields, PHP processing and database operations.
- [ ] Demonstrated SELECT, INSERT and UPDATE; at least one server-generated page.
- [ ] Appropriate HTML5 checks, JavaScript for remaining client-checkable rules, and required PHP checks. Keep server validation when client checks are bypassed.
- [ ] External CSS containing at least four meaningful styles.
- [ ] Theme 2 browsing/purchase, email acknowledgement and a way to review the visual proposed order and make final changes. Document how shoe images/colour/size/quantity/delivery/total implement that preview.
- [ ] Successful and unsuccessful payment handling; no real payment portal/redirection needed.
- [ ] Email only to the exact own local account; allowing every `@localhost` recipient is too broad.
- [ ] No prohibited Base libraries/frameworks/templates, jQuery/JSON/AJAX, frames/iframes, mailto/external form handlers or prohibited external-site links.
- [ ] Functional testing and publication in the course-approved environment; Base completed before modern enhancement work.
- [ ] Report: application requirements, functional requirements, storyboard/site map, wireframes, implementation details, input/expected/method/actual test results, and justification of any implemented modern enhancements.

Apply the following coding boundaries:

| Area | Keep / use | Remove, replace or avoid |
|---|---|---|
| Packaging | Local assets and plain-text/report attribution. | Exclude unused `assets/images/sources.json` from the final Base deliverable. It is inert metadata, but conflicts with the literal JSON exclusion. Moving it into this project's docs alone does not remove it. Keep `docs/CODEX_HANDOVER.md` out of submission. |
| JavaScript | `var`, named functions, ordinary conditions/loops, DOM lookup, regex test/search, focus, existing fixed error boxes, numeric parsing and `toFixed(2)`. | No new validation framework or dynamically constructed warning system. Preserve entered text instead of stripping characters while typing. |
| Events | Taught property assignment `form.onsubmit = checkForm` for validation; return false when invalid. Demonstrated `addEventListener("change", namedFunction, false)` for change events. Register after elements exist. | Do not assume return false from an addEventListener callback cancels submission. Inline return handlers are also taught; they are not automatically forbidden. |
| Dates | JavaScript Date/getFullYear/getMonth and PHP date(), with ordinary comparisons. JavaScript months are 0-11. | No format-only expiry test or hard-coded current date. Accept the current expiry month through its end. |
| PHP/MySQL | Existing arrays, loops, sessions, isset, regex, integer conversion after validation, escaping, mysqli query/fetch, success checks and affected_rows. | Do not add unverified prepared-statement or transaction APIs under the team's taught-only restriction. |
| Stock | Validate the entire proposed request first; aggregate demand by size, calculate differences, conditionally reserve extra stock and inspect affected_rows. Make cancellation conditional on owner/current Pending status. | Do not temporarily release stock before validating the edit or claim several separate writes are atomic. Restoration after a failed later write is best-effort recovery. |
| Existing safeguards | Keep password_hash/password_verify, output escaping and owner checks already present. | Do not downgrade to MD5. Existing use is not evidence that every inherited API appeared in the module. |
| HTML/CSS | Local PHP forms, labels, fieldsets, constraints, alt text and external CSS. Add useful captions/table sections and fix checkout's extra closing span. | Inline CSS/JavaScript are taught; consolidating them is consistency work, not a blanket brief prohibition. Avoid a redesign that delays required fixes. |

Selected validation rules: names use the CS04 letters/spaces rule with at least one letter; emails use its described username/domain rules plus a documented localhost exception; phone `^[689][0-9]{7}$`; postal `^[0-9]{6}$`; quantities are integers 1-10 per combined product/size, subject to stock. Optional account phone/postal may be blank. Apply relevant rules consistently in JavaScript and PHP. The name restriction is class-style alignment, not a general rule about valid real-world names.

Teaching references: CS04 pp. 1-6; JavaScript PDF pp. 16, 36, 62, 65-67; PHP PDF pp. 11, 15; MySQL PDF pp. 20-21, 26, 30, 36-38, 41; Tables PDF pp. 4, 10, 15-16; CS05 pp. 1-5. CS05 reports are **daily**. The earlier audits' unsupported "Usable Websites" criteria must not be cited.

The taught-only restriction is the team's additional constraint. The official brief does not explicitly ban prepared statements, SQL transactions, CSRF protection or session regeneration. They are not introduced in this plan because their APIs were not verified in the supplied examples. Keep the resulting limitations honest: guarded updates protect individual reservations, not an entire multi-table operation against crashes/concurrent changes. A failed required scenario is not made acceptable merely by documenting it.

Approved implementation exception: the user allowed `mysqli_report(MYSQLI_REPORT_OFF);` before the connection in `includes/db.php`. PHP 8 otherwise throws SQL exceptions instead of returning false as the lecture's checks expect. Keep this compatibility setting; it is documented as explicitly approved rather than claimed as a taught example.

## 3. Manual tests for the application

This condensed checklist preserves the functionality IDs from the detailed workbook. Ranges group related cases; check every listed variant and record its actual outcome. All results below are **Not run**. Tests of planned behavior do not mean that behavior is already implemented.

Preparation:

1. Use a disposable test database imported from schema.sql then seed.sql, not an existing working database that would be destroyed by reset.
2. Record tester/date/browser, actual URL and starting data. AUTH-01 establishes account A using the exact own local mailbox, called SELF, once. Create a second controlled account B for ownership tests; B must not receive mail.
3. Choose product P with two available sizes, a sold-out size and another product Q. Record IDs, prices and stock. Use stock at least 11 for the cumulative-limit test. Use a fresh Pending order for each destructive test.
4. Default valid data: `Alex Tan`, `91234567`, `10 Test Street`, `639798`, SELF, dummy password `TestPass123`, CVV `123`, and future expiry relative to the test month. Use dummy card data only.
5. Browser means manual interaction; DB means before/after read-only phpMyAdmin inspection. JavaScript disabled does not disable HTML5 checks: bypass client constraints or edit the submitted request when testing PHP directly.
6. For rejected-recipient tests, review the guard and keep sending disabled. Verify there is no mail call. Never test rejection by actually sending to an external account or another local account.

| Function / IDs | Input data and actions | Expected output | Method | Actual result |
|---|---|---|---|---|
| Setup: ENV-01-03 | Follow README in clean test setup; import data; navigate deployed pages and emailed order URL; complete the customer journey there. | Correct connections/paths/assets, no raw errors, repeatable deployed flow. | Browser + DB + permitted mailbox | Not run |
| Home: HOME-01-02 | Follow hero/collection/product links; compare hero price and displayed count with DB. | Correct destinations/data; fixed selections labelled Featured unless real popularity is implemented. | Browser + DB | Not run |
| Catalog: CAT-01-03 | Search name/colour/activity/description; select each category/activity; combine size, maximum price and search. | Results/count satisfy every selected constraint, including available size stock and inclusive price boundary. | Browser + DB | Not run |
| Catalog: CAT-04-06 | Sort low-high/high-low/name, clear filters, search nonexistent/quote/HTML-like text. | Correct ordering/full reset; useful no-results state; no execution or SQL error. | Browser + DB | Not run |
| Product: PRD-01-04 | Inspect details/related shoes; select available/sold-out/no size; change quantity; open missing/invalid product IDs. | Correct data/subtotal/stock feedback; invalid additions blocked; useful safe not-found page. | Browser + DB | Not run |
| Bag: BAG-01-02 | Add two then one of same product/size; separately add six then five with stock >=11. | Valid quantities combine; combined quantity above ten rejected; bag alone does not reserve DB stock. | Browser + DB | Not run |
| Bag: BAG-03-06 | Update quantity, switch size, merge lines; exceed combined stock/limit; forge cross-product/missing size. | Correct line totals; no spinner-triggered repeated reload; invalid changes do not silently discard/replace items. | Browser + edited request + DB | Not run |
| Bag: BAG-07-08 | Cancel/accept Remove and Empty actions; inspect totals and empty/table states. | Intended changes once, correct badge/message/total, readable table and useful empty state. | Browser | Not run |
| Authentication: AUTH-01-03 | Register A once; try short/mismatched passwords and duplicate email with/without client checks. | Valid account created; invalid cases rejected; no plaintext password or duplicate user. | Browser + DB | Not run |
| Authentication: AUTH-04-06 | Wrong/correct login; protected pages; forged return URL; logout and sign in as B. | Correct access/return flow; no external redirect or inherited A access. Current logout clears the session/bag. | Browser | Not run |
| Account: ACC-01-04 | Save valid details, invalid phone/postal/name and optional blanks; inspect empty/history states; forge user/email fields. | Valid changes persist/prefill; invalid ones do not overwrite; own history only; identity cannot be reassigned. | Browser + edited request + DB | Not run |
| Names/email: VAL-01-02 | Test valid name, digits/punctuation/spaces-only; test SELF, CS04-compatible domain and malformed email in editable registration/contact fields. | Chosen rules enforced; no silent character deletion. Non-SELF email-format acceptance does not permit email delivery. | Browser + server-only repeat + DB, mail disabled | Not run |
| Delivery: VAL-03-05 | Test phone prefixes/lengths/letters, postal five/six/seven digits, optional account blanks and blank delivery address. | Valid formats/optional blanks accepted; required or malformed delivery entries rejected. | Browser + server-only repeat + DB | Not run |
| Card: VAL-06-08 | Test 15/16/17 digits, letters, past/current/future expiry months, month 00/13 and CVV lengths two/three/four. | Only valid demo format accepted; past months rejected; current month valid; JS/PHP agree. | Browser + server-only repeat + DB | Not run |
| Other input: VAL-09-11, VAL-13 | Test quantity 0/-1/1.5/11/blank, unlisted contact topic, missing POST keys, unsupported payment/action, overlong values and HTML-like permitted text. | Clear rejection or safe escaped text; no truncation into a valid quantity, raw errors or unintended writes. | Edited request + DB | Not run |
| Submit cancellation: VAL-12 | Enter mismatched passwords that both meet minlength; watch network; correct confirmation. | No POST at all while client-invalid; exactly one POST after correction. PHP rejecting a POST does not pass this cancellation test. | Browser network panel | Not run |
| Checkout: CHK-01-04 | Start signed out/empty bag; check saved delivery; toggle Card/COD; forge email/price/total; reduce stock before submit. | Correct access/empty-bag flow; method-specific validation; server-controlled recipient/price/stock. | Browser + edited request + DB | Not run |
| Payment: PAY-01-02 | After implementation, submit success and decline demo numbers with otherwise valid details. | Success creates one correct Pending review order; decline changes no stock/account/order data, retains bag and sends no success mail. | Browser + DB + permitted mailbox | Not run |
| Payment: PAY-03-05 | Retry after decline, use COD with blank card fields, double-click/replay completed submission. | Retry succeeds once; COD needs no card; no duplicate order/reservation or misleading completed-attempt destination. | Browser + DB + permitted mailbox | Not run |
| Mail: MAIL-01-03 | Inspect own-account acknowledgement/link; check different-local and external recipients with sending disabled. | Correct permitted message/link; disallowed recipients rejected before mail(). | Browser + DB + mailbox; disabled-send review for rejection | Not run |
| Mail: MAIL-04-05 | Test received/updated/confirmed/cancelled independently with local mail unavailable; also save without changes. | Accurate action and mail outcomes in all four paths; unchanged save sends no unnecessary update mail. | Browser + DB + permitted local mail | Not run |
| Review: ORD-01-05 | Open owned order; edit delivery/quantity/size; swap two sizes or direct both lines to one size. | Correct visual preview; full proposed demand validated; saved values/totals and stock differences correct; historical price preserved. | Browser + DB | Not run |
| Review: ORD-06-08 | Remove one/all items; change address/first line validly but second line invalidly; forge size/quantity/missing fields. | Correct removal stock/total; all-items removal cancels with zero total; an invalid full edit leaves original data unchanged. | Browser + edited request + DB | Not run |
| Lock/cancel/access: ORD-09-12 | Save unchanged, confirm, cancel separate Pending fixtures, replay actions, and access A's order as B/signed out. | No unnecessary update; locked order cannot change; cancellation restores once; no cross-user disclosure/write/mail. Demo payment wording stays factual. | Browser + edited request + DB | Not run |
| Contact: CON-01-03 | Submit four valid fields as guest/member; blank/unlisted/overlong/markup variants; refresh/back after success. | Correct prefill/one enquiry insert; invalid data rejected; safe output and no duplicate from result-page reload. | Browser + DB | Not run |
| Help/About: HELP-01-02 | Verify story/guide/FAQ anchors; test planned live counts and size choice with available/empty/invalid result. | Appropriate images/text, working navigation and meaningful correct dynamic response. | Browser + DB | Not run |
| Layout: UI-01-04 | All routes/states at 375px/1280px and 200% zoom; keyboard only; JS-disabled journey; full seeded catalog. | Readable/operable forms/tables, labels/focus/alt text; server flow works; correct output/assets. Record observed performance against report requirements. | Browser + DB | Not run |
| SQL: SQL-01-03 | Inspect before/after records for registration, enquiry, purchase, account save, edit and cancellation. | SELECT/INSERT/UPDATE demonstrated; correct item sums/reservations; no full card/CVV stored or unrelated changes. | Browser + read-only phpMyAdmin | Not run |
| Brief/package: BRF-01-06 | Check pages/dynamic evidence, titles/images/table/form/CSS, exclusions/dependencies, Base independence and originality/contributors. | Every applicable checklist item in section 2 has evidence; no prohibited artifact or unsupported authorship claim. | Review + browser + DB | Not run |
| Report/release: DOC-01-03 | Compare report/requirements/site map/wireframes/implementation/results with package and deployed journey. | All sections agree; actual outcomes/evidence recorded; reproducible setup; no secrets/internal handover/unused JSON shipped. | Review + browser | Not run |
| Failure limits: LIM-01-02 | Compete for final unit in separate sessions; overlap edits/cancel; induce controlled later-write failure in disposable setup. | Assess negative stock, duplicate restoration, partial records and false success. Record failures/blocked tests honestly; complete atomicity is not guaranteed by taught-only fixes. | Two browsers + DB + controlled logs | Not run |

Run optional tests only after choosing and implementing the feature:

| Function / IDs | Input data and actions | Expected output | Method | Actual result |
|---|---|---|---|---|
| Admin: EXT-ADM-01-03 | Guest/customer/admin reads and POSTs; selected price 123.45; invalid/zero/negative/over-precision prices; inspect old order. | Only admin authorized; only selected valid prices update and reselect; old order price unchanged. | Browser + edited request + DB | Not implemented / Not run |
| Reports: EXT-ADM-04-05 | Known eligible orders across dates/statuses, no-sales day and ranking ties. | Correct daily product/category revenue/quantity; exclude cancelled/unconfirmed. Best product by revenue, popular size by quantity; ties by ascending product ID/EU size. | Browser + independent DB sums | Not implemented / Not run |
| Discovery: EXT-SHOP-01-02 | Switch category with filters; inspect all-sold-out product and low-stock bag. | Compatible filters retained; incompatible activity cleared; availability matches DB and server still prevents overselling. | Browser + DB | Not implemented / Not run |
| Review help: EXT-ORD-01-02 | Change unsaved quantities/removal; inspect account with zero/one/multiple Pending orders. | Proposed live total correct; saved PHP total authoritative; reminder count/links owner-scoped. | Browser + DB | Not implemented / Not run |
| Fulfilment: EXT-STATUS-01-02 | Allowed/skipped/reversed/admin-forged transitions; permitted local mail and mail-failure scenarios. | Only allowed admin transitions persist; correct customer view and truthful restricted-email outcome. | Browser + DB + permitted mailbox | Not implemented / Not run |

For the daily report option, use order creation day and currently eligible Confirmed orders, also Shipped/Delivered if those states are implemented. Label that basis explicitly. For a modern version, rerun the matching customer/validation/persistence tests against that implementation, verify its proposed live behavior and show the original Base still runs independently.

Use this evidence record for every executed case or variant:

```text
Test ID / variant:
Date / tester / browser:
URL / test database:
Starting records and stock:
Input data and actions:
Expected output:
Test method:
Actual output:
Pass / Fail / Blocked:
Screenshot / local email / database evidence:
Fix and dated retest:
```

Complete the required Base fixes and tests before modern enhancement work. A planned test is not a pass, and a documented functional failure remains unresolved until fixed and retested.
