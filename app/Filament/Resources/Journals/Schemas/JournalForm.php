<?php

namespace App\Filament\Resources\Journals\Schemas;

use App\Models\Account;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Jurnal')
                ->columns(3)
                ->schema([
                    TextInput::make('journal_number')
                        ->label('No. Jurnal')
                        ->default(fn () => \App\Models\Journal::generateJournalNumber())
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    DatePicker::make('journal_date')
                        ->label('Tanggal')
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $date = \Carbon\Carbon::parse($state);
                                $set('period_year', $date->year);
                                $set('period_month', $date->month);
                            }
                        }),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft'            => 'Draft',
                            'posted'           => 'Posted',
                            'reversed'         => 'Reversed',
                            'pending_approval' => 'Pending Approval',
                        ])
                        ->default('draft')
                        ->required(),

                    TextInput::make('period_year')
                        ->label('Tahun')
                        ->numeric()
                        ->required(),

                    TextInput::make('period_month')
                        ->label('Bulan')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(12)
                        ->required(),

                    Select::make('reference_type')
                        ->label('Tipe Referensi')
                        ->options([
                            'manual'           => 'Manual',
                            'invoice'          => 'Invoice',
                            'purchase_order'   => 'Purchase Order',
                            'cash_receipt'     => 'Cash Receipt',
                            'reversal'         => 'Reversal',
                        ])
                        ->required(),

                    TextInput::make('reference_number')
                        ->label('No. Referensi')
                        ->nullable(),

                    TextInput::make('unit_code')
                        ->label('Kode Unit'),

                    TextInput::make('budget_code')
                        ->label('Kode Budget'),
                ]),

            Section::make('Keterangan')
                ->columns(1)
                ->schema([
                    TextInput::make('memo')
                        ->label('Memo')
                        ->placeholder('Keterangan singkat'),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->placeholder('Deskripsi lengkap transaksi'),
                ]),

            Section::make('Detail Jurnal')
                ->schema([
                    Repeater::make('lines')
                        ->label('')
                        ->relationship('lines')
                        ->schema([
                            Select::make('account_id')
                                ->label('Akun')
                                ->options(
                                    Account::active()
                                        ->posting()
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($a) => [
                                            $a->id => "{$a->code} - {$a->name}"
                                        ])
                                )
                                ->searchable()
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('line_description')
                                ->label('Keterangan')
                                ->columnSpan(2),

                            TextInput::make('debit')
                                ->label('Debit')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),

                            TextInput::make('credit')
                                ->label('Kredit')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                        ])
                        ->columns(6)
                        ->addActionLabel('+ Tambah Baris')
                        ->minItems(2)
                        ->defaultItems(2)
                        ->reorderable()
                        ->collapsible(),
                ]),

            Section::make('Total')
                ->columns(2)
                ->schema([
                    TextInput::make('total_debit')
                        ->label('Total Debit')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('total_credit')
                        ->label('Total Kredit')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->disabled()
                        ->dehydrated(),
                ]),
        ]);
    }
}