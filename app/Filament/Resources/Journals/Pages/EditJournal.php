<?php

namespace App\Filament\Resources\Journals\Pages;

use App\Filament\Resources\Journals\JournalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditJournal extends EditRecord
{
    protected static string $resource = JournalResource::class;
    protected function afterSave(): void
{
    $this->record->recalculateTotals();
}

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
            ->visible(fn () => $this->record->status === 'draft'),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->status !== 'draft') {
            Notification::make()
                ->title('Tidak bisa diedit!')
                ->body("Jurnal dengan status {$this->record->status} tidak bisa diedit.")
                ->warning()
                ->send();

            $this->redirect(JournalResource::getUrl('index'));
        }
    }
}
