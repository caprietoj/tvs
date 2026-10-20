<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UserDeletionService
{
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $id = $user->id;

            $this->nullifyReferences($id);
            $this->deleteRecordHistory($id);
            $this->deleteOwnedRecords($id);
            $this->deleteAccessRecords($user);

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            Log::info('Usuario eliminado junto con sus registros asociados', [
                'user_id' => $id,
                'email' => $user->email,
                'deleted_by' => auth()->id(),
            ]);
        });
    }

    private function nullifyReferences(int $id): void
    {
        $references = [
            ['purchase_orders', 'approved_by'],
            ['purchase_orders', 'cancelled_by'],
            ['purchase_orders', 'sent_by'],
            ['purchase_orders', 'viewed_by'],
            ['purchase_requests', 'approved_by'],
            ['purchase_requests', 'delivery_marked_by'],
            ['quotations', 'pre_approved_by'],
            ['request_histories', 'user_id'],
            ['salidas_pedagogicas', 'arl_confirmado_por'],
            ['salidas_pedagogicas', 'comunicaciones_confirmado_por'],
        ];

        foreach ($references as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)->where($column, $id)->update([$column => null]);
        }
    }

    private function deleteRecordHistory(int $id): void
    {
        $requestIds = $this->table('purchase_requests')
            ? DB::table('purchase_requests')->where('user_id', $id)->pluck('id')
            : collect();

        if ($requestIds->isEmpty() || ! $this->table('quotations')) {
            return;
        }

        $quotationIds = DB::table('quotations')
            ->whereIn('purchase_request_id', $requestIds)
            ->pluck('id');

        if ($quotationIds->isEmpty()) {
            return;
        }

        if (Schema::hasColumn('purchase_requests', 'pre_approved_quotation_id')) {
            DB::table('purchase_requests')
                ->whereIn('pre_approved_quotation_id', $quotationIds)
                ->update(['pre_approved_quotation_id' => null]);
        }

        if (Schema::hasColumn('purchase_requests', 'selected_quotation_id')) {
            DB::table('purchase_requests')
                ->whereIn('selected_quotation_id', $quotationIds)
                ->update(['selected_quotation_id' => null]);
        }
    }

    private function deleteOwnedRecords(int $id): void
    {
        $records = [
            ['commitments', 'author_id'],
            ['committee_decisions', 'author_id'],
            ['contributions', 'author_id'],
            ['disciplinary_cases', 'author_id'],
            ['external_supports', 'author_id'],
            ['strategies', 'author_id'],
            ['strategy_reviews', 'author_id'],
            ['import_batches', 'user_id'],
            ['document_requests', 'user_id'],
            ['equipment_loans', 'user_id'],
            ['feedback_sessions', 'employee_id'],
            ['feedback_sessions', 'supervisor_id'],
            ['loan_requests', 'user_id'],
            ['maintenance_requests', 'user_id'],
            ['nursing_survey_responses', 'uploaded_by'],
            ['parent_student_surveys', 'uploaded_by'],
            ['purchase_orders', 'created_by'],
            ['purchase_requests', 'user_id'],
            ['quotation_item_selections', 'selected_by'],
            ['sst_documents', 'user_id'],
        ];

        foreach ($records as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)->where($column, $id)->delete();
        }
    }

    private function deleteAccessRecords(User $user): void
    {
        $morphClass = $user->getMorphClass();

        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)
                ->where('model_type', $morphClass)
                ->where('model_id', $user->id)
                ->delete();
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('notifiable_type', $morphClass)
                ->where('notifiable_id', $user->id)
                ->delete();
        }

        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', $morphClass)
                ->where('tokenable_id', $user->id)
                ->delete();
        }
    }

    private function table(string $table): bool
    {
        return Schema::hasTable($table);
    }
}
