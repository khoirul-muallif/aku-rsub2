<?php

namespace App\Filament\Resources\Journals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JournalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('journal_number')
                    ->searchable(),
                TextColumn::make('journal_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('period_year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('period_month')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->searchable(),
                TextColumn::make('reference_number')
                    ->searchable(),
                TextColumn::make('unit_code')
                    ->searchable(),
                TextColumn::make('budget_code')
                    ->searchable(),
                TextColumn::make('transaction_code')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('posted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('posted_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('approved_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('total_debit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_credit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
