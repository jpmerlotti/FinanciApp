<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Tables\Columns\FileUploadColumn;
use App\Filament\Tables\Columns\MoneyInputColumn;
use App\Filament\Tables\Columns\StatusSelectColumn;
use App\Filament\Tables\Columns\TextareaColumn;
use App\Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use App\Filament\Tables\Columns\DateInputColumn;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->groups([
                Group::make('type')
                    ->label('Tipo de Transação')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),

                Group::make('due_date')
                    ->label('Por mês')
                    ->getKeyFromRecordUsing(fn (Transaction $record): string => $record->due_date ? $record->due_date->format('Y-m') : 'sem-data')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
            ])
            ->columns([
                DateInputColumn::make('due_date')
                    ->label('Vencimento')
                    ->sortable()
                    ->grow(false)
                    ->width('100px')
                    ->alignEnd()
                    ->disabledClick()
                    ->rules(['date']),
                TextInputColumn::make('title')
                    ->label('Descrição/Título')
                    ->searchable()
                    ->disabledClick()
                    ->rules(['required', 'string', 'max:255']),
                TextareaColumn::make('description')
                    ->label('Observações')
                    ->disabledClick()
                    ->rows(2)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->size('lg')
                    ->badge()
                    ->weight('bold')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('category.title')
                    ->label('Categoria')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->color(fn ($record) => $record->category?->color)
                    ->alignCenter()
                    ->searchable(),
                MoneyInputColumn::make('amount_cents')
                    ->label('Valor')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->grow(false)
                    ->width('2rem')
                    ->rules(['numeric']),
                StatusSelectColumn::make('status')
                    ->label('Status')
                    ->grow(false)
                    ->width('9rem')
                    ->alignCenter()
                    ->sortable(),
                FileUploadColumn::make('payment_proof')
                    ->label('Comprovante')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->disabledClick(),
                SelectColumn::make('recipient')
                    ->label('Contraparte')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('Nome do Pagante ou Recebedor')
                    ->options(\App\Models\Counterparty::pluck('name', 'id'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
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

                \Filament\Tables\Filters\Filter::make('due_date')
                    ->form([
                        DatePicker::make('due_from')->label('Vencimento (De)'),
                        DatePicker::make('due_until')->label('Vencimento (Até)'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('category')
                    ->label('Categoria')
                    ->relationship('category', 'title')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('counterparty')
                    ->label('Contraparte')
                    ->relationship('counterparty', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('upload_file')
                    ->label('Enviar Comprovante')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('payment_proof')
                            ->label('Comprovante')
                            ->disk('public') // Assuming public disk
                            ->directory('payment-proofs')
                            ->preserveFilenames(),
                    ])
                    ->action(function (Transaction $record, array $data) {
                        $record->update(['payment_proof' => $data['payment_proof']]);
                        
                        Notification::make()
                            ->success()
                            ->title('Comprovante enviado com sucesso!')
                            ->send();
                    })
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(true),
                DeleteAction::make()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
