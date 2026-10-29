# ReStep Manual Test Cases

Prepared: 29 October 2026. Baseline code: `7e73a56`. These are acceptance tests for the current features and planned fixes, not a record that they have passed. Every result starts as **Not run**. Optional features are identified separately.

Automated implementation checks for steps 1-4 are recorded separately in `STEPS_1_TO_4_RESULTS.md`. They do not fill in this manual workbook's actual-result cells; record manual execution independently.

Execution plan: [Base Version plan](superpowers/plans/2026-10-29-restep-baseline.md).

## How to run and record

1. Use the actual XAMPP URL and a disposable test database imported from schema.sql then seed.sql. Never reset an existing working database just to obtain fixtures.
2. Record browser, date, tester, URL and database name. Start Apache/MySQL; enable Mercury only for the student's exact permitted local account after reviewing the mail guard.
3. Let SELF mean that exact account. AUTH-01 establishes account A with SELF once; do not pre-register A and then expect that test to register it again. For a registration retest use a fresh disposable pre-registration state, without deleting evidence from an existing run. Account B may use another address for ownership/login tests, but must never receive mail. Registration itself does not send mail. Use only dummy passwords/cards.
4. Select shoe P with two sizes A/B in stock, one size with zero stock, and another shoe Q. Record IDs, prices and starting stock in phpMyAdmin. For the cumulative-quantity boundary choose stock at least 11, or prepare that fixture through the course's normal test-data setup. Use a separate fresh Pending order for each destructive order test.
5. Unless a row says otherwise, use name `Alex Tan`, phone `91234567`, address `10 Test Street`, postal `639798`, SELF, dummy password `TestPass123`, CVV `123`, and a future expiry relative to the actual test month.
6. **Browser** means manual page operation. **Browser + DB** adds before/after phpMyAdmin SELECT inspection. **Review** means checking source/package/report files. No test framework or added application library is required.
7. JavaScript disabled does not disable HTML5 validation. For server-only cases, use browser developer tools to remove client constraints for that submission or resend an edited request, then verify PHP rejects it. This is a test method, not code to ship.
8. For negative-recipient cases, review the guard first and keep sending disabled. Verify the rejection branch makes no mail call. Do not send to an external account or another localhost account to test rejection.
9. Replace Not run with Pass/Fail and actual observations. Add evidence filenames in the same cell. If blocked, record the reason. On a fix, preserve the earlier failure and append a dated retest.

Each row contains the brief's required **input data**, **expected output**, **test method** and **test result**. Steps appear with the input. A test containing several inputs passes only when every listed variant has been checked.

## Environment and deployment — ENV

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| ENV-01 | Follow README in a clean test installation; import schema then seed; open the home page. | Database connects, catalog images load, no PHP errors; setup instructions match reality. | Browser + DB | Not run |
| ENV-02 | Open catalog/product/account/help through navigation at the deployed URL; open the acknowledgement link from MAIL-01. | Paths, stylesheet, script, local images and email link use the correct deployment location. | Browser | Not run |
| ENV-03 | Complete sign-up, purchase, local email, review/edit and confirmation on the environment used for marking. | Full Theme 2 journey works there, not only in an editor or a different local path. | Browser + DB + permitted mailbox | Not run |

## Home and collections — HOME

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| HOME-01 | Open home; follow hero, Shop all, Sport and Lifestyle links. | Correct destinations, relevant images/text and descriptive title; no broken links. | Browser | Not run |
| HOME-02 | Compare hero price, displayed product count and Featured products with products table; change a test product price through normal test-data setup and reload. | Displayed price/count follow the database. Fixed selections are labelled Featured unless real popularity is implemented. | Browser + DB | Not run |

