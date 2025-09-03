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

                        Forms\Components\FileUpload::make('miniature_url')
                            ->label('Miniatura')
                            ->directory('course-miniatures')
                            ->image()
                            ->columnSpan(2)
                            ->rules('max:1024'),

                        Forms\Components\FileUpload::make('cover_path')
                            ->image()
                            ->directory('course-covers')
                            ->columnSpan(2)
                            ->rules('max:2458'),

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
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('webinar_url')
                            ->label('Webinar URL')
                            ->suffixIcon('heroicon-o-link')
                            ->placeholder('https://www...')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('webinar_password')
                            ->label('Webinar Password')
                            ->suffixIcon('heroicon-o-lock-closed')
                            ->placeholder('Password per accedere al webinar')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('webinar_id')
                            ->label('Webinar ID')
                            ->suffixIcon('heroicon-o-identification')
                            ->placeholder('ID del webinar')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('youtube_embed')
                            ->label('YouTube Embed Link')
                            ->suffixIcon('heroicon-o-video-camera')
                            ->placeholder('https://www.youtube.com/embed/...')
                            ->columnSpanFull(),

                        Forms\Components\Section::make('File addizionali')
                            ->description('Aggiungi file aggiuntivi come PDF, documenti Word, ecc. Questi file saranno accessibili agli utenti iscritti al corso.')
                            ->schema([
                                Forms\Components\Repeater::make('additional_resources')
                                    ->label('File addizionali')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome File')
                                            ->required()
                                            ->rules('required|string|max:255'),

                                        Forms\Components\FileUpload::make('file_path')
                                            ->label('Carica File')
                                            ->required()
                                            ->rules('required|file|max:5120'),
                                    ])
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Relatori')
                            ->description('Aggiungi i relatori del corso. Puoi inserire più relatori separandoli con una virgola.')
                            ->schema([
                                Forms\Components\Repeater::make('relators')
                                    ->label('Relatori')
                                    ->columns(1)
                                    ->columnSpanFull()
                                    ->schema([
                                        Forms\Components\FileUpload::make('photo')
                                            ->label('Foto Relatore')
                                            ->image()
                                            ->rules('nullable|image|max:1024')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome Relatore')
                                            ->required()
                                            ->rules('required|string|max:255'),
                                        Forms\Components\TextInput::make('bio')
                                            ->label('Didascalia Relatore')
                                            ->columnSpanFull()
                                            ->rules('nullable|string'),
                                    ])
                                    ->minItems(0)
                                    ->maxItems(5)
                                    ->addActionLabel('Aggiungi Relatore')
                                    ->columnSpanFull(),
                            ]),
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
