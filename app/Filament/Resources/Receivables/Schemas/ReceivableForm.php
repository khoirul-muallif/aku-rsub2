<?php

namespace App\Filament\Resources\Receivables\Schemas;

use App\Models\Account;
use App\Models\Receivable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceivableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Tagihan')
                ->columns(2)
                ->schema([
                    TextInput::make('invoice_number')
                        ->label('No. Tagihan')
                        ->default(fn () => Receivable::generateInvoiceNumber())
                        ->required()
                        ->unique(ignoreRecord: true),

                    DatePicker::make('invoice_date')
                        ->label('Tanggal Tagihan')
                        ->default(now())
                        ->required(),

                    TextInput::make('debtor_name')
                        ->label('Nama Pasien / Debitur')
                        ->required(),

                    TextInput::make('penjamin')
                        ->label('Penjamin')
                        ->placeholder('BPJS / Asuransi / Umum'),

                    Select::make('type')
                        ->label('Tipe')
                        ->options([
                            'RI'   => 'Rawat Inap',
                            'RJ'   => 'Rawat Jalan',
                            'lain' => 'Lain-lain',
                        ])
                        ->required()
                        ->default('RJ'),

                    Select::make('account_id')
                        ->label('Akun Piutang')
                        ->options(
                            Account::active()->where('type', 'asset')
                                ->where('code', 'like', '1.1%')
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"])
                        )
                        ->searchable()
                        ->required(),
                ]),

            Section::make('Nilai Tagihan')
                ->columns(2)
                ->schema([
                    TextInput::make('amount')
                        ->label('Total Tagihan')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->default(0),

                    TextInput::make('paid_amount')
                        ->label('Sudah Dibayar')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),

                    TextInput::make('discount')
                        ->label('Diskon')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),

                    DatePicker::make('due_date')
                        ->label('Jatuh Tempo'),

                    DatePicker::make('paid_date')
                        ->label('Tanggal Bayar'),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'unpaid'  => 'Belum Dibayar',
                            'partial' => 'Bayar Sebagian',
                            'paid'    => 'Lunas',
                        ])
                        ->required()
                        ->default('unpaid'),
                ]),

            Section::make('Keterangan')
                ->schema([
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(2),
                ]),
        ]);
    }
}