## Catalog search, filters and sorting — CAT

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| CAT-01 | Search a known name, colour, activity and description keyword separately; then submit from header using Enter and its visible button. | Relevant matching results and count for each search; both header submission methods work. | Browser + DB | Not run |
| CAT-02 | Select Sport, then Lifestyle; choose a compatible activity. | Only matching category/activity products appear; activity options fit the chosen category. | Browser + DB | Not run |
| CAT-03 | Select EU size and maximum price, then combine category/activity/search/size/price using the filter form. | Every displayed shoe satisfies all selected constraints; size filter includes only available stock. | Browser + DB | Not run |
| CAT-04 | Apply price low-high, price high-low and name sort; include equal-price items. | Correct nondecreasing/nonincreasing/alphabetical order; ties do not lose items. | Browser | Not run |
| CAT-05 | Search `zzzz-no-shoe`; use an incompatible combination; press Clear all filters. | Useful no-results state; clear restores the full catalog. No empty or broken page. | Browser | Not run |
| CAT-06 | Search an apostrophe, `<script>alert(1)</script>` and a quote-containing SQL-like string as plain text. | No execution, SQL error or unexpectedly broadened results; visible input is escaped. | Browser | Not run |

## Product details, sizes and subtotal — PRD

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| PRD-01 | Open P, inspect photo/name/colour/category/description/price and related shoes. | Matches database; related shoes match activity and omit P. | Browser + DB | Not run |
| PRD-02 | Select available size A; inspect a zero-stock size; try adding with no size. | Available size selectable; sold-out size unavailable; missing size rejected without bag change. | Browser | Not run |
| PRD-03 | Select size A; change quantity 1 to 3 and then choose a lower-stock size. | Subtotal equals database unit price times quantity to two decimals; stock feedback and limit match selection. | Browser + DB | Not run |
| PRD-04 | Open product.php with a nonexistent, negative and malformed ID. | Safe not-found output and navigation, no PHP error or unrelated product disclosure. | Browser | Not run |

## Shopping bag — BAG

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| BAG-01 | Add two of P/A, then one more of P/A, within stock/limit. | One product-size entry with quantity three; badge/subtotal/total agree; stock is not reserved merely by using the bag. | Browser + DB | Not run |
| BAG-02 | Add quantities six then five to the same size with stock >=11; try a forged quantity 11. | Combined quantity above ten is rejected without silent truncation or an unconfirmable order. | Browser + DB | Not run |
| BAG-03 | Add Q as a second line; change P quantity using spinner/text; press Update bag once. | Quantity can be edited without repeated page reloads; both lines/totals save correctly. | Browser | Not run |
| BAG-04 | Change P/A to P/B; then change two entries of P into the same size, within and above combined limit/stock. | Valid same-product change/merge preserves correct quantities and total; excessive merged demand is rejected. | Browser + DB | Not run |
| BAG-05 | Submit another product's size ID or nonexistent size ID for a P line. | Invalid selection rejected clearly; valid existing bag is not silently lost or converted to Q. | Browser + DB | Not run |
| BAG-06 | Make test stock lower than an existing bag quantity, then update/check out; also test a zero-stock target size. | No order exceeds current stock; clear explanation/update path; zero-stock size cannot be selected or purchased. | Browser + DB | Not run |
| BAG-07 | Cancel and accept remove-item confirmation; repeat for Empty bag. | Cancel preserves contents; accept changes only intended contents, badge/total and message. Empty bag has a useful shop link. | Browser | Not run |
| BAG-08 | Inspect table for multiple lines, different quantities and an empty bag. | Headings/caption and total are clear; subtotal arithmetic and summary agree; table is usable at narrow width. | Browser | Not run |

## Registration, login, logout and access — AUTH

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| AUTH-01 | Register A with SELF, valid name and matching dummy passwords. | One user created; signed-in state/name appears; password is not stored as plain text. | Browser + DB | Not run |
| AUTH-02 | Register with a seven-character password; then mismatched confirmation. Repeat with client checks bypassed. | Both rejected; no user/session creation. | Browser + DB | Not run |
| AUTH-03 | Register with an existing email; repeat with browser checks bypassed. | Duplicate rejected clearly; existing user unchanged. | Browser + DB | Not run |
| AUTH-04 | Sign out; attempt wrong password and nonexistent user; then correct credentials. | Invalid sign-in remains signed out without database error; correct sign-in succeeds. | Browser | Not run |
| AUTH-05 | Signed out, open account/checkout/another owned order link; sign in. Forge next to an external URL and an unapproved local path. | Login required; permitted return destination preserved; forged destinations fall back safely. | Browser | Not run |
| AUTH-06 | Sign out with an existing bag; reload protected pages; sign in as B. | Session is cleared, including the current implementation's bag; protected data is not available as B. A new login cannot inherit A's order access. | Browser | Not run |

