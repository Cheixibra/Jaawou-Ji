<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Voyage;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // Récupérer toutes les réservations
    public function index()
    {
        $reservations = Reservation::with(['voyage', 'client'])->get(); // Récupère les réservations avec les voyages et clients
        return response()->json($reservations);
    }

    // Créer une nouvelle réservation
    public function store(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'date_reservation' => 'required|date',
                'statut' => 'required|string|max:255',
                'voyage_id' => 'required|exists:voyages,id', // Vérifie que le voyage existe
                'client_id' => 'required|exists:clients,id', // Vérifie que le client existe
            ]);

            // Création d'une nouvelle réservation
            $reservation = Reservation::create($request->all());

            return response()->json($reservation, 201); // 201 Created
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion des erreurs de validation
            return response()->json([
                'message' => 'Les données fournies étaient invalides.',
                'errors' => $e->errors(),
            ], 422); // 422 Unprocessable Entity
        } catch (\Exception $e) {
            // Gestion des autres exceptions
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    // Récupérer une réservation spécifique
    public function show($id)
    {
        $reservation = Reservation::with(['voyage', 'client'])->find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Réservation non trouvée'], 404);
        }
        return response()->json($reservation);
    }

    // Mettre à jour une réservation existante
    public function update(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Réservation non trouvée'], 404);
        }

        // Validation des données
        $request->validate([
            'date_reservation' => 'sometimes|required|date',
            'statut' => 'sometimes|required|string|max:255',
            'voyage_id' => 'sometimes|required|exists:voyages,id',
            'client_id' => 'sometimes|required|exists:clients,id',
        ]);

        // Mise à jour des attributs
        $reservation->update($request->all());

        return response()->json($reservation);
    }

    // Supprimer une réservation
    public function destroy($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Réservation non trouvée'], 404);
        }

        $reservation->delete();
        return response()->json(['message' => 'Réservation supprimée avec succès']);
    }

    public function reserve(Request $request)
    {
        try {
            // Validation des données d'entrée
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'voyage_id' => 'required|exists:voyages,id',
            ]);

            // Récupérer le voyage et vérifier la disponibilité
            $voyage = Voyage::find($request->input('voyage_id'));

            if (!$voyage || !$voyage->disponibilite) {
                return response()->json([
                    'message' => 'Le voyage sélectionné n\'est pas disponible.',
                ], 400); // 400 Bad Request
            }

            // Créer la réservation
            $reservation = Reservation::create([
                'client_id' => $request->input('client_id'),
                'voyage_id' => $voyage->id,
                'date_reservation' => now(),
                'statut' => 'confirmée',
            ]);

            // Mettre à jour la disponibilité du voyage si nécessaire (par exemple, le rendre indisponible)
            $voyage->disponibilite = false;
            $voyage->save();

            return response()->json([
                'message' => 'Réservation effectuée avec succès.',
                'reservation' => $reservation,
            ], 201); // 201 Created
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Les données fournies étaient invalides.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la réservation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
