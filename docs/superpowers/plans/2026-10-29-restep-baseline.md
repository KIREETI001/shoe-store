# ReStep Base Version Execution Plan

Date: 29 October 2026. Code reviewed: commit `7e73a56`, with no tracked application changes. The pre-existing `docs/CODEX_HANDOVER.md` is an untracked working note and must stay out of the submission.

**Goal:** Complete and demonstrate every Base Version requirement before adding project features or starting the modern version.

**Approach:** Retain the existing PHP pages, shared includes, session bag and MySQL tables. Fix the current forms and order flow in place. Use the supplied class examples for new code; do not add libraries, services or a new application structure.

**Stack:** HTML, external CSS, JavaScript, PHP, MySQL and local Mercury mail on XAMPP.

**Test checklist:** [Manual test cases](../../MANUAL_TEST_CASES.md). Test results are not supplied by this plan; record actual outcomes when executing them.

## 1. Sources and decisions

The official source is [IE4727 Project List and Guidelines_2025.pdf](<C:/Users/Kireeti/Desktop/IE4727/Project/References/IE4727 Project List and Guidelines_2025.pdf>). PDF page numbers are used throughout this document.

- Page 1: Theme 2 requires browsing, purchase, email acknowledgement, and a way to verify the proposed order and make final changes.
- Page 3: All themes must consider successful and unsuccessful payment. A payment portal and its redirection do not need implementing.
- Pages 4-6: Base Version requirements, exclusions, original design and completion before modern enhancements.
- Page 7: Required report sections and test record format.
- Page 8: Send mail only to the student's own account on the local web server.

Case Studies 01-05 and the lectures provide implementation examples. Their exercise-specific tasks are not automatically additional Theme 2 requirements. The earlier pasted reviews and handover are advisory, not authorities for the brief.

Corrections retained from the combined audit:

1. Admin, sales analytics, shipment emails, a cookie feature, guest checkout and password changes are not required by Theme 2.
2. Payment failure handling is required. Invalid card formatting and a failed email are different failures and do not cover it.
3. There are home plus ten content routes. Even excluding static About and FAQ, enough dynamic routes remain. Deleting About is not necessary for the minimum page count.
4. Every normal page already has shared logo images. Login, account, contact and FAQ need more relevant imagery to remove uncertainty about the images requirement.
5. Inline JavaScript and CSS are taught. Consolidating them is a consistency change, not a blanket prohibition from the brief.
6. The current site takes no real payments. Do not promise a real refund or claim a real card was charged.
7. No supplied source establishes the earlier review's invented "Usable Websites" marking criteria. Do not cite them.
8. The earlier review's claim that the journey works end to end is not a recorded runtime result for this audit. Treat implemented source and executed tests as different evidence.

## 2. Baseline checklist against the official brief

"Present" means implemented in source, not a claim that browser testing passed. "Check" requires evidence or an explicit implementation decision. Tests below refer to the separate checklist.

