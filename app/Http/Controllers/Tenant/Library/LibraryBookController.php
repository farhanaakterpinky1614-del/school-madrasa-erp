<?php

namespace App\Http\Controllers\Tenant\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Library\Concerns\LoadsLibraryFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Library\Models\LibraryBook;

class LibraryBookController extends Controller
{
    use LoadsLibraryFormOptions;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $language = (string) $request->query('language', '');

        $books = LibraryBook::query()
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', '%'.$q.'%')
                        ->orWhere('author', 'like', '%'.$q.'%')
                        ->orWhere('isbn', 'like', '%'.$q.'%')
                        ->orWhere('category', 'like', '%'.$q.'%');
                });
            })
            ->when($language !== '' && in_array($language, LibraryBook::LANGUAGES, true), fn ($query) => $query->where('language', $language))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.library.books.index', array_merge($this->libraryFormOptions(), [
            'books' => $books,
            'filterQ' => $q,
            'filterLanguage' => $language,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        LibraryBook::query()->create($this->payload($request));

        return back()->with('status', __('ui.library_book_saved'));
    }

    public function update(Request $request, LibraryBook $book): RedirectResponse
    {
        $this->assertTenantOwned($book);
        $book->update($this->payload($request));

        return back()->with('status', __('ui.library_book_saved'));
    }

    public function destroy(LibraryBook $book): RedirectResponse
    {
        $this->assertTenantOwned($book);
        abort_if($book->copies()->exists(), 422);
        $book->delete();

        return back()->with('status', __('ui.library_book_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'category' => ['nullable', 'string', 'max:128'],
            'subject_tags' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'language' => ['required', Rule::in(LibraryBook::LANGUAGES)],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
