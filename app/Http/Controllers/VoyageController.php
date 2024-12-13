<?php

namespace App\Http\Controllers;

use App\Models\Voyage; // Assurez-vous que le modèle Voyage existe
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoyageController extends Controller
{
    // Récupérer tous les voyages
    public function index()
    {
        $voyages = Voyage::with('partenaire')->get(); // Récupère les voyages avec les partenaires
        return response()->json($voyages);
    }

    // Créer un nouveau voyage
    public function store(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'partenaire_id' => 'required|exists:partenaires,id', // Vérifie que le partenaire existe
                'destination' => 'required|string|max:255',
                'date_depart' => 'required|date',
                'prix' => 'required|numeric',
                'disponibilite' => 'required|boolean',
            ]);

            // Vérification de l'existence d'un voyage identique
            $voyage = Voyage::where('partenaire_id', $request['partenaire_id'])
                ->where('destination', $request['destination'])
                ->where('date_depart', $request['date_depart'])
                ->where('prix', $request['prix'])
                ->where('disponibilite', $request['disponibilite'])
                ->first();

            if ($voyage) {
                // Si un voyage identique existe déjà, renvoyer une erreur
                return response()->json([
                    'message' => 'Un voyage avec les mêmes informations existe déjà pour ce partenaire.',
                ], 409); // 409 Conflict
            }

            // Création d'un nouveau voyage
            $voyage = new Voyage();
            $voyage->partenaire_id = $request['partenaire_id']; // Assignation de la clé étrangère
            $voyage->destination = $request['destination'];
            $voyage->date_depart = $request['date_depart'];
            $voyage->prix = $request['prix'];
            $voyage->disponibilite = $request['disponibilite'];
            $voyage->save();

            return response()->json($voyage, 201); // 201 Created
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

    // Récupérer un voyage spécifique
    public function show($id)
    {
        $voyage = Voyage::with('partenaire')->find($id);
        if (!$voyage) {
            return response()->json(['message' => 'Voyage non trouvé'], 404);
        }
        return response()->json($voyage);
    }

    // Mettre à jour un voyage existant
    public function update(Request $request, $id)
    {
        $voyage = Voyage::find($id);
        if (!$voyage) {
            return response()->json(['message' => 'Voyage non trouvé'], 404);
        }

        // Validation des données
        $request->validate([
            'partenaire_id' => 'sometimes|required|exists:partenaires,id', // Vérifie que le partenaire existe
            'destination' => 'sometimes|required|string|max:255',
            'date_depart' => 'sometimes|required|date',
            'prix' => 'sometimes|required|numeric',
            'disponibilite' => 'sometimes|required|boolean',
        ]);

        // Mise à jour des attributs
        if ($request->has('partenaire_id')) {
            $voyage->partenaire_id = $request['partenaire_id']; // Mise à jour de la clé étrangère
        }
        if ($request->has('destination')) {
            $voyage->destination = $request['destination'];
        }
        if ($request->has('date_depart')) {
            $voyage->date_depart = $request['date_depart'];
        }
        if ($request->has('prix')) {
            $voyage->prix = $request['prix'];
        }
        if ($request->has('disponibilite')) {
            $voyage->disponibilite = $request['disponibilite'];
        }

        $voyage->save();
        return response()->json($voyage);
    }

    // Supprimer un voyage
    public function destroy($id)
    {
        $voyage = Voyage::find($id);
        if (!$voyage) {
            return response()->json(['message' => 'Voyage non trouvé'], 404);
        }

        $voyage->delete();
        return response()->json(['message' => 'Voyage supprimé avec succès']);
    }
    public function search(Request $request)
    {
        // Récupérer les paramètres de recherche
        $destination = $request->input('destination');
        $date_depart = $request->input('date_depart');
        $prix_min = $request->input('prix_min');
        $prix_max = $request->input('prix_max');

        Log::info('Paramètres reçus : ', [
            'destination' => $destination,
            'date_depart' => $date_depart,
            'prix_min' => $prix_min,
            'prix_max' => $prix_max
        ]);

        // Construire la requête de base
        $query = Voyage::query();

        // Filtrer par destination si fourni
        if ($destination) {
            $query->where('destination', 'like', '%' . $destination . '%');
        }

        // Filtrer par date de départ si fourni
        if ($date_depart) {
            $query->where('date_depart', $date_depart);
        }

        // Filtrer par prix si fourni
        if ($prix_min) {
            $query->where('prix', '>=', $prix_min);
        }
        if ($prix_max) {
            $query->where('prix', '<=', $prix_max);
        }

        // Exécuter la requête et récupérer les résultats
        $voyages = $query->with('partenaire')->get();

        Log::info('Résultat de la requête : ', $voyages->toArray());

        if ($voyages->isEmpty()) {
            Log::warning('Aucun voyage trouvé avec les paramètres fournis.');
            return response()->json(['message' => 'Voyage non trouvé'], 404);
        }

        // Retourner les résultats
        return response()->json($voyages);
    }
}