| Ref | Required pointer | Current state and action | Acceptance tests |
|---|---|---|---|
| B01 | Theme 2: browse goods | Present: catalog, filters, product details and images. Verify normal and empty states. | HOME-01-02, CAT-01-06, PRD-01-04 |
| B02 | Theme 2: purchase online | Present bag/checkout; fix quantity consistency and payment outcomes. | BAG-01-08, CHK-01-04, PAY-01-05 |
| B03 | Theme 2: acknowledge by email | Present helper; restrict exact recipient and make failure messages truthful. | MAIL-01-05 |
| B04 | Theme 2: verify mock-up and make final changes | Present visual order review. Explain that the stocked-shoe preview shows image, colour, size, quantity, address and total; no custom shoe designer is specified. Fix saves before relying on it. | ORD-01-12 |
| B05 | All themes: successful/unsuccessful payment | Missing simulated decline independent of field validation. No gateway integration needed. | PAY-01-05 |
| B06 | Own design; no purchased/free/textbook templates | Authorship cannot be established from source appearance. Retain original design work and image attribution. | BRF-06, DOC-01 |
| B07 | Group of two, or permitted single-student exception | Confirm contributor names and the permitted arrangement in the submission. | BRF-06 |
| B08 | Not reused for FYP or another assessed course | Authors must confirm this; it is not a code fix. | BRF-06 |
| B09 | Home plus 4-10 additional meaningful content pages | Source count meets the range. Keep the current routes initially. Do not count logout, configuration or includes as pages. Improve About/FAQ content rather than counting a static PHP wrapper as dynamic. | BRF-01, HELP-01-02 |
| B10 | Appropriate text and images on every page | Text and shared logos present. Add contextual local images to login/account/contact/FAQ; inspect empty and error states. | BRF-02, UI-01 |
| B11 | Appropriate titles | Present shared title output. Improve the repetitive home title and verify page/error titles. | BRF-02 |
| B12 | At least one effective table | Present bag, account history and size guide. Add useful captions, table sections and heading associations; total footer where useful. | BRF-03, BAG-08, ACC-03 |
| B13 | At least one local form with four fields and PHP/database processing | Present registration/contact/checkout. Preserve local PHP actions and verify saved records. | BRF-03, AUTH-01, CON-01 |
| B14 | Database SELECT, INSERT and UPDATE | Present throughout site. Capture one working example of each in the report. The brief's word "transactions" does not itself specify SQL commit/rollback. | SQL-01-03 |
| B15 | At least one server-generated page | Present catalog/product/account/order output. Demonstrate a change in displayed data from database/session/input. | SQL-01, CAT-01, ACC-03 |
| B16 | HTML5 checks; JavaScript for remaining client-checkable entries; required PHP checks | Account format checks lack a client equivalent. Complete the validation rules below and keep PHP checks even when JavaScript is bypassed. | VAL-01-13, AUTH-02-03, ACC-02, CHK-02 |
| B17 | External CSS with at least four styles | Present. Consolidate page-specific styles for consistency, not because embedded CSS is forbidden. | BRF-03, UI-01-04 |
| B18 | No mailto/external form handlers, frames or iframes | None found in application source. Recheck rendered forms and packaged files. | BRF-04 |
| B19 | No jQuery, JSON, AJAX or prohibited base frameworks/tools | Runtime source complies. Omit/convert unused `assets/images/sources.json` outside the Base deliverable; moving it to this project's docs alone is insufficient. | BRF-04 |
| B20 | No image-only pages or prohibited external-site links | Normal pages have text. No external navigation found; Drive URLs occur in the unused manifest. SVG namespace URLs are not navigation. | BRF-02, BRF-04 |
| B21 | Design, develop, test and publish a functional application | Source exists; live operation/publication still needs evidence. Use the course-approved serving arrangement, not an assumed requirement for public Internet hosting. | ENV-01-03, UI-01-04, SQL-01-03 |
| B22 | Base complete before modern enhancements | Keep framework/API work outside this execution phase. | BRF-05, DOC-02 |
| B23 | Report: application and functional requirements, storyboard, wireframes, implementation, tests, enhancement justification if applicable | A complete report and executed results were not found in this repository. Prepare all required sections and update the existing sitemap. | DOC-01-03 |
| B24 | Mail only to own local account | Current helper permits arbitrary recipients. A domain-only localhost guard is too broad. | MAIL-01-05 |

## 3. Coding rules for these fixes

The user's taught-only restriction is stricter than the brief's list of prohibited technologies. Do not introduce an API solely because an earlier audit recommended it.

