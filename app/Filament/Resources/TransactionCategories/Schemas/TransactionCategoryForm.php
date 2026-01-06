<?php

namespace App\Filament\Resources\TransactionCategories\Schemas;

use App\Models\TransactionCategory;
use Awcodes\Palette\Forms\Components\ColorPicker;
use Awcodes\Palette\Forms\Components\ColorPickerSelect;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TransactionCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Hidden::make('id'),
                TextInput::make('title')
                    ->live(true)
                    ->required()
                    ->label('Título')
                    ->afterStateUpdated(fn (string $operation, string $state, Set $set) =>
                        $operation === 'create' ? $set('slug', str($state)->slug()) : null
                    ),

                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(TransactionCategory::class, 'slug', ignoreRecord: true,
                        modifyRuleUsing: function ($rule, $get, $record) {
                            $rule->where('organization_id', Filament::getTenant()->id);

                            if ($record instanceof TransactionCategory) {
                                return $rule->ignore($record->getKey());
                            }
                            
                            if ($id = $get('id')) {
                                return $rule->ignore($id);
                            }

                            return $rule;
                        }
                    ),

                ColorPicker::make('color')
                    ->inlineLabel(true)
                    ->label('Cor da categoria')
                    ->size('md')
                    ->storeAsKey()
                ]);
    }
}
