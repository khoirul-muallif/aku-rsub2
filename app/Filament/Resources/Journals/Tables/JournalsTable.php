<?php

namespace App\Filament\Resources\Journals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JournalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('journal_number')
                    ->label('No. Jurnal')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('journal_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('journal_type')
                    ->label('Tipe Jurnal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kas'        => 'info',
                        'bank'       => 'primary',
                        'memo'       => 'gray',
                        'pendapatan' => 'success',
                        'pembiayaan' => 'warning',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'kas'        => 'Jurnal Kas',
                        'bank'       => 'Jurnal Bank',
                        'memo'       => 'Jurnal Memo',
                        'pendapatan' => 'Jurnal Pendapatan',
                        'pembiayaan' => 'Jurnal Pembiayaan',
                        default      => $state,
                    }),

                TextColumn::make('memo')
                    ->label('Memo')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('total_debit')
                    ->label('Total Debit')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_credit')
                    ->label('Total Kredit')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'            => 'gray',
                        'posted'           => 'success',
                        'reversed'         => 'danger',
                        'pending_approval' => 'warning',
                        default            => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'            => 'Draft',
                        'posted'           => 'Posted',
                        'reversed'         => 'Reversed',
                        'pending_approval' => 'Pending Approval',
                        default            => $state,
                    }),
            ])
            ->defaultSort('journal_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'            => 'Draft',
                        'posted'           => 'Posted',
                        'reversed'         => 'Reversed',
                        'pending_approval' => 'Pending Approval',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}