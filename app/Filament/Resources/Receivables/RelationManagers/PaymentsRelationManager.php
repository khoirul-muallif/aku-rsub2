<?php

namespace App\Filament\Resources\Receivables\RelationManagers;


use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;



class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Riwayat Pembayaran';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('paid_date')
                ->label('Tanggal Bayar')
                ->default(now())
                ->required(),

            TextInput::make('amount')
                ->label('Jumlah Bayar')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            Select::make('payment_method')
                ->label('Metode Bayar')
                ->options([
                    'cash'          => 'Tunai',
                    'bank_transfer' => 'Transfer Bank',
                    'check'         => 'Cek/Giro',
                ])
                ->default('cash')
                ->required(),

            TextInput::make('reference_number')
                ->label('No. Referensi')
                ->nullable(),

            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paid_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash'          => 'Tunai',
                        'bank_transfer' => 'Transfer Bank',
                        'check'         => 'Cek/Giro',
                        default         => $state,
                    }),

                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->default('-'),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->default('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Tambah Pembayaran')
                    ->after(function () {
                        $owner = $this->getOwnerRecord();
                        $owner->refresh();
                        $owner->updatePaidAmount();
                        Notification::make()
                            ->title('Pembayaran berhasil dicatat!')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function () {
                        $owner = $this->getOwnerRecord();
                        $owner->refresh();
                        $owner->updatePaidAmount();
                    }),
                DeleteAction::make()
                    ->after(function () {
                        $owner = $this->getOwnerRecord();
                        $owner->refresh();
                        $owner->updatePaidAmount();
                    }),
            ]);
    }
}