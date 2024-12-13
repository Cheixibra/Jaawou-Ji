<?php

namespace App\Http\Controllers;

use App\Models\Client; // Assurez-vous que le modèle Client existe
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    // Récupérer tous les clients
    public function index()
    {
        $clients = Client::all();
        return response()->json($clients);
    }

    // Créer un nouveau client
    public function store(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'nom' => 'required|string|max:255',
                'telephone' => 'required|string|max:15', // Ajustez la longueur selon vos besoins
                'email' => 'required|email|unique:clients|regex:/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,6}$/',
                'password' => 'required|string|min:8',
            ]);

            // Création d'un nouveau client
            $client = new Client();
            $client->nom = $request['nom'];
            $client->telephone = $request['telephone'];
            $client->email = $request['email'];
            $client->password = Hash::make($request['password']); // Hachage du mot de passe
            $client->save();

            return response()->json($client, 201); // 201 Created
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

    // Récupérer un client spécifique
    public function show($id)
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }
        return response()->json($client);
    }

    // Mettre à jour un client existant
    public function update(Request $request, $id)
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }

        // Validation des données
        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'telephone' => 'sometimes|required|string|max:15',
            'email' => 'sometimes|required|email|regex:/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,6}$/|unique:clients,email,' . $client->id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        // Mise à jour des attributs
        if ($request->has('password')) {
            $client->password = Hash::make($request['password']); // Hachage du mot de passe
        }
        $client->update($request->except('password')); // Met à jour les autres attributs
        return response()->json($client);
    }

    // Supprimer un client
    public function destroy($id)
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }

        $client->delete();
        return response()->json(['message' => 'Client supprimé avec succès']);
    }
}
