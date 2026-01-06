<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Filament\Resources\Counterparties\Schemas\CounterpartyForm;
use App\Filament\Resources\TransactionCategories\Schemas\TransactionCategoryForm;
use App\Models\Counterparty;
use App\Models\TransactionCategory;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Notifications\Notification;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->label('Descrição/Título')
                            ->required()
                            ->columnSpan([
                                1,
                                'md' => 2,
                                'lg' => 4
                            ]),

                        ToggleButtons::make('type')
                            ->label('Tipo de Transação')
                            ->options(TransactionTypes::class)
                            ->inline()
                            ->required()
                            ->columnSpan([
                                1,
                                'md' => 2,
                                'lg' => 3
                            ]),
                        TextInput::make('amount_cents')
                            ->label('Valor')
                            ->prefix('R$')
                            ->default(0)
                            ->formatStateUsing(fn ($state) => number_format($state / 100, 2, ',', '.'))
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/[^0-9]/', '', $state))
                            ->mask(RawJs::make(<<<'JS'
                                let value = $el.value.replace(/\D/g, '');
                                let formatted = (Number(value) / 100).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                $el.value = formatted;
                            JS))
                            ->required()
                            ->columnSpan([
                                1,
                                'md' => 2,
                                'lg' => 2
                            ]),

                        Select::make('transaction_category_id')
                            ->label('Categoria')
                            ->relationship('category', 'title')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->createOptionForm([
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Filament::getTenant()->transactionCategories()->create([
                                    'title' => $data['title'],
                                    'color' => 'primary', // Default color
                                    'slug' => str($data['title'])->slug()
                                ])->getKey();
                            })
                            ->suffixAction(
                                \Filament\Actions\Action::make('edit_category')
                                    ->icon('heroicon-m-pencil-square')
                                    ->color('gray')
                                    ->tooltip('Editar Categoria')
                                    ->visible(fn ($state) => filled($state))
                                    ->mountUsing(fn ($form, $state) => $form->fill(\App\Models\TransactionCategory::find($state)?->toArray()))
                                    ->form(function (Schema $schema): Schema {
                                        return TransactionCategoryForm::configure($schema);
                                    })
                                    ->action(function ($state, array $data) {
                                        TransactionCategory::find($state)?->update($data);
                                        Notification::make()->title('Categoria atualizada')->success()->send();
                                    })
                            )
                            ->columnSpan([
                                1,
                                'md' => 2,
                                'lg' => 3
                            ]),

                        Select::make('status')
                            ->options(TransactionStatuses::class)
                            ->default(TransactionStatuses::default())
                            ->prefixIcon(fn (Get $get) => $get('status')?->getIcon())
                            ->required()
                            ->live()
                            ->selectablePlaceholder(false)
                            ->columnSpan([
                                1,
                                'md' => 2,
                                'lg' => 3
                            ]),

                        Select::make('counter_party_id')
                            ->label('Contraparte')
                            ->options(fn () => Filament::getTenant()->counterParties()->orderBy('name', 'asc')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema): Schema => CounterpartyForm::configure($schema))
                            ->createOptionUsing(function (array $data): Counterparty {
                                return Filament::getTenant()->counterParties()->create($data)->getKey();
                            })
                            ->columnSpan([
                                1,
                                'md' => 2,
                                'lg' => 3
                            ]),
                            
                            DatePicker::make('due_date')
                                ->label('Data')
                                ->required()
                                ->default(now())
                                ->columnSpan([
                                    1,
                                    'md' => 2,
                                    'lg' => 3
                                ]),

                            Grid::make(3)
                                ->schema([
                                    Toggle::make('repeat_transaction')
                                        ->label('Repetir lançamento?')
                                        ->onColor('success')
                                        ->live()
                                        ->columnSpanFull(),

                                    Grid::make(5)
                                        ->schema([
                                            TextInput::make('repeat_count')
                                                ->hiddenLabel()
                                                ->prefix('Mais')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->maxValue(360) // Trava de segurança
                                                ->required()
                                                ->columnSpan(2),

                                            Select::make('repeat_interval')
                                                ->hiddenLabel()
                                                ->options([
                                                    'weekly'       => 'Semanas',
                                                    'monthly'      => 'Meses',
                                                    'semiannually' => 'Semestres',
                                                    'annually'     => 'Anos',
                                                ])
                                                ->default('monthly')
                                                ->required()
                                                ->columnStart(3)
                                                ->columnSpan(3),
                                        ])
                                        ->visible(fn (Get $get) => $get('repeat_transaction'))
                                        ->columnSpanFull(),
                                ])
                            ->columnSpan([
                                1,
                                'md' => 2,
                                'lg' => 3
                            ]),

                        Textarea::make('description')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpan([
                                'xs' => 1,
                                'md' => 3,
                                'lg' => 6,
                            ]),

                        FileUpload::make('payment_proof')
                            ->label('Comprovante')
                            ->disk('public')
                            ->directory('receipts')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->columnSpan([
                                'xs' => 1,
                                'md' => 3,
                                'lg' => 6,
                            ]),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'xs' => 1,
                        'md' => 6,
                        'lg' => 12, // Grid de 3 Colunas
                    ])
            ]);
    }
}