## Shared validation rules — VAL

Run these against every relevant form listed, once normally and once with client checks bypassed. Browser feedback must reject before a successful request; PHP must reject a forged invalid submission without saving it.

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| VAL-01 | Registration/account/checkout/contact name: `Alex Tan`, `Alex1`, spaces only and punctuation. | Letters/spaces with a letter accepted; other variants rejected under the chosen CS04 rule. Input is not silently rewritten while typing. | Browser + DB; server-only repeat | Not run |
| VAL-02 | Editable registration/contact email fields: SELF, `alex.tan@school.edu.sg`, `a@b`, `alex@@school.com`, embedded spaces and a one-character final extension. Use unused valid addresses for registration variants. | SELF and chosen CS04-compatible domains accepted; malformed variants rejected. Acceptance is not permission to email non-SELF addresses. Checkout ignores posted email; test that separately in CHK-02. | Browser + DB; server-only repeat, mail disabled | Not run |
| VAL-03 | Delivery/account phone: `91234567`, `61234567`, `12345678`, seven digits and letters. | Valid chosen Singapore formats accepted; invalid nonblank formats rejected. | Browser + DB; server-only repeat | Not run |
| VAL-04 | Delivery/account postal: `639798`, five digits, seven digits and letters. | Exactly six digits accepted; other nonblank values rejected. | Browser + DB; server-only repeat | Not run |
| VAL-05 | Leave optional account phone/address/postal blank; leave required checkout/order address blank or spaces only. | Optional fields may remain blank; required delivery address rejected before save. | Browser + DB; server-only repeat | Not run |
| VAL-06 | Demo card number: 16 digits with permitted spaces; 15 digits; 17 digits; letters. | Allowed format accepted; invalid formats rejected, not changed into a different number. | Browser + DB; server-only repeat | Not run |
| VAL-07 | Expiry: previous month, current month, next month; also `01/20`, `13/30`, `00/30`, malformed separators. | Past/invalid expiry rejected; current and future month accepted. JavaScript and PHP agree. | Browser + DB; server-only repeat | Not run |
| VAL-08 | CVV `123`, `12`, `1234` and letters. | Exactly three digits accepted for Card; invalid variants rejected. | Browser + DB; server-only repeat | Not run |
| VAL-09 | Product/bag/order quantities: 1, 10, 0, -1, 11, 1.5, letters and blank, within fixture stock. | Integers 1-10 accepted subject to stock/combined limit; invalid inputs rejected before casting/saving. | Browser + DB; server-only repeat | Not run |
| VAL-10 | Contact subject: a listed choice, blank and a forged unlisted choice. | Listed choice accepted; blank/unlisted values rejected. | Browser + DB; server-only repeat | Not run |
| VAL-11 | Remove required posted keys; submit an unsupported action/payment value; submit values longer than defined database field lengths. | Clear failure, no PHP notices/raw errors and no partial user/order/enquiry write. | Edited browser request + DB | Not run |
| VAL-12 | Enter two different registration passwords that both meet minlength, with all other fields valid. Watch the network panel; correct the confirmation and submit again. | No POST request at all for the client-invalid submission; exactly one POST after correction. A PHP rejection after a POST does not pass this cancellation test. | Browser network panel | Not run |
| VAL-13 | Use HTML-sensitive text in permitted address/contact message fields, then view relevant output. | Text safely displayed or rejected by a documented field rule; no executable markup. | Browser + DB | Not run |

