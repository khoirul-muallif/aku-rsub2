<?php

namespace App\Filament\Resources\Journals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class JournalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('journal_number')
                    ->required(),
                DatePicker::make('journal_date')
                    ->required(),
                TextInput::make('period_year')
                    ->required()
                    ->numeric(),
                TextInput::make('period_month')
                    ->required()
                    ->numeric(),
                TextInput::make('reference_type')
                    ->required(),
                TextInput::make('reference_number'),
                Textarea::make('memo')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('unit_code'),
                TextInput::make('budget_code'),
                TextInput::make('transaction_code'),
                Select::make('status')
                    ->options([
            'draft' => 'Draft',
            'posted' => 'Posted',
            'reversed' => 'Reversed',
            'pending_approval' => 'Pending approval',
        ])
                    ->default('draft')
                    ->required(),
                DateTimePicker::make('posted_at'),
                TextInput::make('posted_by')
                    ->numeric(),
                TextInput::make('approved_by')
                    ->numeric(),
                DateTimePicker::make('approved_at'),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                TextInput::make('total_debit')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_credit')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
