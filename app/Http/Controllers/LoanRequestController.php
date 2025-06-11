<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Mail\LoanRequestCreated;
use App\Mail\LoanRequestReviewed;
use App\Mail\LoanRequestFinalized;
use App\Notifications\LoanRequestReadyForApproval;
use App\Notifications\LoanRequestApproved;
use App\Notifications\LoanRequestRejected;
use PDF;
use Carbon\Carbon;

class LoanRequestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of loan requests.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Show all requests to Admin, HR and Finance roles
        if ($user->hasRole(['Admin', 'rrhh', 'financiera'])) {
            $loanRequests = LoanRequest::with('user')->latest()->get();
        } else {
            $loanRequests = LoanRequest::where('user_id', $user->id)->latest()->get();
        }
        
        return view('loan-requests.index', compact('loanRequests'));
    }

    /**
     * Show the form for creating a new loan request.
     */
    public function create()
    {
        $user = auth()->user();
        return view('loan-requests.create', compact('user'));
    }

    /**
     * Store a newly created loan request.
     */
    public function store(Request $request)
    {
        \Log::info('Iniciando solicitud de préstamo', $request->all());

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'installments' => 'required|integer|min:1',
            'installment_type' => 'required|in:monthly,biweekly',
            'installment_value' => 'required|numeric|min:0.01',
            'deduction_start_date' => 'required|date',
            'full_name' => 'required|string|max:255',
            'document_number' => 'required|string|max:20',
            'position' => 'required|string|max:255',
            'signature' => 'required|string|max:255',
            // Campos adicionales
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'employment_years' => 'nullable|integer|min:0',
            'contract_type' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:1000',
            'bank_name' => 'nullable|string|max:255',
            'account_type' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
        ]);

        \Log::info('Validación exitosa', $validated);

        try {
            DB::beginTransaction();

            $data = [
                'user_id' => Auth::id(),
                'amount' => $validated['amount'],
                'installments' => $validated['installments'],
                'installment_type' => $validated['installment_type'],
                'installment_value' => $validated['installment_value'],
                'deduction_start_date' => $validated['deduction_start_date'],
                'full_name' => $validated['full_name'],
                'document_number' => $validated['document_number'],
                'position' => $validated['position'],
                'signature' => $validated['signature'],
                'status' => 'pending',
                // Campos adicionales
                'department' => $validated['department'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'employment_years' => $validated['employment_years'] ?? null,
                'contract_type' => $validated['contract_type'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'account_type' => $validated['account_type'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
            ];

            \Log::info('Intentando crear solicitud de préstamo con datos', $data);
            
            $loanRequest = LoanRequest::create($data);

            // Send different email templates to applicant and HR
            Mail::to(Auth::user()->email)
                ->send(new LoanRequestCreated($loanRequest, 'created-applicant'));

            Mail::to(config('loan_emails.hr'))
                ->send(new LoanRequestCreated($loanRequest, 'created-hr'));

            // Notify treasury about new loan request
            Mail::to(config('loan_emails.treasury'))
                ->send(new LoanRequestCreated($loanRequest, 'created-hr'));

            DB::commit();

            return redirect()->route('loan-requests.index')
                ->with('success', 'Solicitud enviada correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating loan request: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->withInput()
                ->with('error', 'Error al crear la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified loan request.
     */
    public function show(LoanRequest $loanRequest)
    {
        // Check if the user can view this request (either admin/HR or the owner)
        if (!Auth::user()->hasRole(['Admin', 'rrhh']) && Auth::id() !== $loanRequest->user_id) {
            abort(403, 'No autorizado para ver esta solicitud');
        }
        
        return view('loan-requests.show', compact('loanRequest'));
    }

    /**
     * Show the form for editing the specified loan request.
     */
    public function edit(LoanRequest $loanRequest)
    {
        // Check if the user can edit this request (admin/HR or the owner if it's pending)
        if (!Auth::user()->hasRole(['Admin', 'rrhh']) && 
            (Auth::id() !== $loanRequest->user_id || $loanRequest->status !== 'pending')) {
            abort(403, 'No autorizado para editar esta solicitud');
        }
        
        return view('loan-requests.edit', compact('loanRequest'));
    }

    /**
     * Update the specified loan request.
     * This method handles both HR approval and Admin final approval.
     */
    public function update(Request $request, LoanRequest $loanRequest)
    {
        // HR update flow
        if (Auth::user()->hasRole('rrhh') && $loanRequest->status === 'pending') {
            $validated = $request->validate([
                'current_salary' => 'required|numeric|min:0',
                'has_active_loans' => 'required|boolean',
                'current_loan_balance' => 'nullable|numeric|min:0',
                'has_advances' => 'required|boolean',
                'advances_amount' => 'nullable|numeric|min:0',
                'hr_signature' => 'required|string|max:255'
            ]);

            try {
                DB::beginTransaction();

                $loanRequest->update([
                    'current_salary' => $validated['current_salary'],
                    'has_active_loans' => $validated['has_active_loans'],
                    'current_loan_balance' => $validated['current_loan_balance'],
                    'has_advances' => $validated['has_advances'],
                    'advances_amount' => $validated['advances_amount'],
                    'hr_signature' => $validated['hr_signature'],
                    'status' => 'reviewed',
                    'review_date' => now()
                ]);

                // Notify finance team after HR review
                Notification::route('mail', config('loan_emails.finance'))
                    ->notify(new LoanRequestReadyForApproval($loanRequest));

                DB::commit();

                return redirect()->route('loan-requests.index')
                    ->with('success', 'Solicitud actualizada correctamente.');
            } catch (\Exception $e) {
                DB::rollback();
                return back()->withInput()
                    ->with('error', 'Error al actualizar la solicitud: ' . $e->getMessage());
            }
        }

        abort(403, 'No autorizado para esta acción');
    }

    /**
     * Controller method to handle loan approval
     */
    public function approve(LoanRequest $loanRequest)
    {
        try {
            DB::beginTransaction();

            $loanRequest->update([
                'status' => 'approved',
                'admin_signature' => auth()->user()->name,
                'decision_date' => now()
            ]);

            // Notify applicant
            $loanRequest->user->notify(new LoanRequestApproved($loanRequest));

            // Generate PDF for accounting and HR
            $pdf = PDF::loadView('emails.loan-requests.pdf', [
                'loanRequest' => $loanRequest
            ]);

            // Notify accounting with PDF
            Notification::route('mail', config('loan_emails.accounting'))
                ->notify(new LoanRequestApproved($loanRequest, 'accounting', $pdf));
                
            // También notificar a Recursos Humanos con el PDF
            Notification::route('mail', 'recursoshumanos@tvs.edu.co')
                ->notify(new LoanRequestApproved($loanRequest, 'hr', $pdf));

            // Notify treasury with PDF
            Notification::route('mail', config('loan_emails.treasury'))
                ->notify(new LoanRequestApproved($loanRequest, 'accounting', $pdf));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving loan request: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Controller method to handle loan rejection
     */
    public function reject(Request $request, LoanRequest $loanRequest)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'rejection_reason' => 'required|string|min:3|max:500'
            ], [
                'rejection_reason.required' => 'Debe proporcionar una razón para el rechazo',
                'rejection_reason.min' => 'La razón del rechazo debe tener al menos 3 caracteres',
                'rejection_reason.max' => 'La razón del rechazo no puede exceder los 500 caracteres'
            ]);

            if ($loanRequest->status === 'rejected') {
                throw new \Exception('Esta solicitud ya ha sido rechazada');
            }

            DB::beginTransaction();

            $loanRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'admin_signature' => auth()->user()->name,
                'decision_date' => now()
            ]);

            // Notify the applicant
            $loanRequest->user->notify(new LoanRequestRejected($loanRequest));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada correctamente'
            ]);

        } catch (ValidationException $e) {
            \Log::error('Validation error when rejecting loan request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error rejecting loan request: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified loan request from storage.
     */
    public function destroy(LoanRequest $loanRequest)
    {
        // Only admins can delete
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'No autorizado para eliminar solicitudes');
        }
        
        $loanRequest->delete();
        
        return redirect()->route('loan-requests.index')
            ->with('success', 'Solicitud eliminada correctamente.');
    }

    /**
     * Generate a PDF for the loan request
     *
     * @param LoanRequest $loanRequest
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(LoanRequest $loanRequest)
    {
        // Check authorization
        if (!Auth::user()->hasRole(['Admin', 'rrhh', 'financiera']) && Auth::id() !== $loanRequest->user_id) {
            abort(403, 'No autorizado para ver esta solicitud');
        }

        $pdf = PDF::loadView('emails.loan-requests.pdf', [
            'loanRequest' => $loanRequest
        ]);

        return $pdf->download('prestamo-' . $loanRequest->id . '.pdf');
    }

    /**
     * Generate an amortization schedule for the loan
     *
     * @param LoanRequest $loanRequest
     * @return \Illuminate\Http\Response
     */
    public function amortization(LoanRequest $loanRequest)
    {
        // Check authorization
        if (!Auth::user()->hasRole(['Admin', 'rrhh', 'financiera']) && Auth::id() !== $loanRequest->user_id) {
            abort(403, 'No autorizado para ver esta solicitud');
        }

        // Get loan details
        $amount = $loanRequest->amount;
        $installments = $loanRequest->installments;
        $installmentValue = $loanRequest->installment_value;
        $interestRate = 0; // Suponiendo que tu modelo no tiene una tasa de interés definida
        $startDate = Carbon::parse($loanRequest->deduction_start_date);
        
        // Calculate amortization schedule
        $amortizationTable = [];
        $remainingAmount = $amount;
        
        for ($i = 1; $i <= $installments; $i++) {
            // Calculate payment date based on installment type (monthly or biweekly)
            $paymentDate = ($loanRequest->installment_type === 'monthly') 
                ? $startDate->copy()->addMonths($i - 1)
                : $startDate->copy()->addWeeks(($i - 1) * 2);
            
            // Calculate principal and interest
            $interest = $remainingAmount * ($interestRate / 100);
            $principal = $installmentValue - $interest;
            
            // Ensure we don't go below zero in the last payment
            if ($principal > $remainingAmount) {
                $principal = $remainingAmount;
                $installmentValue = $principal + $interest;
            }
            
            $remainingAmount -= $principal;
            
            // Add to amortization table
            $amortizationTable[] = [
                'installment_number' => $i,
                'payment_date' => $paymentDate->format('d/m/Y'),
                'installment_value' => $installmentValue,
                'principal' => $principal,
                'interest' => $interest,
                'remaining_amount' => max(0, $remainingAmount)
            ];
            
            // If remaining amount is zero or less, break
            if ($remainingAmount <= 0) {
                break;
            }
        }
        
        return view('loan-requests.amortization', [
            'loanRequest' => $loanRequest,
            'amortizationTable' => $amortizationTable
        ]);
    }
}
