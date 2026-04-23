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
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

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
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_credit')
                    ->label('Total Kredit')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Post Jurnal')
                    ->modalDescription('Jurnal yang sudah diposting tidak bisa diedit. Pastikan debit = kredit.')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        if ($record->total_debit != $record->total_credit) {
                            Notification::make()
                                ->title('Gagal!')
                                ->body('Total debit Rp ' . number_format($record->total_debit, 0, ',', '.') . ' tidak sama dengan kredit Rp ' . number_format($record->total_credit, 0, ',', '.'))
                                ->danger()
                                ->send();
                            return;
                        }

                        if ($record->lines()->count() < 2) {
                            Notification::make()
                                ->title('Gagal!')
                                ->body('Jurnal minimal harus memiliki 2 baris detail.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'status'    => 'posted',
                            'posted_at' => now(),
                            'posted_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Berhasil!')
                            ->body("Jurnal {$record->journal_number} berhasil diposting.")
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),
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