| Area | Use for the planned changes | Supplied teaching |
|---|---|---|
| HTML/CSS | Local PHP forms; labels, fieldsets, HTML5 constraints; local images and alt text; caption/thead/tbody/tfoot; existing external stylesheet. Keep existing responsive CSS. | HTML/Tables/CSS notes; Tables PDF pp. 4, 10, 15-16 |
| JavaScript | `var`, named functions, ordinary conditions/loops, `getElementById`, regex test/search, focus, alert or existing fixed error boxes, value/innerHTML updates, numeric parsing and `toFixed(2)`. | JavaScript notes; CS04 pp. 1-6 |
| Events | Register submit validation with the taught `form.onsubmit = checkForm` pattern. Use `addEventListener("change", namedFunction, false)` where demonstrated. Set handlers after the elements exist, using the taught window.onload pattern or script placement at the bottom. | JavaScript pp. 16, 62, 65-67; CS04 pp. 1, 5 |
| Submit cancellation | Return false from the property submit handler when invalid, true when valid. Returning false from an addEventListener callback does not cancel submission. CS04 requests a submit listener but the supplied examples did not establish the cancellation API needed for that approach. | JavaScript p. 65 code example; use the complete demonstrated pattern |
| Dates | JavaScript `new Date()`, `getFullYear()` and zero-based `getMonth()`; PHP `date()`; ordinary comparisons. Use the actual current month when testing. | JavaScript p. 36; PHP pp. 11, 15 |
| PHP/MySQL | Existing arrays, loops, functions, POST/GET, isset, session handling, mysqli query/fetch, integer casts, escaping, query-success checks and `$db->affected_rows`. Conditional arithmetic UPDATE with WHERE uses taught SQL. | PHP/MySQL/Session notes; MySQL pp. 20-21, 26, 30, 36-38, 41 |
| Existing safeguards | Keep existing password_hash/password_verify and output escaping. Do not replace them with MD5 to resemble an old example. They are inherited code, not a claim that every API was shown in class. | Existing implementation; Sessions p. 23 contains the historical MD5 example |

Do not add prepared-statement APIs, begin_transaction/commit/rollback, CSRF-token machinery, session_regenerate_id, new exception-handling machinery, fetch, JSON, frameworks or test libraries in this phase. They were not verified as taught for the planned base changes. Do not add DOM warning construction, classList/remove/dataset helpers or a new validation framework when fixed markup and existing error boxes suffice.

For any proposed function outside this list, locate its course example before adding it. Do not claim an existing unverified function becomes taught simply because it is already in this repository.

Approved exception during implementation, 29 October 2026: the user explicitly allowed `mysqli_report(MYSQLI_REPORT_OFF);` before the connection in `includes/db.php`. This makes PHP 8 failures return false so the taught result checks and recovery branches can execute. Preserve the setting; it is an approved compatibility exception, not a claimed lecture example.

## 4. Execution sequence

Complete tasks 1-7 and their applicable baseline tests before optional work. Change one area at a time and record the test result immediately; do not batch all verification at the end.

### Task 1 — Establish the environment and submission boundary

Files: `README.md`, `config.php`, local override (never publish credentials), image attribution and final packaging list.

- [ ] Establish the actual XAMPP URL and database connection. Make `$site_url` match it so acknowledgement links open the deployed application.
- [ ] Confirm the student's exact permitted local mailbox; do not assume the example `f31ee@localhost` belongs to the student.
- [ ] Use a separate disposable test database/import for state-changing manual tests. Record starting stock and choose fixtures as described in the checklist.
- [ ] Preserve image attribution in plain text/report material. Exclude the unused JSON manifest from the Base package, and exclude the internal handover.
- [ ] Record ENV-01-03 and BRF-04-06. Do not invent a pass when Apache, MySQL or Mercury cannot be reached.

### Task 2 — Close the mail-recipient violation

Files: `config.php`, `includes/functions.php`, `checkout.php`, `order.php`, `includes/order-actions.php`, registration hints in `login.php`.

