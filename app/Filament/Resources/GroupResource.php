<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $modalLabel = 'Gruppo';

    protected static ?string $navigationGroup = 'Downloads';

    protected static ?string $pluralModelLabel = 'Gruppi';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('title')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_default')
                    ->label('Predefinito')
                    ->helperText('Se abilitato, questo gruppo sarà selezionato per impostazione predefinita quando si aggiungono nuovi utenti.'),

                Forms\Components\Select::make('users')
                    ->label('Utenti')
                    ->multiple()
                    ->relationship('users', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('downloads')
                    ->label('Downloads')
                    ->multiple()
                    ->relationship('downloads', 'title')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('title')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
