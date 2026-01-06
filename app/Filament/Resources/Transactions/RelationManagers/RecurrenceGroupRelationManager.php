<?php

namespace App\Filament\Resources\Transactions\RelationManagers;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecurrenceGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'recurrenceGroup';

    public function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }
}