- [ ] Add a clearly named configured permitted recipient for the student's own local account. Before `mail()`, compare the proposed recipient with that exact configured account. Reject all others without calling mail; do not silently rewrite recipients.
- [ ] Use the saved account email for checkout and ignore a forged posted email. Readonly presentation is helpful but is not the server-side restriction.
- [ ] Keep acknowledgement details and the order review link. Preserve owner-only access when that link is opened after signing in.
- [ ] Check the email result for received, updated, confirmed and cancelled messages. Distinguish an order being saved from mail being accepted. `mail()` acceptance is not proof of inbox delivery.
- [ ] Describe local-only mail clearly. Do not add external mail links or a real customer-mail promise.
- [ ] Execute MAIL-01-05 using only permitted live delivery. Rejection cases must be checked with sending disabled after reviewing the guard.

### Task 3 — Make validation consistent using class patterns

Files: `assets/script.js`, `includes/header.php`/`footer.php`, `login.php`, `account.php`, `checkout.php`, `contact.php`, `order.php`, `includes/order-actions.php`.

- [ ] Register submit checks using the property-handler pattern above; do not lose cancellation while moving handlers. Use named functions and existing fixed error boxes.
- [ ] Remove checkout's character-deleting input listeners and dynamically constructed warning spans. Preserve typed input and validate on submit.
- [ ] Names: apply the CS04 letters/spaces rule consistently to registration, account, delivery and contact names, with at least one letter. Reject all-space values. This deliberately limits accepted names for class-style alignment; it is not a claim about all real-world names.
- [ ] Email: use one documented expression implementing CS04's username/domain rules, with the explicit localhost alternative needed by Mercury. The handover's expression is an implementation of those criteria, not a literal quotation from the notes. The recipient restriction remains separate.
- [ ] Phone: keep `^[689][0-9]{7}$`; postal: keep `^[0-9]{6}$`. Optional account values may be blank. Required delivery values may not.
- [ ] Card: retain the demo 16-digit number/3-digit code checks. Validate MM/YY and reject previous months in both JavaScript and PHP; accept the current month through its end. Treat JavaScript months as 0-11. Do not hard-code October 2026 in application code.
- [ ] Validate raw quantity text as an integer before casting; reject decimals, negatives, blank values and out-of-range values instead of silently truncating. Enforce a consistent 1-10 combined quantity per product/size throughout the order flow.
- [ ] Check required POST keys, permitted payment methods, permitted contact topics and database field lengths before a write. Use the same field limits in HTML and PHP.
- [ ] Fix the extra closing span in checkout. Move FAQ styles into the stylesheet, use the existing theme colour for required markers, and remove the false clickable cursor from size rows.
- [ ] Execute VAL-01-13 plus AUTH-02-03, ACC-02, CHK-02 and ORD-08. Repeat relevant cases with browser checks bypassed to prove PHP behavior.

### Task 4 — Fix bag and order changes before adding payment outcomes

Files: `cart.php`, `checkout.php`, `includes/order-actions.php`, `order.php`, `includes/functions.php` only where needed.

- [ ] Check cumulative add quantities, merged bag lines, cart updates, checkout and order edits against the same 1-10 product/size rule and stock availability.
- [ ] Rely on Update bag for quantity edits. Avoid reloading on every spinner click. If replacing remove/empty GET links with POST forms, use the existing local-form pattern; do not claim POST alone supplies CSRF protection.
- [ ] Validate the entire proposed order change before the first delivery/item/stock write. Build arrays of old reserved quantities and proposed quantities by size. Validate size/product membership and total demand across all affected lines, including swaps.
- [ ] Calculate stock changes as differences. Do not temporarily release the old reservation before validating the proposed selection. Reserve required extra stock with conditional UPDATE and inspect affected_rows; zero-change rows do not need a decrement.
- [ ] Check necessary query results. If a later reservation fails, restore earlier extra reservations using the recorded quantities and report failure. This is best-effort recovery, not an SQL transaction.
- [ ] For cancellation, make the Pending-to-Cancelled change conditional on owner and current status; restore stock only when that transition actually changed one row. Repeated requests must not restore it again. Keep confirmation conditional on owner and Pending status too.
- [ ] Save changed details and totals only after the full request validates. Only send an update message if something actually changed, and report mail failure accurately.
- [ ] Stop displaying success when a required query fails. Record any residual data-recovery limitation; do not treat a success flag as evidence that every table stayed consistent.
- [ ] Execute BAG-01-08, ORD-01-12 and SQL-01-03. Assess LIM-01-02 separately and retain their actual outcomes.

