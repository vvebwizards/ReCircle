<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PickupDownloadController extends Controller
{
    /**
     * Télécharger la liste des pickups en PDF
     */
    public function downloadPDF(Request $request)
    {
        // Récupérer tous les pickups avec leurs relations
        $pickups = Pickup::with(['wasteItem.generator', 'courier'])
            ->latest()
            ->get();

        // Préparer les données pour le PDF
        $data = [
            'pickups' => $pickups,
            'totalCount' => $pickups->count(),
            'generatedAt' => now()->format('d/m/Y H:i:s'),
            'title' => 'Liste des Pickups',
        ];

        // Générer le PDF
        $pdf = Pdf::loadView('pickups.pdf.list', $data);

        // Configurer le PDF
        $pdf->setPaper('A4', 'landscape');

        // Télécharger
        return $pdf->download('pickups_list_'.date('Y-m-d').'.pdf');
    }

    /**
     * Télécharger la liste des pickups en CSV
     */
    public function downloadCSV(Request $request)
    {
        // Récupérer tous les pickups avec leurs relations
        $pickups = Pickup::with(['wasteItem.generator', 'courier'])
            ->latest()
            ->get();

        // Créer le fichier CSV
        $filename = 'pickups_list_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($pickups) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Code de Suivi',
                'Adresse de Pickup',
                'Produit',
                'Générateur',
                'Email Générateur',
                'Courrier',
                'Email Courrier',
                'Statut',
                'Fenêtre de Début',
                'Fenêtre de Fin',
                'Notes',
                'Créé le',
                'Mis à jour le',
            ]);

            // Données des pickups
            foreach ($pickups as $pickup) {
                fputcsv($file, [
                    $pickup->id,
                    $pickup->tracking_code,
                    $pickup->pickup_address,
                    $pickup->wasteItem->title ?? 'N/A',
                    $pickup->wasteItem->generator->name ?? 'N/A',
                    $pickup->wasteItem->generator->email ?? 'N/A',
                    $pickup->courier->name ?? 'Non assigné',
                    $pickup->courier->email ?? 'N/A',
                    $pickup->status,
                    $pickup->scheduled_pickup_window_start ? $pickup->scheduled_pickup_window_start->format('d/m/Y H:i') : 'N/A',
                    $pickup->scheduled_pickup_window_end ? $pickup->scheduled_pickup_window_end->format('d/m/Y H:i') : 'N/A',
                    $pickup->notes ?? 'N/A',
                    $pickup->created_at->format('d/m/Y H:i:s'),
                    $pickup->updated_at->format('d/m/Y H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Télécharger la liste des pickups en Excel (CSV avec formatage)
     */
    public function downloadExcel(Request $request)
    {
        // Récupérer tous les pickups avec leurs relations
        $pickups = Pickup::with(['wasteItem.generator', 'courier'])
            ->latest()
            ->get();

        // Créer le fichier CSV avec formatage Excel
        $filename = 'pickups_list_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($pickups) {
            $file = fopen('php://output', 'w');

            // BOM pour Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Code de Suivi',
                'Adresse de Pickup',
                'Produit',
                'Générateur',
                'Email Générateur',
                'Courrier',
                'Email Courrier',
                'Statut',
                'Fenêtre de Début',
                'Fenêtre de Fin',
                'Notes',
                'Créé le',
                'Mis à jour le',
            ]);

            // Données des pickups
            foreach ($pickups as $pickup) {
                fputcsv($file, [
                    $pickup->id,
                    $pickup->tracking_code,
                    $pickup->pickup_address,
                    $pickup->wasteItem->title ?? 'N/A',
                    $pickup->wasteItem->generator->name ?? 'N/A',
                    $pickup->wasteItem->generator->email ?? 'N/A',
                    $pickup->courier->name ?? 'Non assigné',
                    $pickup->courier->email ?? 'N/A',
                    $pickup->status,
                    $pickup->scheduled_pickup_window_start ? $pickup->scheduled_pickup_window_start->format('d/m/Y H:i') : 'N/A',
                    $pickup->scheduled_pickup_window_end ? $pickup->scheduled_pickup_window_end->format('d/m/Y H:i') : 'N/A',
                    $pickup->notes ?? 'N/A',
                    $pickup->created_at->format('d/m/Y H:i:s'),
                    $pickup->updated_at->format('d/m/Y H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
