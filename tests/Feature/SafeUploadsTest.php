<?php

use App\Models\User;
use App\Support\SafeUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

function uploadUrl(): string
{
    return URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5), [], false);
}

function webshell(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "GIF89a<?php system(\$_GET['c']); ?>");
}

// Livewire stores temporary uploads on the 'tmp-for-tests' disk when running tests.
beforeEach(fn () => Storage::fake('tmp-for-tests'));

it('rejects temporary uploads from guests', function () {
    $this->post(uploadUrl(), ['files' => [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')]])
        ->assertRedirect(route('login'));
});

it('rejects php files on the temporary upload endpoint', function (string $name) {
    $this->actingAs(User::factory()->create())
        ->postJson(uploadUrl(), ['files' => [webshell($name)]])
        ->assertStatus(422);
})->with(['shell.php', 'shell.phtml', 'shell.php.png', '.htaccess', 'shell.phar']);

it('accepts pdf and images on the temporary upload endpoint', function (UploadedFile $file) {
    $this->actingAs(User::factory()->create())
        ->postJson(uploadUrl(), ['files' => [$file]])
        ->assertOk();
})->with([
    fn () => UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf'),
    fn () => UploadedFile::fake()->image('cover.png'),
]);

it('document rules accept office files and reject scripts', function () {
    $rules = ['f' => [...SafeUploads::documentRules(5120), SafeUploads::blockDangerousNames()]];

    expect(Validator::make(['f' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')], $rules)->passes())->toBeTrue()
        ->and(Validator::make(['f' => webshell('a.php')], $rules)->passes())->toBeFalse()
        ->and(Validator::make(['f' => webshell('a.pdf.php')], $rules)->passes())->toBeFalse();
});

it('image rules reject svg and disguised php', function () {
    $rules = ['f' => [...SafeUploads::imageRules(1024), SafeUploads::blockDangerousNames()]];

    expect(Validator::make(['f' => UploadedFile::fake()->image('a.jpg')], $rules)->passes())->toBeTrue()
        ->and(Validator::make(['f' => UploadedFile::fake()->create('a.svg', 1, 'image/svg+xml')], $rules)->passes())->toBeFalse()
        ->and(Validator::make(['f' => webshell('a.php.png')], $rules)->passes())->toBeFalse();
});
