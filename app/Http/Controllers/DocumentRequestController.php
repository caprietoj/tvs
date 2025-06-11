<?php
namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $perPage = 10; // Number of items per page

        // If user has admin or rrhh role, show all requests
        if ($user->hasAnyRole(['Admin', 'rrhh'])) {
            $requests = DocumentRequest::with(['user', 'document'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } else {
            // Otherwise, show only user's requests
            $requests = DocumentRequest::with(['user', 'document'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        }

        return view('document-requests.index', compact('requests'));
    }

    public function create()
    {
        $documents = Document::all();
        return view('document-requests.create', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'description' => 'required|string'
        ]);

        $request->merge(['user_id' => auth()->id()]);
        DocumentRequest::create($request->all());
        return redirect()->route('document-requests.index')->with('success', 'Solicitud creada exitosamente');
    }

    public function edit(DocumentRequest $documentRequest)
    {
        $documents = Document::all();
        return view('document-requests.edit', compact('documentRequest', 'documents'));
    }

    public function update(Request $request, DocumentRequest $documentRequest)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'description' => 'required|string',
            'status'      => 'required|in:abierto,en proceso,cerrado',
            'certificate' => 'nullable|file|mimes:pdf,jpg,docx'
        ]);

        $data = $request->all();
        // Remove certificate from $data to avoid saving it in DB
        unset($data['certificate']);

        if ($request->hasFile('certificate')) {
            $certificateFile = $request->file('certificate');
            $originalName = $certificateFile->getClientOriginalName();
            // New name: documentRequestID-userSlug-originalFilename.extension
            $customName = $documentRequest->id . '-' . Str::slug($documentRequest->user->name) . '-' .
                          pathinfo($originalName, PATHINFO_FILENAME) . '.' . $certificateFile->getClientOriginalExtension();
            $path = $certificateFile->storeAs('certificates', $customName, 'public');
            
            // Send email to user with the attachment using custom file name
            \Mail::to($documentRequest->user->email)
                ->send(new \App\Mail\DocumentResolved($documentRequest, $path));
        }

        $documentRequest->update($data);
        return redirect()->route('document-requests.index')->with('success', 'Solicitud actualizada exitosamente');
    }
}
