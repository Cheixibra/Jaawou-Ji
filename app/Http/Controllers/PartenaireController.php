<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Partenaire; // Assurez-vous que le modèle Partenaire existe
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class PartenaireController extends Controller
{
    // Récupérer tous les partenaires
    public function index()
    {
        $partenaires = Partenaire::all();
        return response()->json($partenaires);
    }

    // Créer un nouveau partenaire
    public function store(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'nom' => 'required|string|max:255',
                'email' => 'required|email|unique:partenaires|regex:/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,6}$/',
                'password' => 'required|string|min:8',
                'type_service' => 'required|string|max:255',
                'contact' => 'required|string|max:15',
            ]);

            // Récupérer l'administrateur par défaut (par exemple, le premier administrateur)
            $admin = Admin::first(); // Assurez-vous que cela correspond à votre logique

            if (!$admin) {
                return response()->json(['message' => 'Aucun administrateur trouvé'], 404);
            }

            // Création d'un nouveau partenaire
            $partenaire = new Partenaire();
            $partenaire->nom = $request['nom'];
            $partenaire->email = $request['email'];
            $partenaire->password = Hash::make($request['password']); // Hachage du mot de passe
            $partenaire->type_service = $request['type_service'];
            $partenaire->contact = $request['contact'];
            $partenaire->admin_id = $admin->id; // Assignation de l'admin_id
            $partenaire->save();

            return response()->json($partenaire, 201); // 201 Created
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

    // Récupérer un partenaire spécifique
    public function show($id)
    {
        $partenaire = Partenaire::find($id);
        if (!$partenaire) {
            return response()->json(['message' => 'Partenaire non trouvé'], 404);
        }
        return response()->json($partenaire);
    }

    // Mettre à jour un partenaire existant
    public function update(Request $request, $id)
    {
        $partenaire = Partenaire::find($id);
        if (!$partenaire) {
            return response()->json(['message' => 'Partenaire non trouvé'], 404);
        }

        // Validation des données
        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|regex:/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,6}$/|unique:partenaires,email,' . $partenaire->id,
            'password' => 'sometimes|required|string|min:8',
            'type_service' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:15',
        ]);

        // Mise à jour des attributs
        if ($request->has('password')) {
            $partenaire->password = Hash::make($request['password']); // Hachage du mot de passe
        }
        $partenaire->update($request->except('password')); // Met à jour les autres attributs
        return response()->json($partenaire);
    }

    // Supprimer un partenaire
    public function destroy($id)
    {
        $partenaire = Partenaire::find($id);
        if (!$partenaire) {
            return response()->json(['message' => 'Partenaire non trouvé'], 404);
        }

        $partenaire->delete();
        return response()->json(['message' => 'Partenaire supprimé avec succès']);
    }
}
