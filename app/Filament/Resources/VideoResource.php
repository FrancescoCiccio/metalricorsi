<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Filament\Resources\VideoResource\RelationManagers;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationGroup = 'Video';

    protected static ?string $modelLabel = 'Video';

    protected static ?string $pluralModelLabel = 'Video';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order')
                    ->label('Ordine')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->rules('required|string|max:255')
                    ->columnSpan(3),

                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull()
                    ->rules('nullable|string'),

                Forms\Components\SpatieTagsInput::make('tags')
                    ->type('videos')
                    ->suggestions($videoTags = \Spatie\Tags\Tag::getWithType('videos')->pluck('name')->toArray())
                    ->columnSpan('full'),

                Forms\Components\TextInput::make('video_path')
                    ->label('Percorso Video (YouTube o Vimeo)')
                    ->columnSpanFull()
                    ->rules('nullable|string|max:255'),
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
                    ->sortable()
                    ->wrap(),

            ])
            ->reorderable('order')
            ->defaultSort('order', 'asc')
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