## Account details and order history — ACC

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| ACC-01 | Save valid changed name/phone/address/postal, reload, sign out/in and open checkout. | Details persist; header name updates; checkout prefills saved details. | Browser + DB | Not run |
| ACC-02 | Save an invalid phone/postal/name, then optional blanks; run VAL cases with JavaScript disabled. | Invalid changes do not overwrite stored details; valid optional blanks save. | Browser + DB | Not run |
| ACC-03 | Open account with no orders, then with Pending/Confirmed/Cancelled orders. Follow each order link. | Useful empty state; correct own history/date/total/status and destinations; no other user's orders. | Browser + DB | Not run |
| ACC-04 | Forge an account email/user identifier in a submitted details form. | Account email/identity cannot be reassigned by posted fields; only the signed-in user's allowed details change. | Edited browser request + DB | Not run |

## Checkout delivery and payment choice — CHK

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| CHK-01 | Signed out with bag, follow Checkout; sign in; also open Checkout with an empty bag. | Bag retained through sign-in; saved details prefill; empty bag redirects usefully rather than creating an order. | Browser | Not run |
| CHK-02 | Complete valid delivery fields; run VAL-01-09 and VAL-11; change posted email away from saved account email. | Valid delivery accepted; invalid fields rejected; forged email does not become the order recipient. | Browser + DB | Not run |
| CHK-03 | Switch Card to Cash on delivery and back, including after a validation error. | Card boxes/validation follow actual selected method; COD does not require card data; selection remains understandable with JavaScript disabled. | Browser | Not run |
| CHK-04 | Forge hidden/displayed unit price or total; submit with stock reduced after adding to bag. | Server derives prices/total from database and rechecks stock; no cheaper or oversold order from forged values. | Edited browser request + DB | Not run |

## Simulated payment outcomes — PAY

These tests are for the planned behavior; it is missing in the current checkout. Both card outcomes use valid delivery details and future expiry.

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| PAY-01 | Submit demo success card `4111 1111 1111 1111`. | Exactly one Pending review order/items with correct total; stock reduced once; bag cleared after success; permitted acknowledgement attempted; review opens. | Browser + DB + permitted mailbox | Not run |
| PAY-02 | Submit demo decline card `4000 0000 0000 0002` with otherwise valid input. | Clear decline/retry result; no successful order or stock/account change; bag retained; no success email. | Browser + DB + mailbox count | Not run |
| PAY-03 | Following PAY-02, retry with demo success card; reload/back afterward. | Exactly one successful order for the retry; no duplicate stock decrement or email from replay. | Browser + DB + permitted mailbox | Not run |
| PAY-04 | Submit Cash on delivery with empty card fields. | Correct COD order and review; no card-format errors; no claim that a card was charged. | Browser + DB + permitted mailbox | Not run |
| PAY-05 | Double-click Place order; reload/repeat its consumed POST. Inspect orders/items/stock and resulting destination. | One completed purchase, correct order destination and one stock reservation; no misleading empty-bag result for the completed attempt. | Browser + DB + permitted mailbox | Not run |

## Local order email — MAIL

Never use live delivery to an external or another local recipient. Source review and disabled sending are part of MAIL-02/03, not optional precautions after attempting a send.

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| MAIL-01 | Place a successful order as A using SELF; open acknowledgement in Thunderbird and follow its link signed out. | Correct order/items/total/address and valid URL; message reaches SELF; sign-in returns to A's order. | Browser + DB + permitted mailbox | Not run |
| MAIL-02 | With sending disabled, inspect/exercise a proposed recipient for a different localhost account. | Exact-account guard rejects before mail(); no send attempt or false success claim. | Review + disabled-send verification | Not run |
| MAIL-03 | With sending disabled, inspect/exercise an external recipient, including forged checkout email. | Non-SELF recipient cannot reach mail(); checkout ignores forged destination. | Review + disabled-send verification | Not run |
| MAIL-04 | With local mail unavailable, test received, updated, confirmed and cancelled messages independently, each using an eligible own-account fixture. Restore mail afterward. | Each database action outcome remains accurate; every page distinguishes mail failure from saved state; retrying mail does not create another purchase. Record each of the four outcomes. | Browser + DB, controlled local-mail test | Not run |
| MAIL-05 | Save a real order change, save without changes, confirm and cancel separate eligible orders. | Changed/confirmed/cancelled messages match actual state and allowed recipient; unchanged save creates no unnecessary update email. | Browser + DB + permitted mailbox | Not run |