### Task 5 — Add simulated payment success and failure

Files: `checkout.php`, order/email wording where needed, `README.md`. No real gateway, new endpoint or new framework is required.

These are proposed project rules for repeatable testing, not numbers prescribed by the brief:

- `4111 1111 1111 1111`: valid demo card with a successful simulated outcome.
- `4000 0000 0000 0002`: valid-format demo card with a declined simulated outcome.
- Cash on delivery: a separate accepted order path; no card validation is required and payment is due on delivery.

- [ ] After validation and before any order/stock write, branch on the simulated payment outcome using ordinary PHP conditions. Keep field-validation errors distinct from a declined payment.
- [ ] On success, save one complete order and its items, adjust stock, clear the bag only after necessary saves succeed, acknowledge the order to the permitted account and open its review page.
- [ ] On decline, preserve the bag, stock and saved account details, create no successful order, send no success acknowledgement and offer a retry within checkout.
- [ ] Keep order Pending/Confirmed/Cancelled as review states. They are not an unpaid/paid status. No payment-status schema expansion is necessary when declined attempts are not stored.
- [ ] Guard repeat submits with a session-held integer checkout attempt number and a hidden form field, using taught arrays/POST/session/redirect patterns. Record the owned order ID for a completed attempt. A consumed attempt returns that order before the empty-bag redirect; an unknown or mismatched attempt is rejected. A disabled button alone is not protection. Do not introduce cryptographic APIs for this demo guard.
- [ ] Keep all customer-facing payment/cancellation text factual: this is a demonstration and no real payment or refund occurs.
- [ ] Execute CHK-01-04 and PAY-01-05, then repeat the core mail and order-review tests.

### Task 6 — Remove page and usability ambiguity

Files: `index.php`, `about.php`, `faq.php`, `login.php`, `account.php`, `contact.php`, `includes/header.php`, `assets/style.css`, relevant table markup.

- [ ] Retain home plus the ten existing content routes. No Admin page is added in this baseline phase.
- [ ] Give About useful live collection/product counts. Give FAQ a simple GET size choice that shows matching in-stock shoes or a useful no-stock message. Both use current PHP/SELECT patterns; generating identical fixed rows from an array alone is not meaningful dynamic content.
- [ ] Add relevant local imagery to login, account, FAQ and contact, with useful alt text. Inspect empty and error states too.
- [ ] Add captions/head/body sections to tables and a bag total footer where useful. Keep figures consistent with the summary.
- [ ] Add a visible search submit button, a clear note that checkout requires sign-in, and a message after emptying the bag. These use ordinary HTML and the existing session message pattern.
- [ ] Rename hard-coded "Popular" to "Featured" for the baseline, unless order-based ranking is actually implemented. Read the displayed hero price and product count from MySQL so visible facts cannot drift.
- [ ] Correct the home title; retain meaningful navigation, page headings, labels and keyboard operation. Avoid unnecessary new visual effects or a redesign.
- [ ] Execute HOME-01-02, CAT-01-06, HELP-01-02, BRF-01-03 and UI-01-04.

### Task 7 — Complete evidence and approve the Base Version

Files: `README.md`, final report and wireframe assets, `docs/MEETING_SITEMAP.png` or an updated editable equivalent, manual test results.

