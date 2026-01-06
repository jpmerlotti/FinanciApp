<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ManageAuditLogs;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry as KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes da Auditoria')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('causer.name')
                            ->label('Usuário'),
                        TextEntry::make('created_at')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('event')
                            ->label('Ação')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'warning',
                                'deleted' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('description')
                            ->label('Descrição'),
                    ]),

                Section::make('Alterações')
                    ->columns(2)
                    ->schema([
                        KeyValue::make('properties.old')
                            ->label('Antes')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor Original'),
                        
                        KeyValue::make('properties.attributes')
                            ->label('Depois')
                            ->keyLabel('Campo')
                            ->valueLabel('Novo Valor'),
                    ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuário'),
                \Filament\Tables\Columns\TextColumn::make('subject_type')
                    ->label('Alvo')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                \Filament\Tables\Columns\TextColumn::make('event')
                    ->label('Ação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Descrição'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->modalWidth('5xl'),
                // EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAuditLogs::route('/'),
        ];
    }
}
