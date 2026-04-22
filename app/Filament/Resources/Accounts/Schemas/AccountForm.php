<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Models\Account;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Akun')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Akun')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20)
                        ->placeholder('Contoh: 1.100'),

                    TextInput::make('name')
                        ->label('Nama Akun')
                        ->required()
                        ->placeholder('Contoh: Kas Bendahara'),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Klasifikasi')
                ->columns(2)
                ->schema([
                    Select::make('type')
                        ->label('Tipe Akun')
                        ->options([
                            'asset'     => 'Aset',
                            'liability' => 'Kewajiban',
                            'equity'    => 'Ekuitas',
                            'revenue'   => 'Pendapatan',
                            'expense'   => 'Beban',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('normal_side',
                                in_array($state, ['asset', 'expense']) ? 'debit' : 'credit'
                            )
                        ),

                    Select::make('normal_side')
                        ->label('Saldo Normal')
                        ->options([
                            'debit'  => 'Debit',
                            'credit' => 'Kredit',
                        ])
                        ->required(),

                    Select::make('parent_id')
                        ->label('Akun Induk')
                        ->relationship('parent', 'name')
                        ->getOptionLabelFromRecordUsing(
                            fn (Account $record) => "{$record->code} - {$record->name}"
                        )
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    TextInput::make('current_balance')
                        ->label('Saldo Awal')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),
                ]),

            Section::make('Pengaturan')
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

                    Toggle::make('is_header')
                        ->label('Akun Header')
                        ->helperText('Centang jika akun ini hanya sebagai kelompok, tidak bisa dipakai posting jurnal'),
                ]),
        ]);
    }
}