- [ ] Write application requirements with observable checks: navigation/task completion, representative catalog load, narrow/wide layouts, keyboard operation, ownership/input/output protection, shared-file maintenance, and expected classroom scale.
- [ ] Write functional requirements mapped to the feature IDs in the manual checklist. Include payment failure and Theme 2's review/edit flow.
- [ ] Update storyboard/site map and wireframes to match the final pages and the retry/review/confirmation flow.
- [ ] Explain database tables, SELECT/INSERT/UPDATE examples, sessions, validation, simulated payments and local mail using the actual implementation. State inherited APIs and limitations honestly.
- [ ] Record input, expected output, method and actual result for every applicable baseline case. Add date, evidence and pass/fail. Never convert "not run" into a pass.
- [ ] Confirm original design, contributors/permitted group arrangement, no reuse for another assessed course, image attribution and package exclusions.
- [ ] Run the full journey in the deployment used for marking and record its URL/setup. Recheck all baseline failures after fixes and complete DOC-01-03 and BRF-01-06.
- [ ] Base acceptance requires every applicable required-feature test to pass. Record LIM-01-02 honestly; a reproduced inconsistency remains unresolved and must not be represented as full robustness or silently waived.

## 5. Limits under the taught-only restriction

The notes establish conditional UPDATE and affected_rows, but not SQL transactions or prepared-statement APIs. Validating everything first fixes the confirmed partial-validation save defect. A conditional decrement protects an individual stock reservation from going negative.

These measures do not guarantee an all-or-nothing change across multiple order/items/stock writes. Compensating UPDATEs can also fail. A crash or concurrent edit during several writes can still leave inconsistent records. LIM-01-02 assess that boundary; do not describe the result as atomic or production-ready. If that boundary fails a required acceptance scenario, list it as an unresolved failure. Do not quietly introduce untaught syntax or claim that documenting a failed requirement makes it pass.

Keep existing hashing/escaping and owner checks. Do not add the old audit's prepared statements, transaction methods, CSRF or session regeneration under the present teaching constraint. Do not claim the absence of those mechanisms is comprehensive security. They can be discussed as known limitations in the report.

## 6. A few additions to consider after the Base passes

These are elective project features, not missing Theme 2 checkboxes. Add them one at a time with their EXT tests. If adding `admin.php`, merge About into home first to retain a conservative home-plus-ten route count, and update every navigation/report reference.

| Option | Feature and reason | Files/teaching | Tests |
|---|---|---|---|
| O1 | Admin price updates and daily sales reports: gives the shop a useful operator view and follows a substantial taught case study. | `admin.php`, login/header guards, schema/seed migration and README; CS05 pp. 1-5. Use selected-product price inputs, UPDATE then SELECT. Show daily dollar/quantity totals per product and Sport/Lifestyle category; best product by dollar sales, its popular size by quantity. Break revenue ties by ascending product ID and size-popularity ties by ascending EU size. Use order creation day and eligible Confirmed orders (also Shipped/Delivered if O4 is implemented); exclude cancelled/unconfirmed orders. Label this as a report of orders placed that day which currently have an eligible status. | EXT-ADM-01-05 |
| O2 | Better product discovery: retain compatible filters when switching category, show sold-out cards/button state and useful bag stock warnings. | Existing products/product/card/cart files; PHP GET, SELECT and conditional output. No AJAX. | EXT-SHOP-01-02 |
| O3 | Better final-order review: a live proposed total and a pending-order reminder on account. | Existing script.js/order/account; CS04 arithmetic/value updates and PHP SELECT. The server total remains authoritative after saving. | EXT-ORD-01-02 |
| O4 | Fulfilment progress, if time permits: Confirmed to Shipped to Delivered, with restricted local email and truthful failure feedback. | Admin/order/account, schema status comment and mail helper. It is not a Theme 2 mandate. | EXT-STATUS-01-02 |

Do not add a cookie just to demonstrate cookies, automatic 48-hour cancellation, guest checkout or password changes before the required work. Each adds behavior and tests without closing a current brief gap.

## 7. Modern version gate

Only after the Base is complete, consider the separate `shoe-store-plus` version from the handover. React, Tailwind and Express are demonstrated options, not an obligation to use every tool. Keep the Base independently runnable and free of their dependencies/API formats. For each actual enhancement, document its benefit and rerun the matching customer journey tests against that version, plus a Base independence check. A proposed modern stack is not evidence of an implemented enhancement.

