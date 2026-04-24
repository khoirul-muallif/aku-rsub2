<?php

namespace App\Filament\Resources\Payables\Schemas;

use App\Models\Account;
use App\Models\Payable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Hutang')
                ->columns(2)
                ->schema([
                    TextInput::make('invoice_number')
                        ->label('No. Invoice')
                        ->default(fn () => Payable::generateInvoiceNumber())
                        ->required()
                        ->unique(ignoreRecord: true),

                    DatePicker::make('invoice_date')
                        ->label('Tanggal Invoice')
                        ->default(now())
                        ->required(),

                    TextInput::make('creditor_name')
                        ->label('Nama Supplier / Kreditur')
                        ->required(),

                    Select::make('account_id')
                        ->label('Akun Hutang')
                        ->options(
                            Account::active()->where('type', 'liability')
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"])
                        )
                        ->searchable()
                        ->required(),
                ]),

            Section::make('Nilai Hutang')
                ->columns(2)
                ->schema([
                    TextInput::make('amount')
                        ->label('Total Hutang')
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