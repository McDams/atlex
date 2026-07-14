<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Donation;

/**
 * Consultation des dons en ligne (MTN MoMo + PayPal).
 */
final class DonationsController extends Controller
{
    private Donation $donations;

    public function __construct()
    {
        Auth::requireAuth();
        $this->donations = new Donation();
    }

    public function index(): void
    {
        $status = (string) ($this->input('status') ?: 'tous');
        $method = $this->input('method');
        $method = is_string($method) && $method !== '' ? $method : null;

        $this->render('admin/donations/index', [
            'title'        => 'Dons — Espace SG',
            'donations'    => $this->donations->filtered($status, $method),
            'totals'       => $this->donations->totalsByCurrency(),
            'filterStatus' => $status,
            'filterMethod' => $method,
        ], 'layouts/admin');
    }

    /**
     * Rattrapage manuel : le SG a vérifié dans son propre compte marchand
     * MTN que le paiement est bien arrivé, malgré une confirmation
     * automatique manquante (webhook non livré, coupure réseau du donateur...).
     */
    public function markConfirmed(string $id): void
    {
        $this->verifyCsrf();

        $donation = $this->donations->find((int) $id);
        if ($donation === null) {
            flash('error', 'Don introuvable.');
            $this->redirect('admin/dons');
        }

        if ($donation['status'] !== 'completed') {
            $this->donations->update((int) $id, ['status' => 'completed']);
            flash('success', 'Don marqué comme confirmé.');
        }

        $this->redirect('admin/dons');
    }
}
