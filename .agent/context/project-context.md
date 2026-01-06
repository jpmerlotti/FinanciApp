# Project Context: FinanciApp

**Last Updated:** 2026-01-06
**Tech Stack:** PHP 8.5, Laravel 12.x, Filament v4, AlpineJS v3, TailwindCSS v3/v4, PostgreSQL.

## 1. Core Philosophy
*   **Single Resource View**: The user should manage everything (edit, analyze, list) from the main `Transactions` list.
*   **Inline Editing**: Extensive use of custom inline-editable columns ("Notion-like").
*   **Automated Recurrence**: No Cron jobs for creation; uses Batch Insert at creation time.

## 2. Database Schema Refactors
### Transactions (`transactions`)
*   **Dates**: Centralized on `due_date`. Removed `transaction_date`. Added `paid_at` and `cancelled_at` (nullable datetimes).
*   **Status Automation**: `booted()` method in `Transaction` model automatically sets `paid_at` when status is `COMPLETED` and `cancelled_at` when `CANCELED`.
*   **Recurrence**: Uses `recurring_group_id` (UUID) to group series. `installment_number` and `total_installments` track progress.
*   **Money**: Stored as `amount_cents` (Integer).
*   **Relationships**: `category` (BelongsTo TransactionCategory), `recipient` (BelongsTo Counterparty), `organization` (Tenant).

### Categories (`transaction_categories`)
*   Refactored from "Tags".
*   **Relationship**: 1-to-Many with Transactions.
*   **Fields**: `title`, `slug`, `color` (Filament Palette key).

## 3. Key Components & Features

### Custom Filament Columns (`app/Filament/Tables/Columns`)
*   **`MoneyInputColumn`**: Inline currency editing. Handles `R$` prefix and cents conversion/masking via AlpineJS.
*   **`DateInputColumn`**: Inline date picker. Borderless, clean UI.
*   **`StatusSelectColumn`**: Combines a Badge (display) with a Select dropdown (edit). Custom styled options.
*   **`TextInputColumn` / `TextareaColumn`**: Custom implementations for clean, borderless inline editing with success notifications.
*   **`FileUploadColumn`**: Inline file upload for payment proofs (triggered via `upload_file` action).

### Transaction Resource (`TransactionResource`)
*   **Table Grouping**: Transactions are grouped by **Month** (`due_date` format 'F Y').
*   **Form**:
    *   **Category**: Single-select with **Inline Creation** and **Edit Action** (Pencil icon) to modify color/title on the fly.
    *   **Recurrence**: Logic handled by `HandlesRecurrence` trait.
        *   **Creation**: Generates up to `$count` future installments immediately.
        *   **Editing**: If a recurring transaction is edited, updates are batch-propagated to **future pending** installments in the same group (Title, Amount, Category, etc.).
*   **Auto-Tagging**: Recurring transactions get a suffix (e.g., "Salary - JAN") automatically generated based on the due date month.

### Dashboard & Widgets
*   **`FinancialStatsOverview`**: Chart-based stats widget filtered by date range.
*   **`DueTransactionsWidget`**: Custom Blade view widget displaying a stylized list of due/overdue transactions (Infolist style).
*   **`Balance`**: Widget logic updated to use `due_date`.

### Logic & Automation
*   **Login Notification**: `CheckDueTransactions` listener checks for due transactions on login and sends a detailed Notification (Toast) to the user.
*   **Recurrence Trait** (`HandlesRecurrence`): Centralizes logic for `createRecurringTransactions` and `updateFutureRecurringTransactions`. Smartly handles Month suffixes in titles.

## 4. Configuration
*   **Money Handling**: Front-end sees float/formatted string, Back-end receives integer cents.
*   **Date Formats**: Portuguese (pt_BR) locale used for Month names.
*   **Tenancy**: All models (except User) are scoped by `Organization`.

## 5. File Structure Highlights
*   `app/Filament/Resources/Transactions/Concerns/HandlesRecurrence.php`: Recurrence Logic.
*   `app/Filament/Tables/Columns/`: Custom Column implementations.
*   `app/Models/Transaction.php`: Status/Date automation logic (`booted`).
