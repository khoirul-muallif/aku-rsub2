# Analisa Database RSU Banyumanik - Catatan & Saran Perbaikan

## ✅ YANG SUDAH BAIK

### 1. **Core Tables (Solid)**
- ✅ `accounts` - Struktur COA lengkap dengan hierarchy
- ✅ `journals` - Header dengan workflow status
- ✅ `journal_lines` - Detail debit-kredit
- ✅ `account_balances` - Monthly snapshots
- ✅ `coa_versions` - Change tracking
- ✅ `audit_logs` - Compliance ready

### 2. **Additional Tables (Smart)**
- ✅ `receivables` - Piutang tracking (RI, RJ, Asuransi)
- ✅ `payables` - Hutang tracking (Supplier)

### 3. **Extension Tables (Practical)**
- ✅ Extension untuk `journals` (journal_type, account_bank_code, helper_code)
- ✅ Extension untuk `journal_lines` (running_balance, helper_code)

Ini bagus untuk tracking pihak ketiga (vendor, pasien, asuransi).

---

## ⚠️ ISSUE & PERBAIKAN

### ISSUE 1: **Receivables & Payables - Missing FK**

**Status sekarang:**
```php
$table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
```

**Masalah:**
- Tracking piutang/hutang terpisah dari journal entry
- Rawan data inconsistency
- Payment tidak langsung terintegrasi

**Solusi:**
Tambahkan tabel `receivable_payments` & `payable_payments`:

```php
// payments untuk receivables (pembayaran dari debitur)
Schema::create('receivable_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('receivable_id')->constrained()->cascadeOnDelete();
    $table->foreignId('journal_id')->constrained('journals')->restrictOnDelete();
    $table->decimal('amount', 18, 2);
    $table->date('paid_date');
    $table->string('payment_method')->comment('cash, bank_transfer, check');
    $table->string('reference_number', 50)->nullable();
    $table->timestamps();
    
    $table->index('receivable_id');
    $table->index('paid_date');
});

// payments untuk payables (pembayaran ke supplier)
Schema::create('payable_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payable_id')->constrained()->cascadeOnDelete();
    $table->foreignId('journal_id')->constrained('journals')->restrictOnDelete();
    $table->decimal('amount', 18, 2);
    $table->date('paid_date');
    $table->string('payment_method')->comment('cash, bank_transfer, check');
    $table->string('reference_number', 50)->nullable();
    $table->timestamps();
    
    $table->index('payable_id');
    $table->index('paid_date');
});
```

---

### ISSUE 2: **Receivables - Missing Detail Items**

**Status sekarang:**
```php
$table->decimal('amount', 18, 2); // Total hanya
```

**Masalah:**
- Tidak ada detail item per invoice (layanan apa saja yang ditagih)
- Tidak bisa breakdown tagihan (kelas 1, kelas 2, ICU, dll)

**Solusi:**
Tambahkan tabel `receivable_items`:

```php
Schema::create('receivable_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('receivable_id')->constrained()->cascadeOnDelete();
    $table->string('service_code', 50)->comment('Kode layanan/ruang');
    $table->string('service_name')->comment('Nama layanan (Rawat Inap Kelas 1, Lab, dll)');
    $table->decimal('quantity', 10, 2)->default(1);
    $table->decimal('unit_price', 18, 2);
    $table->decimal('amount', 18, 2);
    $table->timestamps();
    
    $table->index('receivable_id');
});
```

---

### ISSUE 3: **Running Balance Calculation**

**Status sekarang:**
```php
$table->decimal('running_balance', 18, 2)->default(0)->after('credit');
```

**Masalah:**
- Field ada tapi tidak jelas logika penghitungannya
- Running balance per journal_line atau per transaksi?
- Bagaimana kalau ada multiple accounts per hari?

**Solusi:**
Clarify logika:
```php
// Option 1: Running balance per akun per hari
$table->decimal('running_balance', 18, 2)
    ->default(0)
    ->comment('Saldo berjalan akun ini hingga baris ini');

// Di aplikasi: Hitung otomatis saat insert journal_line
// SELECT SUM(debit) - SUM(credit) FROM journal_lines 
// WHERE account_id = ? AND journal_date <= ? ORDER BY created_at ASC
```