## Theme 2 order preview and final changes — ORD

Use separate Pending fixtures unless a row explicitly continues its previous step. Record stock before and after each action.

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| ORD-01 | Open own Pending order from email and account. | Correct shoe images/colour, recorded prices, sizes, quantities, delivery, total and editable controls. This is the documented visual final-order preview. | Browser + DB | Not run |
| ORD-02 | Change phone/address/postal and save. | All delivery changes persist with unchanged item stock and total; truthful update-mail result. | Browser + DB | Not run |
| ORD-03 | Increase then decrease one quantity within stock/limit. | Stock changes by the difference; saved subtotal/total and email match the final order. | Browser + DB | Not run |
| ORD-04 | Change one item from size A to B of the same shoe. | Old reservation released once, new reservation made once; recorded price retained. | Browser + DB | Not run |
| ORD-05 | For the same product ordered in two sizes, swap both selected sizes; then direct both lines to one size. | Whole proposed demand evaluated together; valid swap works; combined limit/stock enforced without order-dependent results. | Browser + DB | Not run |
| ORD-06 | Remove one item from a multi-item order; then remove all items from another Pending order. | Removed stock restored once; remaining total correct; all-items removal sets total zero and Cancelled. | Browser + DB | Not run |
| ORD-07 | Change delivery and first line validly; make second line invalid or out of stock; save. | Entire invalid proposed edit rejected before writes: original delivery, lines, stock and total preserved; no false update email. | Browser + DB | Not run |
| ORD-08 | Submit another product's size, nonexistent size, invalid quantity, malformed fields and missing fields. | Rejected without modifying the order or stock; no raw errors. | Edited browser request + DB | Not run |
| ORD-09 | Save unchanged, then confirm valid Pending order. | Unchanged save leaves data alone; confirmation changes status once, shows locked order and truthful mail result. | Browser + DB | Not run |
| ORD-10 | Replay save/cancel/confirm requests after confirmation or cancellation. | Locked order/items/stock remain unchanged; no second confirmation/cancellation side effect. | Edited browser request + DB | Not run |
| ORD-11 | Cancel a Pending order; repeat cancellation. Test a demo card order and COD separately. | Cancelled status; stock restored once; totals follow documented cancelled-order behavior; copy promises no real charge/refund. | Browser + DB | Not run |
| ORD-12 | As B, open and submit changes for A's order; use nonexistent order ID; repeat while signed out. | Login or safe not-found outcome; no disclosure, modification, stock change or email. | Browser + edited request + DB | Not run |

## Contact enquiries — CON

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| CON-01 | As guest, submit four valid fields with a listed topic. Repeat signed in. | One enquiry per submission saved correctly; signed-in name/email prefill; appropriate confirmation. | Browser + DB | Not run |
| CON-02 | Submit blank/space-only fields, invalid name/email, unlisted topic and overlong values; bypass client checks. | Clear error and no inserted enquiry; previously typed valid data retained. | Browser + DB | Not run |
| CON-03 | Submit an apostrophe and HTML-like text in message; reload/back after successful redirect. | Text saved/escaped correctly; no code execution or duplicate insert merely from reloading the result. | Browser + DB | Not run |

## About, FAQ and size guide — HELP

These include the planned page-content improvements, not a claim that existing static bodies are already dynamic.

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| HELP-01 | Open About; compare its live collection/product figures with the database; follow Browse link. | Appropriate story/image and correct meaningful live data; working destination. | Browser + DB | Not run |
| HELP-02 | Open/close FAQ details; follow size/delivery anchors; select an EU size with stock, one without stock and a forged value. | Readable correct guide; matching available-shoe response or useful empty state; invalid choice handled safely. | Browser + DB | Not run |

