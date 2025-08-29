<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Spatie\Tags\Tag;
use App\Models\Course;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CourseResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Filament\Resources\CourseResource\RelationManagers\UsersRelationManager;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationGroup = 'Corsi';

    protected static ?string $navigationLabel = 'Corsi';

    protected static ?string $modelLabel = 'Corso';

    protected static ?string $pluralModelLabel = 'Corsi';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        $courseTags = Tag::getWithType('categories')->pluck('name')->toArray();

        return $form
            ->schema([
                //
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->rules('required')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('cover_path')
                            ->image()
                            ->columnSpanFull()
                            ->rules('max:2458')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('when')
                            ->native(false)
                            ->seconds(false)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('max_attends')
                            ->numeric()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('location')
                            ->columnSpan(2),

                        Forms\Components\SpatieTagsInput::make('tags')
                            ->type('categories')
                            ->suggestions($courseTags)
                            ->columnSpan(2)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('title')
                    ->label('Nome Corso'),

                Tables\Columns\TextColumn::make('when')
                    ->label('Data')
                    ->date('D d M Y'),

                Tables\Columns\SpatieTagsColumn::make('tags')
                    ->type('categories')

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
            UsersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
