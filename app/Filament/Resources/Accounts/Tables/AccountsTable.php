<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Models\Account;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->description(fn (Account $record) => $record->parent?->name),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'asset'     => 'success',
                        'liability' => 'danger',
                        'equity'    => 'warning',
                        'revenue'   => 'info',
                        'expense'   => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'asset'     => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity'    => 'Ekuitas',
                        'revenue'   => 'Pendapatan',
                        'expense'   => 'Beban',
                        default     => $state,
                    }),

                TextColumn::make('normal_side')
                    ->label('Saldo Normal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'debit'  => 'info',
                        'credit' => 'success',
                        default  => 'gray',
                    }),

                TextColumn::make('current_balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('is_header')
                    ->label('Header')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('code')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Akun')
                    ->options([
                        'asset'     => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity'    => 'Ekuitas',
                        'revenue'   => 'Pendapatan',
                        'expense'   => 'Beban',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TernaryFilter::make('is_header')
                    ->label('Akun Header'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}