<?php

namespace App\Filament\Resources\Payables\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('invoice_date')
                    ->label('Tgl Invoice')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('creditor_name')
                    ->label('Nama Supplier')
                    ->searchable(),

                TextColumn::make('account.name')
                    ->label('Akun')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Total Hutang')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('sisa_hutang')
                    ->label('Sisa')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date && $record->due_date->isPast() && $record->status !== 'paid' ? 'danger' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid'  => 'danger',
                        'partial' => 'warning',
                        'paid'    => 'success',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid'  => 'Belum Dibayar',
                        'partial' => 'Bayar Sebagian',
                        'paid'    => 'Lunas',
                        default   => $state,
                    }),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'unpaid'  => 'Belum Dibayar',
                        'partial' => 'Bayar Sebagian',
                        'paid'    => 'Lunas',
                    ]),
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