## Layout, accessibility and representative performance — UI

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| UI-01 | Visit every page plus empty/not-found/error states at 375px and 1280px, then at 200% browser zoom. | Appropriate text/image/title; readable layout and tables; no obscured fields/buttons or unintended page overflow. | Browser responsive view | Not run |
| UI-02 | Use keyboard only for navigation, forms, size/payment radios, FAQ, errors and confirmation actions; inspect labels/alt text. | Operable controls, visible focus, descriptive labels/errors and sensible images/text alternatives. | Browser | Not run |
| UI-03 | Run browse/add/login/COD/review with JavaScript disabled. | Core server flow works; PHP rejects bad data; no card fields prevent COD; dynamic subtotal is optional feedback. | Browser + DB | Not run |
| UI-04 | Load full seeded catalog, apply filters repeatedly and revisit normal pages; record loading observations in the test environment. | Correct complete output without errors or broken assets; evaluate against a declared report target, not an invented performance score. | Browser network panel + DB | Not run |

## Database evidence — SQL

Use phpMyAdmin read-only SELECT inspection for evidence. Input fixtures are established separately before a test, not by resetting production data.

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| SQL-01 | Compare catalog/product/account output with SELECT results; vary a filter or signed-in account. | Server-generated output matches selected data and user scope; capture one SELECT example for report. | Browser + DB | Not run |
| SQL-02 | Compare users/enquiries/orders/order_items counts and fields before/after valid registration/contact/purchase. | Only intended rows inserted; order total equals recorded item price times quantity; no stored full card/CVV. | Browser + DB | Not run |
| SQL-03 | Compare saved account details, order edits and product_sizes before/after updates/cancellation. | UPDATE effects match displayed values and defined stock differences; capture concrete before/after evidence. | Browser + DB | Not run |

## Brief and packaging checks — BRF

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| BRF-01 | Inventory final routes and meaningful dynamic content; distinguish logout/config/includes. | One home and 4-10 additional qualifying pages; no reliance on static wrappers as the only evidence. | Review + Browser | Not run |
| BRF-02 | Inspect all routes and significant empty/error states. | Appropriate title/text/images; no purely image-based page or broken local navigation. | Review + Browser | Not run |
| BRF-03 | Inspect rendered forms/tables and CSS load. | At least one four-field form processed locally by PHP/database; effective table; external stylesheet with >=4 meaningful styles. | Review + Browser + DB | Not run |
| BRF-04 | Review final Base package/imports/form actions/links for frames, mailto actions, external forms/sites, jQuery/AJAX/JSON/framework/template dependencies. | No prohibited feature/file is shipped; unused JSON manifest excluded. Distinguish SVG namespaces and documentation mentions from executable imports/navigation. | Review | Not run |
| BRF-05 | Run Base on its own; inspect dependency/import list and development record. | Plain HTML/CSS/JS/PHP/MySQL Base works independently and was completed before modern enhancement work. | Review + Browser | Not run |
| BRF-06 | Check authors' declaration, contributors/group exception, original design drafts, no other assessed-course reuse and image attribution. | Evidence supports all non-code brief conditions; no copied site template claimed as own design. | Review | Not run |

## Report and release evidence — DOC

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| DOC-01 | Review requirements, sitemap/storyboard, wireframes and implementation against final pages/flows. | All required sections exist and agree; application requirements address usability, responsiveness, security, scale, maintainability and accessibility. | Review | Not run |
| DOC-02 | Map every baseline feature/brief row to tests and saved evidence; inspect any claimed enhancement. | Every applicable test has input/expected/method/actual result; no fabricated pass; actual enhancements have justification and separate verification. | Review | Not run |
| DOC-03 | Follow final README from the submission package; inspect included files and limitations. | Reproducible setup/deployment, correct mail restriction, no secrets/internal handover/JSON manifest; unresolved issues clearly stated. | Review + Browser | Not run |

## Concurrency and failure-recovery limits — LIM

These assess important risks. The verified taught-only changes do not guarantee multi-table atomicity. Use a disposable database, restore fixtures between cases and record failures as unresolved; do not claim a limitation passes merely because it is documented. Some failure scenarios may be blocked without suitable test controls.

