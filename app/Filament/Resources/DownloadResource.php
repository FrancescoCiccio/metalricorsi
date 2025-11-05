<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Spatie\Tags\Tag;
use App\Models\Download;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DownloadResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DownloadResource\RelationManagers;

class DownloadResource extends Resource
{
    protected static ?string $model = Download::class;

    protected static ?string $modalLabel = 'File';

    protected static ?string $navigationGroup = 'Downloads';

    protected static ?string $pluralModelLabel = 'Files';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        $tags = Tag::getWithType('downloads')->pluck('name')->toArray();


        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('order')
                    ->label('Ordine')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('title')
                    ->label('Titolo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(3),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->required()
                    ->disk('public') // Modifica il disco se necessariogit
                    ->directory('downloads')
                    ->maxSize(36 * 1024) // 36 MB in KB
                    ->rules(['max:36864']) // AGGIUNGI QUESTA RIGA
                    ->visibility('private'),
                Forms\Components\RichEditor::make('description')
                    ->label('Descrizione')
                    ->columnSpanFull(),
                Forms\Components\Select::make('users')
                    ->label('Assegna a Utenti')
                    ->multiple()
                    ->relationship('users', 'email')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('groups')
                    ->label('Assegna a Gruppi')
                    ->multiple()
                    ->relationship('groups', 'title')
                    ->searchable()
                    ->preload(),
                Forms\Components\SpatieTagsInput::make('tags')
                    ->type('downloads')
                    ->suggestions($tags)
                    ->columnSpan(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Utenti')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\TextColumn::make('groups_count')
                    ->label('Gruppi')
                    ->counts('groups')
                    ->sortable(),

                Tables\Columns\SpatieTagsColumn::make('tags')
                    ->type('downloads')
            ])
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Scarica')
                    ->url(fn(Download $record) => ''),
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
            'index' => Pages\ListDownloads::route('/'),
            'create' => Pages\CreateDownload::route('/create'),
            'edit' => Pages\EditDownload::route('/{record}/edit'),
        ];
    }
}
