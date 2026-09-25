# Contracts & Agreements Module — User Manual

**Brick (Brickspoint HMS)** · Staff Portal

Jumps straight to the answers: [Logging in & access](#1-logging-in-and-access) · [The Overview dashboard](#2-the-overview-dashboard) · [Working with templates](#3-working-with-templates) · [Creating an agreement](#4-creating-an-agreement) · [The agreement lifecycle](#5-the-agreement-lifecycle-workflow) · [Obligations](#6-obligations-and-commitments) · [Signatures](#7-signatures) · [Amendments & documents](#8-amendments-and-documents) · [PDF export](#9-branded-pdf-export) · [Audit trail](#10-audit-trail) · [Worked sample](#worked-sample-amma-corporate-suite) · [Good to know](#good-to-know)

---

## 1. Logging in and access

Sign in to the Staff Portal with your normal staff account. If your account has the Contracts permission, you will see **Contracts & Agreements** in the left-hand sidebar. The submenu shows:

| Menu item | Purpose |
|---|---|
| **Overview** | Dashboard with counts, recent agreements and expiring agreements |
| **New Agreement** | Creates a new agreement (shown only if you can `contracts.create`) |
| **All Agreements** | Searchable list of every agreement |
| **Templates** | Reusable clause sets (shown only if you can `contracts.manage_templates`) |

### Who can do what

| Permission | Description |
|---|---|
| `access_contracts_dashboard` | See the module and its menu |
| `contracts.read` | View agreement pages and download the branded PDF |
| `contracts.create` | Create a new agreement |
| `contracts.update` | Edit agreements and move them through the workflow |
| `contracts.delete` | Delete **draft** agreements only |
| `contracts.approve` | Approve agreements (and mark Executed / Active) |
| `contracts.sign` | Record signatures |
| `contracts.manage_templates` | Create, edit and delete agreement templates |

The **Admin** role and the dedicated **Contracts Manager** role receive all of the above out of the box. Users with viewing-only rights can read and download PDFs but cannot change anything.

---

## 2. The Overview dashboard

Opening **Contracts & Agreements → Overview** shows seven counters:

- **Total Agreements** — every agreement record
- **Drafts** — not yet submitted for review
- **Awaiting Approval** — sitting in `Awaiting Approval`
- **Awaiting Signature** — anything in `Sent to Client`, `Client Review` or `Client Signed`
- **Active** — live agreements
- **Expiring < 30 Days** — active agreements whose expiry falls within the next 30 days
- **Expired** — agreements past expiry (or marked expired)

Below the counters:

- **Recent Agreements** — newest agreements with reference, client, type, value, expiry and status
- **Expiring Soon (90 days)** — a watch list of the next six active agreements to expire (red badge if within 30 days, amber if within 90)

Click any reference number to open that agreement.

---

## 3. Working with templates

Templates are reusable clause sets. Starting a new agreement from a template pre-fills the standard clauses so you only edit details, not boilerplate.

### Create a template

1. Open **Contracts & Agreements → Templates**.
2. Click **New Template**.
3. Fill in:
   - **Name** — e.g. “Corporate Accommodation – Standard”
   - **Type** — the agreement type it is meant for
   - **Description** — what this template is for (visible in the list)
   - **Clauses** — click **Add Clause** for each clause; give every clause a **title** and its **body text**
   - Switch **Active** on if the template should be selectable when creating agreements
4. Click **Create Template**.

> The module ships with pre-seeded templates covering Corporate Accommodation, Event / Banquet, Room Block, Vendor / Supplier, Long Stay and Conference. You are free to edit or deactivate them.

### Edit a template

Open **Templates →** click the template name → **Edit**. Editing a template **does not change agreements already created from it** — only future agreements started from the template.

### Deactivate vs delete

- Set the Active switch off to hide an inactive template from the New Agreement form without losing it.
- A template **cannot be deleted** while it is referenced by any agreement. Deactivate it instead.

### Use a template

See [Creating an agreement, step 2](#step-2-optional-start-from-a-template).

---

## 4. Creating an agreement

Go to **Contracts & Agreements → New Agreement** (or **All Agreements → New Agreement**). The form is in numbered sections. Everything marked **\*** is mandatory.

### Step 1 — Agreement Information

| Field | Notes |
|---|---|
| **Title** \* | Short business title, e.g. “Corporate Room Agreement – Amara Ltd” |
| **Agreement Type** \* | Corporate Accommodation, Event / Banquet, Long Stay, Room Block, Vendor / Supplier, Travel Agent, OTA / Partner, Conference, Restaurant / Catering, Maintenance, Staff / HR, Lease, Partnership or Custom |
| **Currency** | NGN / USD / GBP / EUR |
| **Property / Branch** | e.g. “Brickspoint Asokoro” |
| **Department** | e.g. “Sales”, “F&B” |
| **Effective Date** | When the term starts |
| **Expiry Date** | When it ends (must be on/after the effective date) |
| **Agreement Value** | Whole contract value |
| **Deposit** | Any upfront deposit |
| **Auto-renewal eligible** | Marks the agreement as renewable (generates a renewal task). It **never renews silently** — a human must act |
| **Internal Notes** | Visible only to staff |

> The **agreement number is generated automatically** and cannot be typed. Format: `AGR-YYYY-0001` (e.g. `AGR-2026-0007`).

### Step 2 (optional) — Start from a template

In the right-hand “Start From Template” card, choose a template. The page reloads with that template’s **clauses already filled in**. You can then edit any clause or add/remove more.

> Choose the template **before** filling in the rest of the form — a template cannot be applied after the agreement exists. (That is what Amendments are for.)

### Step 3 — Commercial Terms

Stays, rates and invoicing specifics: **Room Type** (e.g. “Executive Studio”), **Rooms**, **Rate / Night**, **Discount %**, **Check-in**, **Check-out**, **Payment Terms** (e.g. “Monthly in advance”), **Cancellation Policy** (e.g. “14 days notice”), and the **Tax applicable** switch.

These appear on the agreement page and inside the generated PDF.

### Step 4 — Parties

Two parties are always recorded:

- **Party A — Hotel**: legal name (pre-filled), authorised representative, position, address, email, phone.
- **Party B — Client**: legal/company name, CAC registration number, contact person, position, address, email, phone.

The client entered here is what appears as the “Client” in lists and dashboards.

### Step 5 — Obligations / Commitments

Optional commitments the agreement engine watches, e.g. *“Monthly invoice by the 5th”*. For each obligation choose a **type** (Payment, Service, Delivery / Rooms, Entitlement / Benefit, Renewal, Other), a **title**, who is **responsible** (Client / Hotel / Other), an optional **Amount**, and an optional **Due date**.

### Finish

Click **Create Agreement**. The record is saved as a **Draft** (version 1), and you land back on the agreements list with a confirmation message.

---

## 5. The agreement lifecycle (workflow)

Every agreement follows a versioned workflow. Open an agreement to see the stepper across the top and the allowed “Move to” buttons beneath it.

### The stops in order

`Draft → Internal Review → Awaiting Approval → Approved → Sent to Client → Client Review → Client Signed → Hotel Signed → Executed → Active`

### What each status means and what happens next

| Status | Meaning | You can move it to… |
|---|---|---|
| **Draft** | Being worked on internally | Internal Review, Cancelled |
| **Internal Review** | In-house checks (legal/sales) | Draft, Awaiting Approval, Cancelled |
| **Awaiting Approval** | Needs an authorised approver to sign off | Draft, Approved \*, Cancelled |
| **Approved** | Signed off internally | Sent to Client, Cancelled |
| **Sent to Client** | Sent to the client | Client Review, Cancelled |
| **Client Review** | Client is reviewing | Client Signed \*, Sent to Client |
| **Client Signed** | Client has signed | Hotel Signed \* |
| **Hotel Signed** | Both sides signed | Executed \* |
| **Executed** | Contract is complete but not yet live | Active \*, Amended |
| **Active** | Agreement is live | Amended, Expired, Cancelled |
| **Amended** | Terms were changed by an amendment | Active |
| **Expired** | Term finished | Active |
| **Cancelled** | Abandoned | Draft |

\* *Requires extra permission — see below.*

### How to move an agreement forward

1. Open the agreement.
2. In the **workflow bar**, type an optional **comment / reason** in the “Comments / reason” box (it is saved to the version history).
3. Click the **“Move to:”** button for the destination status.
4. The status badge updates and a new **version snapshot** is recorded.

You can only move an agreement to a status that follows it — the system refuses illegal jumps, so you cannot skip from *Draft* straight to *Active*.

### Who can do the special moves

- **→ Approved**, **→ Executed**, **→ Active**: requires the **Approve** permission.
- **→ Client Signed**, **→ Hotel Signed**: requires the **Sign** permission. (Recording a signature does this automatically — see [Signatures](#7-signatures).)

### Deleting and editing limits

- **Delete** — only **Draft** agreements can be deleted.
- **Edit** — any agreement can be edited **until it is Executed**. Once an agreement is Executed, Active, Expired or Cancelled it is **locked** and the Edit button disappears. To change terms from then on you **create an Amendment** — never edit the executed record.

> Moving an agreement stores a version snapshot (`v1`, `v2`, …) with a “Status changed: X → Y — comment” summary. You can see the recent history on the right of the agreement page under **Version History**.

---

## 6. Obligations and commitments

Obligations capture what each side must do — payments, room deliveries, entitlements, renewals.

### Add an obligation

On the agreement page, under **Obligations & Commitments**, use the **“+ Add an obligation”** panel: choose a **type**, **title**, who is **responsible**, optional **amount**, and description. Each obligation shows an amount, due date and a **status**.

### Update obligation status

After an obligation is saved, change its status from the dropdown on the agreement page:

`Pending → In Progress → Completed` (also `Overdue` / `Cancelled`).

Setting an obligation to **Completed** stamps a completion date automatically.

> Obligations can also be created right inside the New/Edit agreement form so they are born with the contract.

---

## 7. Signatures

Signature records capture who signed, when, how, and with what verification — and each carries a tamper-evident hash.

### Record a signature

1. Open the agreement.
2. On the right, under **Signatures**, open **“+ Record a signature”**.
3. Choose **Client** or **Hotel**, the **signature type** (Click to sign / Typed / Drawn / Uploaded), the **signatory’s full name**, optional **position**, optional **OTP / ID** verification, and any **signature data** (e.g. the image key for drawn/uploaded sigs).
4. Click **Record signature**.

Recording the **client** signature automatically moves a review-ready agreement to **Client Signed**; recording the **hotel** signature moves it to **Hotel Signed** — so the workflow advances as signing happens.

> Signatures are only recorded when the agreement has reached the point where that signature is allowed (e.g. the client must sign before the hotel).

---

## 8. Amendments and documents

### Create an amendment

Only **Executed** or **Active** agreements can be amended.

1. Open the agreement.
2. Under **Amendments**, open **“+ Create an amendment”**.
3. Give it a **title**, the **description** of what changes, and an optional **effective date**.
4. Click **Create amendment**. An amendment number is generated (`AGR-2026-0004-AMD-001`) and the agreement automatically moves to **Amended**.
5. Once both parties sign, move it back to **Active**.

### Attach documents

On any agreement, use **“+ Attach a document”** to upload supporting files (PDF, Word, spreadsheets or images up to 10 MB). Attachments can be **downloaded** anytime and show who uploaded them and when.

---

## 9. Branded PDF export

Anyone with `contracts.read` can download a branded PDF of the agreement.

- From the **All Agreements** list, click the red **PDF** icon on a row.
- From the **agreement page**, click **Download PDF**.

The PDF is generated on the hotel’s **letterhead** (logo and details come from the Website Settings), and includes the parties, commercial terms, all clauses, and any **signature images** embedded from recorded signatures. Filename format: `AGR-2026-0007-corporate-room-agreement.pdf`. Every download is written to the **Audit Trail** as a `pdf_generated` event.

---

## 10. Audit trail

The right-hand **Audit Trail** panel records, oldest-to-newest, everything that happened: creation, every status change, approvals, signature records, amendment creation, PDF downloads, and every field change (with old → new values). It is the definitive “who did what, when” log — useful both for accountability and for answering client or internal queries.

---

## Worked sample — “Amma Corporate Suite”

A full lifecycle walk-through from first keystroke to a live agreement.

### 1. Create the agreement

Open **Contracts & Agreements → New Agreement** and enter:

| Field | Value |
|---|---|
| **Title** | Amma Corporate Suite – Brickspoint Asokoro |
| **Agreement Type** | Corporate Accommodation |
| **Start from Template** | Corporate Accommodation (clauses pre-fill) |
| **Currency** | NGN |
| **Property / Branch** | Brickspoint Asokoro |
| **Department** | Sales |
| **Effective Date** | 2026-01-01 |
| **Expiry Date** | 2026-12-31 |
| **Agreement Value** | 18,000,000.00 |
| **Deposit** | 3,600,000.00 |
| **Auto-renewal eligible** | Off |
| **Room Type / Rooms / Rate** | Executive Studio / 4 / 150,000.00 |
| **Discount / Payment terms** | 10% / Monthly in advance |
| **Check-in / Check-out** | 2026-01-01 / 2026-12-31 |
| **Cancellation policy** | 14 days’ notice |
| **Tax applicable** | Yes |
| **Hotel (Party A)** | Brickspoint Boutique Aparthotel — GM contact — asokoro@brickspoint.com |
| **Client (Party B)** | Amma Ltd, RC 1234567 — Ada Obi, Chief Purchasing Officer — ada.obi@amma.ng |

Add one obligation: type **Payment**, title *“Monthly invoice by the 5th for all rooms”*, responsible **Client**, amount 1,500,000.00.

Click **Create Agreement** → saved as **Draft** with number **AGR-2026-0007**.

### 2. Internal review

Open **AGR-2026-0007**. In the workflow bar, comment *“Verified room inventory available for 4 studios.”* and click **Move to: Internal Review**. Status → **Internal Review**.

### 3. Approval

Sales lead moves it to **Awaiting Approval**. The GM (who holds Approve rights) opens the agreement, adds comment *“Approved per rate card.”*, and clicks **Move to: Approved**. `approved_by` and `approved_at` are stamped; an approval record is logged. Status → **Approved**.

### 4. Send to the client

Click **Move to: Sent to Client**, then **Move to: Client Review** after the client confirms receipt.

### 5. Signatures

- **Client side:** Under **Signatures → + Record a signature**, choose **Client**, type **Click to sign**, name **Ada Obi**, position **CPO**, verification **OTP 442198**. Save → agreement auto-advances to **Client Signed**.
- **Hotel side:** record **Hotel** signature (name **Brickspoint GM**). Agreement advances to **Hotel Signed**.
- Both signatures appear on the agreement page and will be **embedded in the branded PDF**.

### 6. Execute and activate

With Approve rights, move to **Executed** then **Active**. The agreement is now **live** and shows in the **Active** counter and, closer to year-end, in **Expiring < 30 Days**.

### 7. Manage the monthly obligation

Each month, the obligation *“Monthly invoice by the 5th”* is tracked on the agreement page — set it **In Progress** during the month and **Completed** once Amma pays.

### 8. PDF to the client

From All Agreements, click the PDF icon on the AGR-2026-0007 row. A letterheaded PDF with both signatures downloads, and the action is captured in the Audit Trail.

---

## Good to know

- **Auto-renewal never renews on its own** — it only flags the agreement as renewable so someone follows up.
- **Expiry labels** on Active agreements are smart: within 7 days you see “Expires in 3d”, within 30 days “Expires in 17d”, otherwise the date itself. Expired active agreements are counted under **Expired**.
- **Templates referenced by agreements cannot be deleted** — deactivate instead.
- **Editing a template never rewrites existing agreements** — it only affects future agreements started from it.
- **Records are never hard-deleted once they leave Draft** — use Cancelled / Expired statuses to retire an agreement.
- **Version history** is a snapshot every time the status changes, so you can always reconstruct what the agreement looked like at any stage.