| ID | Input data and steps | Expected output to assess | Method | Actual result |
|---|---|---|---|---|
| LIM-01 | Two independent browser sessions attempt the final stock unit; also overlap multi-item checkout or edit/cancel of the same order. Record both outcomes and all affected records. | Stock never negative or restored twice; successful orders and remaining stock agree. Individual guarded updates help, but full multi-write consistency is not guaranteed. | Two browsers + DB | Not run |
| LIM-02 | In a disposable environment, interrupt a required multi-item write or make a later write fail using controlled test conditions. Separately try unavailable DB service. | No false success/acknowledgement; inspect partial orders/items/stock and recovery. Any partial state or uncontrolled error is recorded as a failure/limitation, not hidden. | Controlled environment + DB + logs | Not run |

## Optional additions — EXT (run only if implemented)

These are not prerequisites merely because they appear in an earlier audit. Add separate cases if the chosen feature grows beyond the scope below.

| ID | Input data and steps | Expected output | Method | Actual result |
|---|---|---|---|---|
| EXT-ADM-01 | Open/POST Admin as guest, ordinary user and configured admin. | Server rejects non-admin reads/writes; only admin sees/actions the operator page. | Browser + DB | Not implemented / Not run |
| EXT-ADM-02 | Select one product and set price 123.45; leave another unselected; try zero, negative, text and >2 decimals, including server-only submissions. | Only selected valid prices change; invalid request rejected before partial update; values reselected/displayed. | Browser + DB | Not implemented / Not run |
| EXT-ADM-03 | View product/cart prices after update; inspect an older order. | Current purchases use new database price; historical order-item price remains unchanged. | Browser + DB | Not implemented / Not run |
| EXT-ADM-04 | Choose reporting day with independently recorded confirmed orders, other-day orders, Pending/Cancelled orders, and a no-sales day. | Correct daily product/category quantities and revenue; agreed eligible statuses only; useful zero-sales result. | Browser + DB manual sums | Not implemented / Not run |
| EXT-ADM-05 | Supply records with different revenue/quantity rankings and ties. | Best product is highest dollar sales; its popular size is highest quantity. Revenue ties use ascending product ID; size ties use ascending EU size. | Browser + DB | Not implemented / Not run |
| EXT-SHOP-01 | Apply size/price/sort, switch category, then choose an incompatible activity. | Compatible filters retained; invalid activity cleared explicitly; results match combined constraints. | Browser + DB | Not implemented / Not run |
| EXT-SHOP-02 | View a shoe with no available size and a low-stock bag line. | Sold-out card/button state and useful stock warning match database; server still prevents purchase. | Browser + DB | Not implemented / Not run |
| EXT-ORD-01 | Change quantity/removal in review before saving; then save, including invalid input. | Proposed live total updates accurately; invalid proposal identified; server saved total remains authoritative. | Browser + DB | Not implemented / Not run |
| EXT-ORD-02 | Open account with zero, one and multiple Pending orders; confirm/cancel one and reload. | Reminder count and links are correct and scoped to the owner; update after state change. | Browser + DB | Not implemented / Not run |
| EXT-STATUS-01 | Admin advances Confirmed to Shipped to Delivered; try skipped/reversed/cancelled-order transitions and forged non-admin POST. | Only allowed admin transitions persist; account/order display agrees. | Browser + DB | Not implemented / Not run |
| EXT-STATUS-02 | Change status for an order addressed to SELF; repeat with local mail unavailable and a blocked recipient using disabled sending. | Correct local notification when permitted; saved status and mail result distinguished; no prohibited send. | Browser + DB + permitted mailbox | Not implemented / Not run |

## Evidence record template

For cases needing more space, copy this record and reference its filename in the table:

```text
Test ID:
Date / tester / browser:
URL / test database:
Preconditions and stock/record snapshot:
Input data and actions:
Expected output:
Test method:
Actual output:
Pass / Fail / Blocked:
Screenshot, email and database evidence:
Fix and dated retest, if applicable:
```

