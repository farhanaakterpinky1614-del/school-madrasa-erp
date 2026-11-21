<?php

namespace App\Http\Controllers\Tenant\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Library\Concerns\LoadsLibraryFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Library\Models\LibraryBook;
use Modules\Library\Models\LibraryCopy;

class LibraryCopyController extends Controller
{
    use LoadsLibraryFormOptions;

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $bookId = $request->integer('book_id') ?: null;

        $copies = LibraryCopy::query()
            ->with('book')
            ->when($status !== '' && in_array($status, LibraryCopy::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($bookId, fn ($q) => $q->where('library_book_id', $bookId))
            ->orderBy('accession_no')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.library.copies.index', array_merge($this->libraryFormOptions(), [
            'copies' => $copies,
            'filterStatus' => $status,
            'filterBookId' => $bookId,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        LibraryCopy::query()->create($this->payload($request));

        return back()->with('status', __('ui.library_copy_saved'));
    }

    public function update(Request $request, LibraryCopy $copy): RedirectResponse
    {
        $this->assertTenantOwned($copy);
        $copy->update($this->payload($request, $copy));

        return back()->with('status', __('ui.library_copy_saved'));
    }

    public function destroy(LibraryCopy $copy): RedirectResponse
    {
        $this->assertTenantOwned($copy);
        abort_if($copy->status === LibraryCopy::STATUS_ISSUED, 422);
        abort_if($copy->issues()->where('status', 'issued')->exists(), 422);
        $copy->delete();

        return back()->with('status', __('ui.library_copy_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?LibraryCopy $existing = null): array
    {
        $tenantId = app(\App\Core\Tenancy\CurrentTenant::class)->id();

        $data = $request->validate([
            'library_book_id' => ['required', 'integer', Rule::exists('library_books', 'id')->where('tenant_id', $tenantId)],
            'accession_no' => [
                'required', 'string', 'max:64',
                Rule::unique('library_copies', 'accession_no')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId))
                    ->ignore($existing?->id),
            ],
            'barcode' => ['nullable', 'string', 'max:64'],
            'condition' => ['required', Rule::in(LibraryCopy::CONDITIONS)],
            'status' => ['required', Rule::in(LibraryCopy::STATUSES)],
            'shelf_location' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string'],
        ]);

        $book = LibraryBook::query()->findOrFail($data['library_book_id']);
        $this->assertTenantOwned($book);

        if ($existing && $existing->status === LibraryCopy::STATUS_ISSUED) {
            $data['status'] = LibraryCopy::STATUS_ISSUED;
        }

        return $data;
    }
}