---

### ISSUE 4: **Missing: Expense/Cost Tables**

**Status sekarang:**
Hanya receivables & payables. Tidak ada untuk expenses.

**Masalah:**
- Gaji karyawan bagaimana tracking-nya?
- Pembelian supplies/inventaris?
- Maintenance & service?

**Solusi:**
Tambahkan tabel untuk tracking expenses:

```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
    $table->string('expense_number', 50)->unique();
    $table->enum('type', ['salary', 'utilities', 'maintenance', 'supplies', 'other']);
    $table->date('expense_date');
    $table->string('description');
    $table->string('vendor_name')->nullable();
    $table->decimal('amount', 18, 2);
    $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
    $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index('type');
    $table->index('expense_date');
    $table->index('status');
});
```

---

### ISSUE 5: **Permission Migration - Duplicate?**

**Dari upload Anda:**
Ada permission migration standar Laravel. Tapi ini untuk role-based access control.

**Pertanyaan:**
- Sudah plan role & permission?
- Siapa saja yang bisa buat journal, post, approve?
- Ada approval workflow?

**Rekomendasi:**
Sebelum pakai permission, clarify dulu:
```
Roles yang dibutuhkan:
- Admin: Full access
- Accountant: Create/Post journal
- Manager: Approve journal
- Viewer: View only reports
```

---

### ISSUE 6: **Filament Integration**

**Anda pakai Filament v3.5 + Laravel 13**

**Yang perlu dipertimbangkan:**
1. **Form Builders** - Input double-entry, calculation real-time
2. **Table Builders** - Display dengan filter (status, period, unit)
3. **Actions** - Post, Reverse, Approve buttons
4. **Authorization** - Pakai Filament policies
5. **Reports** - Export PDF/Excel dari Filament

**Rekomendasi:**
- Bikin Filament Resources (Account, Journal, Receivable, Payable)
- Pakai Form\Components\Repeater untuk journal_lines (dynamic)
- Custom Actions untuk post/reverse/approve
- Use Filament's Table filters

---

## 📋 CHECKLIST SEBELUM LANJUT

Sebelum saya bikin Models & Filament Resources, perlu clarify:

**1. Receivables Management**
- [ ] Siapa yang invoice pasien? (billing dept?)
- [ ] Bagaimana proses pembayaran? (cash, bank, asuransi?)
- [ ] Ada reminder untuk piutang jatuh tempo?

**2. Payables Management**
- [ ] Proses persetujuan PO sebelum pembayaran?
- [ ] Discount terms ada tidak? (COD, 30 hari, dll?)
- [ ] Approval workflow berapa tier?

**3. Journal Entry**
- [ ] Ada approval workflow sebelum posting?
- [ ] Siapa saja bisa buat? Atau hanya accounting dept?
- [ ] Budget checking? (budget available checking saat posting?)

**4. Financial Reports**
- [ ] Aged receivable report? (berapa hari outstanding?)
- [ ] Aged payable report? (berapa hari outstanding?)
- [ ] Cash flow forecast?
- [ ] Budget vs actual variance report?

**5. Audit & Compliance**
- [ ] Setiap perubahan harus log (sudah ada audit_logs)
- [ ] Posting journal tidak bisa diedit? (only reverse)
- [ ] History approval (who approved, when)?

---

## 🔧 RECOMMENDED NEXT STEPS

### **Modul-by-Modul Development:**

**Modul 1: Chart of Accounts (FOUNDATION)**
```
1.1 Model: Account + Relations
1.2 Seeder: 26 COA dari Excel
1.3 Filament Resource: AccountResource
1.4 Test: CRUD + Hierarchy
```

**Modul 2: Journal Entry (CORE)**
```
2.1 Model: Journal + JournalLine + Relations
2.2 Form Validation: Double-entry logic
2.3 Service: JournalPostingService
2.4 Filament Resource: JournalResource (dengan Repeater)
2.5 Actions: Post, Reverse buttons
2.6 Test: Create, Post, Reverse dengan validation
```

