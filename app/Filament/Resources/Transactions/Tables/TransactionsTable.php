<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Descrição/Título')
                    ->description(fn (Transaction $record): ?string => $record->description)
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('amount_cents')
                    ->label('Valor')
                    ->numeric()
                    ->formatStateUsing(fn (int $state): string => number_format($state / 100, 2, ',', '.'))
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->label('Data da transação')
                    ->date('d/m/Y')
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('status')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('payment_proof')
                    ->label('Comprovante')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('recipient')
                    ->label('Beneficiário/Recebedor')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(TransactionTypes::class)
                    ->query(fn ($query, $value) => $query->when($value, fn ($q, $value) => $q->where('type', $value))),
                SelectFilter::make('status')
                    ->options(TransactionStatuses::class)
                    ->query(fn ($query, $value) => $query->when($value, fn ($q, $value) => $q->where('status', $value))),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