**Modul 3: Receivables (OPTIONAL but RECOMMENDED)**
```
3.1 Model: Receivable + ReceivableItem + ReceivablePayment
3.2 Filament Resource: ReceivableResource
3.3 Service: ReceivableService (payment logic)
3.4 Test: Create, Payment tracking, Status update
```

**Modul 4: Payables (OPTIONAL but RECOMMENDED)**
```
4.1 Model: Payable + PayablePayment
4.2 Filament Resource: PayableResource
4.3 Service: PayableService
4.4 Test: Create, Payment, Status update
```

**Modul 5: Reports (FINAL)**
```
5.1 Service: ReportService
5.2 Filament Pages: Trial Balance, Balance Sheet, Income Statement
5.3 Export: PDF/Excel
5.4 Filters: Date range, Account type, Unit
```

---

## ✨ SARAN FILAMENT SPECIFIC

Untuk Filament v3.5, beberapa tips:

### **1. Journal Entry Form (Complex)**
```php
// Di JournalResource create form:
Forms\Components\Section::make('Journal Header')
    ->schema([
        Forms\Components\DatePicker::make('journal_date'),
        Forms\Components\Select::make('reference_type'),
        Forms\Components\TextInput::make('reference_number'),
        ...
    ]),

Forms\Components\Section::make('Journal Lines')
    ->schema([
        Forms\Components\Repeater::make('lines')
            ->relationship('journalLines')
            ->schema([
                Forms\Components\Select::make('account_id')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('debit')
                    ->numeric()
                    ->afterStateUpdated(...), // Trigger balance check
                Forms\Components\TextInput::make('credit')
                    ->numeric()
                    ->afterStateUpdated(...),
            ])
            ->live() // Real-time validation
    ]),

Forms\Components\Placeholder::make('Balance Status')
    ->content(...), // Show debit vs credit live
```

### **2. Post Action**
```php
Tables\Actions\Action::make('post')
    ->label('Post Journal')
    ->action(function (Journal $record) {
        $service = app(JournalPostingService::class);
        $service->postJournal($record, auth()->id());
    })
    ->requiresConfirmation()
    ->visible(fn (Journal $record) => $record->status === 'draft')
```

### **3. Filters**
```php
Tables\Filters\SelectFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'posted' => 'Posted',
        'reversed' => 'Reversed',
    ]),

Tables\Filters\Filter::make('period')
    ->form([
        Forms\Components\Select::make('period_year'),
        Forms\Components\Select::make('period_month'),
    ])
```

---

## 📊 STRUCTURE RECOMMENDATION

Folder structure untuk modular development:

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── AccountResource.php
│   │   ├── JournalResource.php
│   │   ├── ReceivableResource.php
│   │   ├── PayableResource.php
│   │   └── ReportResource.php
│   ├── Pages/
│   │   ├── Reports/
│   │   │   ├── TrialBalancePage.php
│   │   │   ├── BalanceSheetPage.php
│   │   │   └── IncomeStatementPage.php
│   │   └── Dashboard.php
│   └── Widgets/
│       ├── JournalStatsWidget.php
│       ├── AccountingDashboard.php
│       └── ...
├── Models/
│   ├── Account.php
│   ├── Journal.php
│   ├── JournalLine.php
│   ├── Receivable.php
│   ├── ReceivableItem.php
│   ├── ReceivablePayment.php
│   ├── Payable.php
│   └── PayablePayment.php
├── Services/
│   ├── JournalPostingService.php
│   ├── ReceivableService.php
│   ├── PayableService.php
│   └── ReportService.php
└── Actions/ (Filament-style)
    ├── PostJournalAction.php
    ├── ReverseJournalAction.php
    └── ...
```

---

## 🎯 NEXT ACTION

**Mana yang mau kita build dulu?**

1. **Model Account + Filament Resource** (Foundation)
2. **Model Journal + Service + Filament Resource** (Core)
3. **Add Receivables Management**
4. **Add Payables Management**
5. **Reports Pages**

Pilih satu, kita kerjakan step-by-step dengan test di setiap tahap.

---

**READY? Mari kita mulai dengan modul yang mana?** 🚀
